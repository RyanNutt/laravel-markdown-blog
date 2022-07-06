<?php

namespace Aelora\MarkdownBlog\Commands;

use Aelora\MarkdownBlog\Events\RepositoryDownloaded;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Str;

class DownloadRepository extends Command
{
    public $signature = 'mdblog:download';

    public $description = 'Downloads blog content and unzips to storage folder';

    public function handle(): int
    {
        if (empty(config('mdblog.repository.url'))) {
            $this->error('Markdown blog repository not set');
            return 1;
        }

        $storage = Storage::build(['driver' => 'local', 'root' => '/']);
        if (File::isDirectory(storage_path('mdblog'))) {
            (new \Illuminate\Filesystem\Filesystem())->cleanDirectory(storage_path('mdblog'));
        } else {
            File::makeDirectory(storage_path('mdblog'));
        }

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

        if (!File::isDirectory(dirname($downloadPath))) {
            File::makeDirectory(dirname($downloadPath));
        }
        file_put_contents($downloadPath, $response->getBody()->__toString());

        $this->line('Downloaded repository ' . $repository);

        // Unzip
        $za = new \ZipArchive();

        $za->open(storage_path('mdblog/.gitdownload.zip'));

        if ($za->numFiles <= 0) {
            $this->line('Zip file is empty');
        } else {

            $this->line('Unzipping files');
            $cnt = 0;
            for ($i = 0; $i < $za->numFiles; $i++) {
                $stat = $za->statIndex($i);
                if ($stat['size'] > 0) {
                    // Can't write directories, it'll be taken care of by the file
                    $this->line('... ' . Str::after($stat['name'], '/'));
                    $fullPath = storage_path('mdblog/') . Str::after($stat['name'], '/');
                    $contents = $za->getFromIndex($i);
                    $storage->put($fullPath, $contents);
                    $cnt++;
                }
            }
            $this->line('Downloaded ' . number_format($cnt) . ' files');
            $za->close();
        }
        $storage->delete(storage_path('mdblog/.gitdownload.zip'));

        event(new RepositoryDownloaded());

        $this->line('Rebuilding Cache');
        Artisan::call('mdblog:cache');

        return self::SUCCESS;
    }
}
