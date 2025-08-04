<?php

namespace Joby\bbMark\WikiTags;

use Joby\bbMark\Nodes\Link;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;

class InlineWikiTagParser implements InlineParserInterface
{
    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::regex('\[\[.+?\^?(\|.*?)*\]\]');
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $match = $inlineContext->getFullMatch();
        $parts = explode('|', mb_substr($match, 2, mb_strlen($match) - 4));
        if (str_ends_with($parts[0], '^')) {
            $parts[0] = substr($parts[0], 0, strlen($parts[0]) - 1);
            $new_window = true;
        } else {
            $new_window = false;
        }
        $inlineContext->getCursor()->advanceBy($inlineContext->getFullMatchLength());
        $inlineContext->getContainer()->appendChild(
            new Link($parts[0], @$parts[1], $new_window)
        );
        return false;
    }
}
