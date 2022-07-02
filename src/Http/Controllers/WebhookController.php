<?php

namespace Aelora\MarkdownBlog\Http\Controllers;

use Illuminate\Routing\Controller;
use Aelora\MarkdownBlog\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class WebhookController extends Controller
{

    public function index(Request $request)
    {
        if (!config('mdblog.webhook.key', false)) {
            die('Webhook key not set');
        }
        $sig_check = 'sha1=' . hash_hmac('sha1', request()->getContent(), config('mdblog.webhook.key'));

        if (hash_equals($sig_check, request()->header('x-hub-signature'))) {
            Artisan::call('mdblog:download');
            die('done');
        } else {
            header('HTTP/1.1 403');
            die('invalid');
        }
    }
}
