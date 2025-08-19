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

namespace Joby\Bracketeer;

use Joby\Bracketeer\Tags\EmbedTagHandler;
use Joby\Bracketeer\Tags\LinkTagHandler;
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
use League\Config\ConfigurationAwareInterface;
use Stringable;

class Bracketeer
{
    const string REGEX_BRACKETEER_TAG = '\[([a-z0-9_]+)?\[(.+?(\|[^]]+)*)\]\]';
    const array DEFAULT_CONFIG = [
        'bracketeer' => [
            'inline_tags' => [
                'link' => LinkTagHandler::class,
                'embed' => EmbedTagHandler::class,
            ],
            'block_tags' => [
                'embed' => EmbedTagHandler::class,
            ],
            'link_resolver' => null,
            'embed_resolver' => null,
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
        ],
        'enable_front_matter' => false,
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
        $this->config['bracketeer']['link_resolver'] ??= new LinkResolver();
        $this->config['bracketeer']['embed_resolver'] ??= new EmbedResolver();
    }

    /**
     * @param string $tag
     *
     * @return array{tag:string,parts:array<string>}
     */
    public static function parseTag(string $tag): array
    {
        $tag = trim($tag);
        $tag = substr($tag, 1, strlen($tag) - 3);
        $tag = explode('[', $tag, 2);
        // get tag name
        $tag_name = $tag[0] ?: 'link';
        $tag_name = trim($tag_name);
        $tag_name = strtolower($tag_name);
        // parse parts/arguments
        $parts = explode('|', $tag[1]);
        $parts = array_map('trim', $parts);
        $parts = array_filter($parts, fn($part) => $part !== '');
        // return
        return [
            'tag' => $tag_name,
            'parts' => $parts,
        ];
    }

    public function parse(string|Stringable $content): RenderedContentInterface
    {
        return $this->converter()->convert($content);
    }

    protected function converter(): MarkdownConverter
    {
        if (is_null($this->commonmark)) {
            $environment = new Environment($this->config);
            // manually set config where necessary
            $this->config['bracketeer']['link_resolver']->setConfiguration($environment->getConfiguration());
            $this->config['bracketeer']['embed_resolver']->setConfiguration($environment->getConfiguration());
            // set config on inline and block tags if necessary
            foreach ($this->config['bracketeer']['inline_tags'] as $handler) {
                if ($handler instanceof ConfigurationAwareInterface) $handler->setConfiguration($environment->getConfiguration());
            }
            foreach ($this->config['bracketeer']['block_tags'] as $handler) {
                if ($handler instanceof ConfigurationAwareInterface) $handler->setConfiguration($environment->getConfiguration());
            }
            // add our custom Markdown extension that uses its own text renderer but is otherwise mostly stock
            $environment->addExtension(new BracketeerExtension());
            // add basic shared extensions
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
