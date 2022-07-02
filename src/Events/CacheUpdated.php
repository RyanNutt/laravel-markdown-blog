<?php

namespace Aelora\MarkdownBlog\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class CacheUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct()
    {
    }
}
