<?php

namespace Aelora\MarkdownBlog\Models;

use Aelora\MarkdownBlog\Facades\MarkdownBlog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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

    protected static function booted()
    {
        static::addGlobalScope('published', function (Builder $builder) {
            $builder->where('published', true);
        });
    }

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
        $obj->categories = Arr::map(collect($o->categories ?? $o->category)->toArray(), function ($v, $idx) {
            return MarkdownBlog::cleanPath($v);
        });
        $obj->tags = Arr::map(collect($o->tags ?? $o->tag)->toArray(), function ($v, $idx) {
            return MarkdownBlog::cleanPath($v);
        });

        // The rest of this might need the filename
        $obj->filepath = $file;
        $obj->filename = basename($file);
        $obj->name = pathinfo($file, PATHINFO_FILENAME);
        $obj->fullpath = $file;
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
        return self::permalink($permalink)->first();
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
        return $parsedown->text($this->content);
    }

    public function rendered(): string
    {
        $html = $this->html();
        return MarkdownBlog::bladeCompile($html, ['post' => $this]);
    }

    /**
     * Loads the contents of the markdown file
     */
    public function content(): ?string
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

    // public function newQuery($excludeDeleted = true)
    // {
    //     $builder = parent::newQuery($excludeDeleted);
    //     $builder->where('published', true);
    //     return $builder;
    //     // if (Config::get('hide_banned_users', true) !== false) {
    //     //     $builder->where('banned', '=', '0');
    //     // }
    //     // return $builder;
    // }

    public function getYearAttribute()
    {
        return $this->date->year;
    }

    public function getDescriptionAttribute()
    {
        return !empty($this->rawFrontMatter['description']) ? $this->rawFrontMatter['description'] : '';
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
}
