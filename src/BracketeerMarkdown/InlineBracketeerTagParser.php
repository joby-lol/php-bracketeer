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
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;
use League\Config\ConfigurationAwareInterface;
use League\Config\ConfigurationInterface;

class InlineBracketeerTagParser implements InlineParserInterface, ConfigurationAwareInterface
{
    protected ConfigurationInterface $config;

    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->config = $configuration;
    }

    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::regex(Bracketeer::REGEX_BRACKETEER_TAG);
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $matches = $inlineContext->getMatches();
        // Parse and verify tag is a configured block tag
        $parsed = Bracketeer::parseTag($matches[0]);
        $inlineTags = $this->config->get('bracketeer')['inline_tags'] ?? [];
        if (!array_key_exists($parsed['tag'], $inlineTags)) {
            return false;
        }
        $inlineContext->getCursor()->advanceBy(strlen($matches[0]));
        $inlineContext->getContainer()->appendChild(new BracketeerTagInline($parsed['tag'], $parsed['parts']));
        return true;
    }
}