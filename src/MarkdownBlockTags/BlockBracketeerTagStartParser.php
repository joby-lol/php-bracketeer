<?php

namespace Joby\Bracketeer\MarkdownBlockTags;

use Joby\Bracketeer\Bracketeer;
use League\CommonMark\Parser\Block\BlockStart;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;
use League\CommonMark\Util\RegexHelper;
use League\Config\ConfigurationAwareInterface;
use League\Config\ConfigurationInterface;

class BlockBracketeerTagStartParser implements BlockStartParserInterface, ConfigurationAwareInterface
{
    protected ConfigurationInterface $config;

    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->config = $configuration;
    }

    public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart
    {
        if ($cursor->isIndented()) {
            return BlockStart::none();
        }
        $match = RegexHelper::matchFirst(
            Bracketeer::REGEX_BRACKETEER_TAG,
            $cursor->getLine(),
            $cursor->getNextNonSpacePosition()
        );
        if ($match === null) {
            return BlockStart::none();
        }
        // advance to the end of the string, consuming the entire line
        $cursor->advanceToEnd();
        return BlockStart::of(
            new BlockBracketeerTagParser($match[1], explode('|', $match[2]))
        )->at($cursor);
    }
}