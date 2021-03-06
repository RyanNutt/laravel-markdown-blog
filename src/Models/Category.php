<?php

namespace Aelora\MarkdownBlog\Models;

use Aelora\MarkdownBlog\Facades\MarkdownBlog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class Category extends Model
{
    use \Sushi\Sushi;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $schema = [
        'id' => 'string',
        'name' => 'string',
        'slug' => 'string',
        'meta' => 'string',
    ];

    public function getRows()
    {
        return Cache::store(MarkdownBlog::cacheStore())->rememberForever('mdblog.categories', function () {
            $cats = [];
            $done = [];
            foreach (Post::all() as $post) {
                $catList = $post->categories;
                if (!empty($catList)) {
                    foreach ($catList as $cat) {
                        $cat = trim($cat);
                        if (!empty($cat)) {
                            $slug = Str::slug($cat);
                            if (!in_array($slug, $done)) {
                                $cats[] = [
                                    'id' => $slug,
                                    'name' => $cat,
                                    'slug' => $slug,
                                    'meta' => json_encode(Arr::get(MarkdownBlog::blogJson(), 'categories.' . $slug, [])),
                                ];
                                $done[] = $slug;
                            }
                        }
                    }
                }
            }
            return $cats;
        });
    }

    public function scopeSlug($qry, $slug)
    {
        return $qry->where('slug', Str::slug($slug));
    }

    public function posts()
    {
        return Post::category($this->slug);
    }
    public function url(): string
    {
        return url(preg_replace('/{(.+?)}/', $this->slug, config('mdblog.permalinks.categories')));
    }

    public function meta(string $field, $default = null)
    {
        $meta = json_decode($this->meta, true);
        return Arr::get($meta, $field, $default);
    }
}
