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

namespace Joby\Bracketeer\Text;

use Joby\Bracketeer\Bracketeer;
use Joby\Bracketeer\ErrorBuilder;
use Joby\Bracketeer\LinkResolver;
use Joby\Bracketeer\Tags\TagHandler;
use League\Config\ConfigurationAwareInterface;
use League\Config\ConfigurationInterface;

/**
 * Parser for handling bbCode and wiki link type tags. This is intended to run inside the BracketeerTextRenderer so
 * that it runs on text nodes in the commonmark AST.
 */
class TextParser implements ConfigurationAwareInterface
{
    protected ConfigurationInterface $config;

    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->config = $configuration;
    }

    public function parse(string $text): string
    {
        return preg_replace_callback(
            '@(' . Bracketeer::REGEX_BRACKETEER_TAG . '|' . Bracketeer::REGEX_WIKILINK_TAG . ')@',
            function ($matches): string {
                $parts = explode('|', mb_substr($matches[1], 2, -2));
                if (str_starts_with($matches[0], '{')) return $this->bracketeerTag($parts);
                elseif (str_starts_with($matches[0], '[')) return $this->wikiLinkTag($parts);
                else return ErrorBuilder::inline('tag parsing error');
            },
            $text
        );
    }

    protected function bracketeerTag(array $parts): string
    {
        $handler = $this->config->get('bracketeer')['inline_tags'][$parts[0]] ?? null;
        if (!$handler) {
            return ErrorBuilder::inline('tag handler not found');
        }
        assert($handler instanceof TagHandler);
        return $handler->render(...$parts);
    }

    protected function wikiLinkTag(array $parts): string
    {
        if (str_ends_with($parts[0], '^')) {
            $parts[0] = substr($parts[0], 0, strlen($parts[0]) - 1);
            $new_window = true;
        } else {
            $new_window = false;
        }
        $url = $parts[0];
        $title = $parts[1];
        // route through central link renderer
        $resolver = $this->config->get('bracketeer')['link_resolver'];
        assert($resolver instanceof LinkResolver);
        return $resolver->render($url, $title, $title, $new_window);
    }
}