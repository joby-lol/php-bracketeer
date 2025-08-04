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

namespace Joby\bbMark;

use Joby\bbMark\TagBuilders\LinkTagBuilder;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\DescriptionList\DescriptionListExtension;
use League\CommonMark\Extension\DisallowedRawHtml\DisallowedRawHtmlExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\SmartPunct\SmartPunctExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Output\RenderedContentInterface;
use Stringable;

class bbMarkParser
{
    const array DEFAULT_CONFIG = [
        // TODO: how tags get configured might need to be totally different? we'll see I guess
        'bbmark' => [
            'tags' => [
                'link' => LinkTagBuilder::class,
            ]
        ],
        'renderer' => [
            'block_separator' => PHP_EOL,
            'inner_separator' => PHP_EOL,
            'soft_break' => PHP_EOL,
        ],
        'allow_unsafe_links' => false,
        'enable_smart_punctuation' => false,
        'enable_attributes' => false,
        'html_input' => 'strip',
        'table_of_contents' => [
            'position' => 'placeholder',
            'placeholder' => '[TOC]',
        ]
    ];

    protected MarkdownConverter|null $commonmark = null;

    /**
     * @param array<string,mixed> $config
     *
     * @return void
     */
    public function __construct(protected array $config = [])
    {
        $this->config = array_replace_recursive(
            static::DEFAULT_CONFIG,
            $this->config
        );
    }

    public function parse(string|Stringable $content): RenderedContentInterface
    {
        return $this->converter()->convert($content);
    }

    protected function converter(): MarkdownConverter
    {
        if (is_null($this->commonmark)) {
            $environment = new Environment($this->config);
            // TODO: there will probably only be one custom extension, since all the additional features will happen just in the updated text renderer
            // add our custom extension
            $environment->addExtension(new bbMarkExtension);
            // add basic markdown/html extensions
            $environment->addExtension(new MarkdownExtension);
            $environment->addExtension(new FootnoteExtension);
            $environment->addExtension(new HeadingPermalinkExtension);
            $environment->addExtension(new TableOfContentsExtension);
            $environment->addExtension(new TableExtension);
            $environment->addExtension(new DescriptionListExtension);
            if ($this->config['html_input'] == 'allow') {
                $environment->addExtension(new HtmlExtension);
                $environment->addExtension(new DisallowedRawHtmlExtension);
            }
            // add additional configurable extensions
            if ($this->config['enable_attributes']) {
                $environment->addExtension(new AttributesExtension);
            }
            if ($this->config['enable_smart_punctuation']) {
                $environment->addExtension(new SmartPunctExtension);
            }
            if ($this->config['enable_front_matter']) {
                $environment->addExtension(new FrontMatterExtension);
            }
            // build converter
            $this->commonmark = new MarkdownConverter($environment);
        }
        // return finished converter
        return $this->commonmark;
    }
}
