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

use League\CommonMark\Extension\CommonMark\Node\Block\HtmlBlock;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\Xml;
use League\CommonMark\Xml\XmlNodeRendererInterface;
use Stringable;

/**
 * TODO: make this parse HTML content for bbcode and wiki links, and then replace the core text renderer with it in MarkdownExtension
 * Also once this is working we can get rid of the inline tag parser, and probably redo the hooks for tags to allow hooks that work for catch-all tags!
 * Also as part of this we'll have to fully refactor and remove the InlineWikiTagParser with our own probably regex-based thing
 */
class bbMarkTextRenderer implements NodeRendererInterface, XmlNodeRendererInterface
{
    /**
     * @param Node $node
     *
     * @return null|string|Stringable
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        Text::assertInstanceOf($node);
        if ($node instanceof HtmlBlock) {
            $text = $node->getLiteral();
        } else {
            $text = $node->getLiteral();
        }
        return Xml::escape($node->getLiteral());
    }

    public function getXmlTagName(Node $node): string
    {
        return 'text';
    }

    public function getXmlAttributes(Node $node): array
    {
        return [];
    }
}