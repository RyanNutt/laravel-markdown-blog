<?php

namespace Aelora\MarkdownBlog\Models;

use Aelora\MarkdownBlog\Facades\MarkdownBlog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use JsonSerializable;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Str;
use Symfony\Component\Yaml\Yaml;

class Post extends Model implements JsonSerializable
{

    protected $casts = [
        'date' => 'datetime',
        'tags' => 'array',
        'categories' => 'array',
        'post' => 'boolean',
        'front_matter' => 'array',
    ];

    protected $table = 'mdblog';

    private $frontMatter = null;

    /**
     * Load the model from a markdown file
     */
    public static function fromFile(string $file): self
    {
        throw_if(!file_exists($file), new \Exception('File does not exist'));

        $o = YamlFrontMatter::parse(file_get_contents($file));
        $obj = new self();
        $obj->title = $o->title;
        $obj->frontMatter = $o->matter();

        // Need to encode these to prevent a PHP warning, casting pulls it back
        // to an array when needed. We're storing the slug, not the actual
        // name. If name is needed later it'll get pulled from the Category
        // model using the slug. 
        $obj->categories = array_map(function ($v) {
            return MarkdownBlog::cleanPath($v);
        }, collect($o->categories ?? $o->category)->toArray());
        $obj->tags = array_map(function ($v) {
            return MarkdownBlog::cleanPath($v);
        }, collect($o->tags ?? $o->tag)->toArray());

        // The rest of this might need the filename
        $obj->fullpath = $file;
        $obj->filename = basename($file);
        $obj->name = pathinfo($file, PATHINFO_FILENAME);

        $obj->filepath = $file;

        if ($o->matter('date') != '') {
            $obj->publish_date = Carbon::parse($o->date);
        } else if (preg_match('/(\/|^)(\d{4}-\d{2}-\d{2})/', $file, $matches)) {
            $obj->publish_date = Carbon::parse($matches[2]);
        } else {
            // Better than nothing
            $obj->publish_date = Carbon::now();
        }

        // Strip date, if there from name
        $obj->name = preg_replace('/^\d{4}-\d{2}-\d{2}-/', '', $obj->name);
        if (empty($obj->title)) {
            $obj->title = Str::headline($obj->name); // fallback
        }
        if ($o->matter('permalink') != '') {
            $obj->permalink = $o->permalink;
        } else {
            // Path relative to mdblog root and filename without date and without extension
            $obj->permalink = MarkdownBlog::cleanPath(Str::contains($file, '/') ? Str::beforeLast($file, '/') . '/' . $obj->name : $obj->name);
            $obj->permalink = preg_replace('#^' . storage_path('mdblog') . '#', '', $obj->permalink);
        }

        // Needs to be relative to root
        $obj->permalink = Str::start($obj->permalink, '/');

        // Remove trailing slash, even if it's explicitly defined
        $obj->permalink = preg_replace('#/{1}$#', '', $obj->permalink);

        $obj->type = Str::lower($o->matter('type', 'post'));

        // High default so can sneak in before or after without defining for all posts
        $obj->sort_order = $o->matter('sort', 50);

        $obj->published = true;
        if (array_key_exists('draft', $o->matter())) {
            $obj->published = false;
        } else if ($obj->publish_date > Carbon::now()) {
            $obj->published = false;
        }
        $obj->image = $o->matter('image', '');

        $obj->front_matter = $o->matter();

        $obj->parent_id = 0; // Will get filled in later after filling table

        $obj->content = $o->body();
        return $obj;
    }

    /**
     * Tries to load a post from the current request
     */
    public static function current()
    {
        $permalink = request()->getPathInfo();
        return self::permalink($permalink)->published()->first();
    }

    /**
     * Load the model from an array. This will be the same
     * array as when it's toJson()d.
     */
    public static function fromArray(array $data): self
    {
    }

    public function html(): string
    {
        $parsedown = new \Parsedown();
        return $this->fixImagePaths($parsedown->text($this->content));
    }

    public function rendered(): string
    {
        $html = $this->html();
        return Blade::render($html, [
            'post' => $this,
        ]);
    }

    /**
     * Loads the raw contents from the markdown file
     */
    public function fileContent(): ?string
    {
        if (!file_exists($this->fullpath)) {
            return '';
        }
        $o = YamlFrontMatter::parse(file_get_contents($this->fullpath));
        return trim($o->body());
    }

