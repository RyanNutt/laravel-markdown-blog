<?php

namespace Aelora\MarkdownBlog\Models;

use Aelora\MarkdownBlog\Facades\MarkdownBlog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Tag extends Model
{
    use \Sushi\Sushi;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $schema = [
        'id' => 'string',
        'name' => 'string',
        'slug' => 'string',
    ];

    public function getRows()
    {
        return Cache::store(MarkdownBlog::cacheStore())->rememberForever('mdblog.tags', function () {
            $allFiles = File::allFiles(storage_path('mdblog'));
            $tags = [];
            $done = [];
            if (!empty($allFiles)) {
                foreach ($allFiles as $file) {
                    $yaml = YamlFrontMatter::parse($file->getContents());
                    $tagList = $yaml->matter('tags');
                    if (!empty($tagList)) {
                        foreach ($tagList as $tag) {
                            $tag = trim($tag);
                            if (!empty($tag)) {
                                $slug = Str::slug($tag);
                                if (!in_array($slug, $done)) {
                                    $tags[] = [
                                        'id' => $slug,
                                        'name' => $tag,
                                        'slug' => $slug,
                                    ];
                                    $done[] = $slug;
                                }
                            }
                        }
                    }
                }
            }
            return $tags;
        });
    }

    public function scopeSlug($qry, $slug)
    {
        return $qry->where('slug', Str::slug($slug));
    }

    public function posts()
    {
        return Post::tag($this->slug);
    }
}