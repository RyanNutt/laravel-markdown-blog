<?php

namespace Aelora\MarkdownBlog\Commands;

use Illuminate\Console\Command;
use Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;


class DownloadRepository extends Command
{
    public $signature = 'mdblog:download';

    public $description = 'Downloads blog content and unzips to storage folder';

    public function handle(): int
    {

        $storage = Storage::build(['driver' => 'local', 'root' => '/']);
        $storage->deleteDirectory(storage_path('mdblog'));
        throw_if(!$storage->makeDirectory(storage_path('mdblog')), new \Exception('Could not create folder for Markdown Blog files'));

        $repository = Str::finish(config('mdblog.repository.url'), '/');
        throw_if(empty($repository), new \Exception('Repository not set for Markdown blog'));

        if (Str::contains($repository, 'github.com') || Str::lower(config('mdblog.repository.type')) === 'github') {
            $zipfile = $repository . 'zipball/' . config('mdblog.repository.branch') . '/';
        } else {
            throw new \Exception('Only GitHub is currently supported');
        }

        $headers = [];
        if (!empty(config('mdblog.repository.key'))) {
            $headers['Authorization'] = 'token ' . config('mdblog.repository.key');
        }

        $response = Http::withHeaders($headers)->get($zipfile);
        $statusCode = $response->getStatusCode();
        if ($statusCode < 200 || $statusCode >= 300) {
            throw new \Exception('Invalid response from repository: ' . $statusCode);
        }
        $downloadPath = Str::finish(storage_path('mdblog'), '/') . '.gitdownload.zip';
        file_put_contents($downloadPath, $response->getBody()->__toString());

        // Unzip
        $za = new \ZipArchive();

        $za->open(storage_path('mdblog/.gitdownload.zip'));

        for ($i = 0; $i < $za->numFiles; $i++) {
            $stat = $za->statIndex($i);
            if ($stat['size'] > 0) {
                // Can't write directories, it'll be taken care of by the file
                $fullPath = storage_path('mdblog/') . Str::after($stat['name'], '/');
                $contents = $za->getFromIndex($i);
                $storage->put($fullPath, $contents);
            }
        }
        $storage->delete(storage_path('mdblog/.gitdownload.zip'));
        return self::SUCCESS;
    }
}