    public function frontMatter()
    {
        if (is_array($this->frontMatter)) {
            return $this->frontMatter;
        }
        if (!file_exists($this->fullpath)) {
            return [];
        }
        $o = YamlFrontMatter::parse(file_get_contents($this->fullpath));
        $this->frontMatter = $o->matter();
        return $o->matter();
    }

    public function jsonSerialize(): mixed
    {
        return [
            'title' => $this->title,
            'tags' => $this->tags,
            'categories' => $this->categories,
            'date' => $this->date,
            'permalink' => $this->permalink,
            'filepath' => $this->filepath,
            'filename' => $this->filename,
            'name' => $this->name,
            'fullpath' => $this->fullpath,
        ];
    }

    public function toArray($opts = 0): mixed
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'tags' => $this->tags,
            'categories' => $this->categories,
            'date' => $this->date,
            'permalink' => $this->permalink,
            'filepath' => $this->filepath,
            'filename' => $this->filename,
            'name' => $this->name,
            'fullpath' => $this->fullpath,
            'type' => Str::lower($this->postType),
            'published' => (bool)$this->published,
            'rawFrontMatter' => json_encode($this->rawFrontMatter),
            'parent_id' => $this->parent_id,
            'children' => $this->children,
            'next' => $this->next,
            'previous' => $this->previous,
            'sortOrder' => $this->sortOrder,
        ];
    }

    /**
     * Add query to filter out the post that's the parent of the 
     * passed post. It's passed as either a Post reference or the
     * permalink of that post. The permalink is what's actually
     * queried, so there's no reason to load a full post for this
     * method unless it's already been loaded. 
     */
    public function scopeParentOf($qry, Post|string $post)
    {
        if ($post instanceof Post) {
            $post = $post->permalink;
        }
        $qry->where('parent', $post);
    }

    public function scopeYear($qry, $year)
    {
        $qry->whereYear('date', $year);
    }

    public function scopeMonth($qry, $month)
    {
        $qry->whereMonth('date', $month);
    }

    public function scopeDay($qry, $day)
    {
        $qry->whereDay('date', $day);
    }

    public function scopePermalink($qry, $permalink)
    {
        // Stored permalinks always contain the leading /
        $permalink = Str::start($permalink, '/');
        $qry->where('permalink', $permalink);
    }

    public function scopeBestMatch($qry, $searchFor, $limitToType = false)
    {
        $qry->where(function ($q) use ($searchFor) {
            $q->where('permalink', 'LIKE', '%' . $searchFor . '%');
            $q->orWhere('filepath', 'LIKE', '%' . $searchFor . '%');
        });

        if ($limitToType) {
            $qry->where('type', $limitToType);
        }
    }

    /**
     * Only posts that should show up in the blog lists
     */
    public function scopePosts($qry)
    {
        $qry->where('type', 'post');
    }

    public function scopeType($qry, $type = 'post')
    {
        $type = Str::lower($type);
        $qry->where('type', $type);
    }

    public function scopeNotType($qry, $type = 'post')
    {
        $type = Str::lower($type);
        $qry->whereNot('type', $type);
    }

    /**
     * Scope to filter out only published posts. This should probably be
     * used in almost all cases unless you want draft and future dated
     * posts included. 
     */
    public function scopePublished($qry)
    {
        $qry->where('publish_date', '<=', now());
        $this->scopeNotFrontMatter($qry, 'draft');
    }

    /**
     * Filter for posts containing a specific front matter key, and optionally
     * a value for that key. If $value is empty then any post that contains $key
     * is included. If $value is not empty then only posts that have that $key 
     * at that $value are included. 
     */
    public function scopeFrontMatter($qry, $key, $value = null)
    {
        if ($value === null) {
            // Just has that key, doesn't matter the value
            $qry->whereNotNull('front_matter->' . $key);
        } else {
            // Has the key and specific value
            $qry->where('front_matter->' . $key, $value);
        }
    }

    /**
     * Filter to filter out posts that have a specific front matter element and
     * value. If $value === null then all posts with any value, including blank, 
     * for that key are filtered out. If $value !== null then only posts with 
     * that key and value pair are filtered out. 
     * 
     * Either way, posts that do not have $key as part of their front matter
     * are still included in the results. 
     */
    public function scopeNotFrontMatter($qry, $key, $value = null)
    {
        if ($value === null) {
            // If the key is there at all, filter it out
            $qry->whereNull('front_matter->' . $key);
        } else {
            // Only filter out where the key value matches
            $qry->whereNot('front_matter->' . $key, $value);
        }
    }

    /**
     * Only posts that should not show up in the blog lists
     */
    public function scopeNotPosts($qry)
    {
        $qry->whereNot('type', 'post');
    }

    /**
     * Category scope filter
     * 
     * Seems there should be a better, more Laravel way, to do this rather
     * than just looking in the JSON encoded string, but it's working for now. 
     */
    public function scopeCategory($qry, $cat)
    {
        $qry->where('categories', 'like', '%' . Str::slug($cat) . '%');
    }

    public function scopeTag($qry, $tag)
    {
        $qry->where('tags', 'like', '%' . Str::slug($tag) . '%');
    }

    protected function year(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                return $this->date->year;
            }
        );
    }

    /**
     * Returns the image property from front matter after fixing the path
     * so it's relative to the public assets folder. 
     */
    protected function image(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $path = '';
                if (empty($value) || Str::startsWith(strtolower($value), 'http')) {
                    return $value;
                } else if (Str::startsWith($value, '/') && !Str::startsWith($value, '//')) {
                    // Path relative to repo root
                    $path = '/' . config('mdblog.public.path', 'assets/blog') . $value;
                } else {
                    // Path relative to post directory
                    $path = $this->publicPath($value);
                }
                return MarkdownBlog::normalizePath($path);
            },
        );
    }

    /**
     * Returns the content of the post after processing the markdown and fixing relative 
     * image paths. 
     */
    protected function content(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                // Markdown images
                $value = preg_replace_callback('/(!\[.*?\]\()(.+?)\)/', function ($matches) {
                    $before = $matches[1];
                    $path = $matches[2];
                    if (Str::startsWith($path, '/') && !Str::startsWith($path, '//')) {
                        // Path relative to the repository root
                        $newPath = Str::start(config('mdblog.public.path', 'assets/blog') . $path, '/');
                        return $before . MarkdownBlog::normalizePath($newPath) . ')';
                    } else {
                        // Path relative to the post
                        $newPath = $this->publicPath($path);
                        // dd('/' . $before . $newPath . ')');
                        return $before . MarkdownBlog::normalizePath($newPath) . ')';
                    }
                }, $value);

                // HTML images
                $value = preg_replace_callback('/(<img.*?)src="(.*?)"(.*?>)/i', function ($matches) {
                    $before = $matches[1];
                    $path = $matches[2];
                    $after = $matches[3];
                    if (Str::startsWith($path, '/') && !Str::startsWith($path, '//')) {
                        // Path relative to the repository root
                        $newPath = Str::start(config('mdblog.public.path', 'assets/blog') . $path, '/');
                        return $before . 'src="' . MarkdownBlog::normalizePath($newPath) . '"' . $after;
                    } else {
                        // Path relative to the post
                        $newPath = $this->publicPath($path);
                        // dd('/' . $before . $newPath . ')');
                        return $before . 'src="' . MarkdownBlog::normalizePath($newPath) . '"' . $after;
                    }
                }, $value);

                return $value;
            }
        );
    }

    public function description(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                return Arr::get($this->rawFrontMatter, 'description', '');
            }
        );
    }

    /**
     * Replaces the content of the markdown, leaving the front matter
     * in place. 
     */
    public function updateContent(string $newContent, $extraMatter = [])
    {
        if ($newContent == $this->content() && empty($extraMatter)) {
            return;
        }

        $matter = array_merge($this->frontMatter(), $extraMatter);

        $frontMatterString = empty($matter) ? '' : Yaml::dump($matter);
        $fullContent = "---\n" . $frontMatterString . "---\n" . $newContent;

        File::put($this->fullpath, $fullContent);
    }

    public function getUrlAttribute()
    {
        return url($this->permalink);
    }

    /**
     * Returns the primary category for this post. The primary is the first
     * one listed in frontmatter. 
     * 
     * Returns null if there isn't a category listed. 
     */
    public function primaryCategory(): ?Category
    {
        $cats = $this->categories;
        if (!empty($cats[0])) {
            return Category::where('slug', $cats[0])->first();
        }
        return null;
    }

    /**
     * Returns the primary tag for this post. The primary is the first
     * tag listed in frontmatter.
     * 
     * Returns null if there isn't a tag specified in front matter. 
     */
    public function primaryTag(): ?Tag
    {
        $tags = $this->tags;
        if (!empty($tags[0])) {
            return Tag::where('slug', $tags[0])->first();
        }
        return null;
    }

    public function parentPost(): ?Post
    {
        if (!empty($this->parent)) {
            return Post::permalink($this->parent)->first();
        }
        return null;
    }

    /**
     * Get children of post, and any grandchildren up to and 
     * including $maxDepth. 
     * 
     * @TODO $maxDepth is currently ignored, always 1
     */
    public function childPosts($maxDepth = 1): ?\Illuminate\Support\Collection
    {
        $children = collect();
        foreach ($this->children as $childPermalink) {
            $child = Post::permalink($childPermalink)->first();
            dump($child);
            if (!empty($child)) {
                $children->push($child);
            }
        }
        return $children;
    }

    public function nextPost(): ?Post
    {
        return null;
    }

    public function nextInCategory($catSlug): ?Post
    {
        return null;
    }

    public function nextInTag($tagSlug): ?Post
    {
        return null;
    }

    public function previousPost(): ?Post
    {
        return null;
    }

    public function previousInCategory($catSlug): ?Post
    {
        return null;
    }

    public function previousInTag($tagSlug): ?Post
    {
        return null;
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Returns the path relative to the public folder where this post would be, if it was
     * copied to the public storage. Note that this is the folder of the post 
     * file, not the file itself. 
     * 
     * This is primarily going to be used for images that are stored in the same
     * folder as a markdown post so that paths can be fixed when the post is
     * rendered. 
     */
    public function publicPath($subPath = false): string
    {
        return Str::start(config('mdblog.public.path', 'assets/blog') . $this->relativePath($subPath), '/');
    }

    /**
     * Returns the path to this file, relative to the repository root. Should both
     * begin and end with a forward slash. It does this by stripping the storage_path
     * from the front of the full path, so this only works if the file is in the
     * storage_path, which it should always be except possibly for tests. 
     */
    public function relativePath($subPath = false): string
    {

        $basePath = dirname(preg_replace('#^' . storage_path('mdblog') . '#', '', $this->fullpath));
        $basePath = Str::start(Str::finish($basePath, '/'), '/');
        if (!empty($subPath)) {
            $basePath .= $subPath;
        }
        return $basePath;
    }

    /**
     * Fix image paths in the content so that they're correctly pointing to the public
     * assets folder, relative to where this post is stored. 
     * 
     * @deprecated Moved to the ->content() attribute getter
     */
    public function fixImagePaths(): string
    {
        $content = $this->content;

        // Markdown images
        $content = preg_replace_callback('/(!\[.*?\]\()(.+?)\)/', function ($matches) {
            $before = $matches[1];
            $path = $matches[2];
            if (Str::startsWith($path, '/') && !Str::startsWith($path, '//')) {
                // Path relative to the repository root
                $newPath = Str::start(config('mdblog.public.path', 'assets/blog') . $path, '/');
                return $before . MarkdownBlog::normalizePath($newPath) . ')';
            } else {
                // Path relative to the post
                $newPath = $this->publicPath($path);
                // dd('/' . $before . $newPath . ')');
                return $before . MarkdownBlog::normalizePath($newPath) . ')';
            }
        }, $content);

        // HTML images
        $content = preg_replace_callback('/(<img.*?)src="(.*?)"(.*?>)/i', function ($matches) {
            $before = $matches[1];
            $path = $matches[2];
            $after = $matches[3];
            if (Str::startsWith($path, '/') && !Str::startsWith($path, '//')) {
                // Path relative to the repository root
                $newPath = Str::start(config('mdblog.public.path', 'assets/blog') . $path, '/');
                return $before . 'src="' . MarkdownBlog::normalizePath($newPath) . '"' . $after;
            } else {
                // Path relative to the post
                $newPath = $this->publicPath($path);
                // dd('/' . $before . $newPath . ')');
                return $before . 'src="' . MarkdownBlog::normalizePath($newPath) . '"' . $after;
            }
        }, $content);

        return $content;
    }
}
