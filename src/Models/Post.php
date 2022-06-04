<?php

namespace Aelora\MarkdownBlog\Models;

use Aelora\MarkdownBlog\Facades\MarkdownBlog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use JsonSerializable;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Str;
use Symfony\Component\Yaml\Yaml;

class Post extends Model implements JsonSerializable
{

    use \Sushi\Sushi;
    protected $casts = [
        'date' => 'datetime',
        'tags' => 'array',
        'categories' => 'array',
        'post' => 'boolean',
    ];
    public function getRows()
    {
        return Cache::store(MarkdownBlog::cacheStore())->rememberForever('mdblog.posts', function () {
            $allFiles = File::allFiles(storage_path('mdblog'));
            $files = [];
            if (!empty($allFiles)) {
                foreach ($allFiles as $file) {
                    $lcFilename = $file->getFilename();
                    if (Str::endsWith($lcFilename, ['.md', '.markdown', '.html', '.html'])) {
                        // Only want markdown or html files
                        $filePath = preg_replace('#^' . storage_path('mdblog') . '#', '', $file->getPathname());
                        $files[] = Post::fromFile($filePath)->toArray();
                    }
                }
            }
            return $files;
        });
    }

    protected function sushiShouldCache()
    {
        return false;
    }

    /**
     * Load the model from a markdown file
     */
    public static function fromFile(string $file): self
    {
        $fullPath = storage_path('mdblog' . $file);
        throw_if(!file_exists($fullPath), new \Exception('File does not exist'));

        $o = YamlFrontMatter::parse(file_get_contents($fullPath));
        $obj = new self();
        $obj->title = $o->title;

        // Need to encode these to prevent a PHP warning, casting pulls it back
        // to an array when needed. We're storing the slug, not the actual
        // name. If name is needed later it'll get pulled from the Category
        // model using the slug. 
        $obj->categories = json_encode(array_map(function ($cat) {
            return Str::slug($cat);
        }, $o->categories ?? []));
        $obj->tags = json_encode(array_map(function ($tag) {
            return Str::slug($tag);
        }, $o->tags ?? []));

        // The rest of this might need the filename
        $obj->filepath = $file;
        $obj->filename = basename($file);
        $obj->name = pathinfo($file, PATHINFO_FILENAME);
        $obj->fullpath = $fullPath;
        if (!empty($o->date)) {
            $obj->date = Carbon::parse($o->date);
        } else if (preg_match('/(\/|^)(\d{4}-\d{2}-\d{2})/', $file, $matches)) {
            $obj->date = Carbon::parse($matches[2]);
        } else {
            // Better than nothing
            $obj->date = Carbon::now();
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
            $obj->permalink = Str::cleanPath(Str::contains($file, '/') ? Str::beforeLast($file, '/') . '/' . $obj->name : $obj->name);
        }

        // Needs to be relative to root
        $obj->permalink = Str::start($obj->permalink, '/');

        $isPost = $o->matter('post', true);
        $obj->post = $isPost === true || Str::toLower($isPost) == 'true' || $isPost == '1';

        return $obj;
    }

    /**
     * Load the model from an array. This will be the same
     * array as when it's toJson()d.
     */
    public static function fromArray(array $data): self
    {
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
        if (!file_exists($this->fullpath)) {
            return '';
        }
        $o = YamlFrontMatter::parse(file_get_contents($this->fullpath));
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
            'title' => $this->title,
            'tags' => $this->tags,
            'categories' => $this->categories,
            'date' => $this->date,
            'permalink' => $this->permalink,
            'filepath' => $this->filepath,
            'filename' => $this->filename,
            'name' => $this->name,
            'fullpath' => $this->fullpath,
            'post' => (bool)$this->post,
        ];
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

    /**
     * Only posts that should show up in the blog lists
     */
    public function scopePosts($qry)
    {
        $qry->where('post', true);
    }

    /**
     * Only posts that should not show up in the blog lists
     */
    public function scopeNotPosts($qry)
    {
        $qry->where('post', false);
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

    public function getYearAttribute()
    {
        return $this->date->year;
    }

    /**
     * Replaces the content of the markdown, leaving the front matter
     * in place. 
     */
    public function updateContent(string $newContent)
    {
        if ($newContent == $this->content()) {
            return;
        }

        $frontMatterString = empty($this->frontMatter()) ? '' : Yaml::dump($this->frontMatter());
        $fullContent = "---\n" . $frontMatterString . "---\n" . $newContent;

        File::put($this->fullpath, $fullContent);
    }

    public function getUrlAttribute()
    {
        return url($this->permalink);
    }
}
