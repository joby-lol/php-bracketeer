<?php

namespace Joby\bbMark\Nodes;

use League\CommonMark\Extension\CommonMark\Node\Inline\AbstractWebResource;

class Link extends AbstractWebResource
{
    public function __construct(
        string                      $url,
        public readonly string|null $title,
        public readonly bool        $new_window,
    )
    {
        parent::__construct($url);
    }
}
