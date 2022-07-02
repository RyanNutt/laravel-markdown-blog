<?php

namespace Aelora\MarkdownBlog\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class RepositoryDownloaded
{
    use Dispatchable, SerializesModels;

    public function __construct()
    {
    }
}
