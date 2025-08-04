<?php

/**
 * bbMark: https://go.joby.lol/php-bbmark
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

namespace Joby\bbMark\Renderers;

use Joby\bbMark\ErrorBuilder;
use Joby\bbMark\Nodes\Link;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use League\CommonMark\Util\RegexHelper;
use League\Config\ConfigurationAwareInterface;
use League\Config\ConfigurationInterface;

class LinkRenderer implements NodeRendererInterface, ConfigurationAwareInterface
{
    private ConfigurationInterface $config;

    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->config = $configuration;
    }

    /**
     * @param Link $node
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        Link::assertInstanceOf($node);
        // set up attributes
        $attrs = [];
        $attrs['href'] = $node->getUrl();
        // title
        if ($node->title) {
            $attrs['title'] = sprintf('%s [%s]', $node->title, $node->getUrl());
        } else {
            $attrs['title'] = sprintf('[%s]', $node->getUrl());
        }
        // new windows
        if ($node->new_window) {
            $attrs['target'] = '_blank';
        }
        // forbid unsafe links
        if (!$this->config->get('allow_unsafe_links') || RegexHelper::isLinkPotentiallyUnsafe($attrs['href'])) {
            return ErrorBuilder::inline('potentially unsafe link');
        }
        // render to HTML element
        if ($node->hasChildren()) {
            return new HtmlElement('a', $attrs, $childRenderer->renderNodes($node->children()));
        } else {
            return new HtmlElement('a', $attrs, $node->title ?: $node->getUrl());
        }
    }
}
