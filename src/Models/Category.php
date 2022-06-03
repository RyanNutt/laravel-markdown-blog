<?php

namespace Aelora\MarkdownBlog\Models;

use Aelora\MarkdownBlog\Facades\MarkdownBlog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Category extends Model
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

        // return [
        //     ['a' => 1, 'b' => 2],
        //     ['a' => 3, 'b' => 4],
        // ];

        Cache::store(MarkdownBlog::cacheStore())->forget('mdblog.categories');
        return Cache::store(MarkdownBlog::cacheStore())->rememberForever('mdblog.categories', function () {
            $allFiles = File::allFiles(storage_path('mdblog'));
            $cats = [];
            $done = [];
            if (!empty($allFiles)) {
                foreach ($allFiles as $file) {
                    $yaml = YamlFrontMatter::parse($file->getContents());
                    $catList = $yaml->matter('categories');
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
                                    ];
                                    $done[] = $slug;
                                }
                            }
                        }
                    }
                }
            }
            return $cats;
        });
    }

    public function posts(): Collection
    {
        ray('hi');
        return collect();
    }
}
