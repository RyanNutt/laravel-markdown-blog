<?php

namespace Aelora\MarkdownBlog\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class DownloadStart
{
    use Dispatchable, SerializesModels;

    public function __construct()
    {
    }
}
