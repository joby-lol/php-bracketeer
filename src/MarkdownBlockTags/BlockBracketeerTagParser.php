<?php

namespace Joby\Bracketeer\MarkdownBlockTags;

use Joby\Bracketeer\Tags\TagHandler;
use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Parser\Block\AbstractBlockContinueParser;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Cursor;

class BlockBracketeerTagParser extends AbstractBlockContinueParser
{
    protected BlockTag $block;

    public function __construct(string $tag, array $parts)
    {
        $this->block = new BlockTag($tag, $parts);
    }

    public function getBlock(): AbstractBlock
    {
        return $this->block;
    }

    public function tryContinue(Cursor $cursor, BlockContinueParserInterface $activeBlockParser): ?BlockContinue
    {
        // Bracketeer tags are always one line, so always fail to match
        return BlockContinue::none();
    }
}