<?php

namespace Joby\Bracketeer\MarkdownBlockTags;

use Joby\Bracketeer\Tags\TagHandler;
use League\CommonMark\Node\Block\AbstractBlock;

class BlockTag extends AbstractBlock
{
    public function __construct(
        public readonly array $parts,
    )
    {
        parent::__construct();
    }
}