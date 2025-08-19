<?php

/**
 * Bracketeer: https://go.joby.lol/php-bracketeer
 * MIT License: Copyright (c) 2024 Joby Elliott
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Joby\Bracketeer\BracketeerMarkdown;

use Joby\Bracketeer\Bracketeer;
use League\CommonMark\Parser\Block\BlockStart;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;
use League\Config\ConfigurationAwareInterface;
use League\Config\ConfigurationInterface;

class BlockBracketeerStartParser implements BlockStartParserInterface, ConfigurationAwareInterface
{
    protected ConfigurationInterface $config;

    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->config = $configuration;
    }

    public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart
    {
        if ($cursor->isIndented()) return BlockStart::none();
        // short circuit if the first non-whitespace character is not a bracket
        if ($cursor->getNextNonSpaceCharacter() != '[') return BlockStart::none();
        // get the current line trimmed
        $line = trim($cursor->getLine());
        // try to match the entire line
        $matched = preg_match('/^' . Bracketeer::REGEX_BRACKETEER_TAG . '$/', $line);
        if (!$matched) return BlockStart::none();
        // ensure that there is a block tag handler for this tag name
        $parsed = Bracketeer::parseTag($line);
        $block_tags = $this->config->get('bracketeer')['block_tags'] ?? [];
        if (!array_key_exists($parsed['tag'], $block_tags)) return BlockStart::none();
        // advance to the end of the string, consuming the entire line
        $cursor->advanceToEnd();
        return BlockStart::of(
            new BlockBracketeerParser($parsed['tag'], $parsed['parts'])
        )->at($cursor);
    }
}