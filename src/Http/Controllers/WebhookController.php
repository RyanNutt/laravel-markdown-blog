<?php

namespace Aelora\MarkdownBlog\Http\Controllers;

use Illuminate\Routing\Controller;
use Aelora\MarkdownBlog\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class WebhookController extends Controller
{

    public function index(Request $request)
    {
        if (!config('mdblog.webhook.key', false)) {
            die('Webhook key not set');
        }
        $sig_check = 'sha1=' . hash_hmac('sha1', request()->getContent(), config('mdblog.webhook.key'));

        if (hash_equals($sig_check, request()->header('x-hub-signature'))) {
            // Clear cached hash so the download is "dirty"
            Cache::forget('mdblog.repository.hash');

            if (config('mdblog.webhook.download', true)) {
                Artisan::call('mdblog:download');
                die('updated from repository');
            } else {
                die('download cached hash cleared');
            }
        } else {
            header('HTTP/1.1 403');
            die('invalid');
        }
    }
}
