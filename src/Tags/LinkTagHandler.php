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

namespace Joby\Bracketeer\Tags;

use Joby\Bracketeer\LinkResolver;
use League\Config\ConfigurationAwareInterface;
use League\Config\ConfigurationInterface;
use Stringable;

class LinkTagHandler implements TagHandler, ConfigurationAwareInterface
{
    protected ConfigurationInterface $config;

    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->config = $configuration;
    }

    /**
     * @param array<string|int,string> $parts 
     */
    public function render(string $tag, array $parts, bool $block): string|Stringable
    {
        $url = $parts[0];
        $title = $parts[1] ?? null;
        if (str_ends_with($url, '^')) {
            $url = substr($url, 0, strlen($url) - 1);
            $new_window = true;
        } else {
            $new_window = false;
        }
        // build HTML
        // @phpstan-ignore-next-line it's definitely a LinkResolver
        $resolver = $this->config->get('bracketeer')['link_resolver'];
        assert($resolver instanceof LinkResolver);
        return $resolver->render($url, null, $title, $new_window);
    }
}