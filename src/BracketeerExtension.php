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

use Joby\Bracketeer\BracketeerMarkdown\BlockBracketeerStartParser;
use Joby\Bracketeer\BracketeerMarkdown\BracketeerTagBlock;
use Joby\Bracketeer\BracketeerMarkdown\BracketeerTagInline;
use Joby\Bracketeer\BracketeerMarkdown\BracketeerTagRenderer;
use Joby\Bracketeer\BracketeerMarkdown\InlineBracketeerTagParser;
use Joby\Bracketeer\BracketeerMarkdown\LinkResolverAndRenderer;
use Joby\Bracketeer\Tags\TagHandler;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\CommonMark\Delimiter\Processor\EmphasisDelimiterProcessor;
use League\CommonMark\Extension\CommonMark\Node;
use League\CommonMark\Extension\CommonMark\Parser;
use League\CommonMark\Extension\CommonMark\Renderer;
use League\CommonMark\Extension\ConfigurableExtensionInterface;
use League\CommonMark\Node as CoreNode;
use League\CommonMark\Parser as CoreParser;
use League\CommonMark\Renderer as CoreRenderer;
use League\Config\ConfigurationBuilderInterface;
use Nette\Schema\Expect;

/**
 * An extension to configure a bare-minimum Markdown rendering setup with no
 * configurability. This will enable basic text formatting only.
 */
class BracketeerExtension implements ConfigurableExtensionInterface
{
    public function configureSchema(ConfigurationBuilderInterface $builder): void
    {
        $builder->addSchema('commonmark', Expect::structure([
            'use_asterisk' => Expect::bool(true),
            'use_underscore' => Expect::bool(true),
            'enable_strong' => Expect::bool(true),
            'enable_em' => Expect::bool(true),
            'unordered_list_markers' => Expect::listOf('string')->min(1)->default(['*'])->mergeDefaults(false)
        ]));
        $builder->addSchema('bracketeer', Expect::structure([
            'block_tags' => Expect::arrayOf(
                Expect::anyOf(
                    Expect::type(TagHandler::class),
                    Expect::string()->assert(fn(string $value) => is_a($value, TagHandler::class, true))
                ),
                Expect::string()->pattern('[a-z0-9_]+')
            ),
            'inline_tags' => Expect::arrayOf(
                Expect::anyOf(
                    Expect::type(TagHandler::class),
                    Expect::string()->assert(fn(string $value) => is_a($value, TagHandler::class, true))
                ),
                Expect::string()->pattern('[a-z0-9_]+')
            ),
            'link_resolver' => Expect::type(LinkResolver::class),
            'embed_resolver' => Expect::type(EmbedResolver::class),
        ]));
    }

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $tag_renderer = new BracketeerTagRenderer();
        $environment
            // more or less the standard commonmark parsers
            ->addBlockStartParser(new Parser\Block\BlockQuoteStartParser(), 70)
            ->addBlockStartParser(new Parser\Block\HeadingStartParser(), 60)
            ->addBlockStartParser(new Parser\Block\FencedCodeStartParser(), 50)
            ->addBlockStartParser(new Parser\Block\ThematicBreakStartParser(), 20)
            ->addBlockStartParser(new Parser\Block\ListBlockStartParser(), 10)
            ->addBlockStartParser(new Parser\Block\IndentedCodeStartParser(), -100)
            ->addInlineParser(new CoreParser\Inline\NewlineParser(), 200)
            ->addInlineParser(new Parser\Inline\BacktickParser(), 150)
            ->addInlineParser(new Parser\Inline\EscapableParser(), 80)
            ->addInlineParser(new Parser\Inline\EntityParser(), 70)
            ->addInlineParser(new Parser\Inline\AutolinkParser(), 50)
            ->addInlineParser(new Parser\Inline\CloseBracketParser(), 30)
            ->addInlineParser(new Parser\Inline\OpenBracketParser(), 20)
            ->addInlineParser(new Parser\Inline\BangParser(), 10)
            // more or less the standard commonmark renderers
            ->addRenderer(CoreNode\Inline\Text::class, new CoreRenderer\Inline\TextRenderer())
            ->addRenderer(Node\Block\BlockQuote::class, new Renderer\Block\BlockQuoteRenderer())
            ->addRenderer(CoreNode\Block\Document::class, new CoreRenderer\Block\DocumentRenderer())
            ->addRenderer(Node\Block\FencedCode::class, new Renderer\Block\FencedCodeRenderer())
            ->addRenderer(Node\Block\Heading::class, new Renderer\Block\HeadingRenderer())
            ->addRenderer(Node\Block\IndentedCode::class, new Renderer\Block\IndentedCodeRenderer())
            ->addRenderer(Node\Block\ListBlock::class, new Renderer\Block\ListBlockRenderer())
            ->addRenderer(Node\Block\ListItem::class, new Renderer\Block\ListItemRenderer())
            ->addRenderer(CoreNode\Block\Paragraph::class, new CoreRenderer\Block\ParagraphRenderer())
            ->addRenderer(Node\Block\ThematicBreak::class, new Renderer\Block\ThematicBreakRenderer())
            ->addRenderer(Node\Inline\Code::class, new Renderer\Inline\CodeRenderer())
            ->addRenderer(Node\Inline\Emphasis::class, new Renderer\Inline\EmphasisRenderer())
            // TODO: explore a way of resolving Markdown images as full-on bracketeer embeds
            ->addRenderer(Node\Inline\Image::class, new Renderer\Inline\ImageRenderer())
            ->addRenderer(CoreNode\Inline\Newline::class, new CoreRenderer\Inline\NewlineRenderer())
            ->addRenderer(Node\Inline\Strong::class, new Renderer\Inline\StrongRenderer())
            // custom bracketeer tag parsers
            ->addInlineParser(new InlineBracketeerTagParser(), 60)
            ->addBlockStartParser(new BlockBracketeerStartParser(), 60)
            // custom bracketeer tag renderers
            ->addRenderer(BracketeerTagBlock::class, $tag_renderer)
            ->addRenderer(BracketeerTagInline::class, $tag_renderer)
            // custom markdown syntax link renderer to unify link resolution and rendering
            ->addRenderer(Node\Inline\Link::class, new LinkResolverAndRenderer());
        // emphasis processing now happens differently because of that security thing where tons of asterisks blows up CPU use
        if ($environment->getConfiguration()->get('commonmark/use_asterisk')) {
            $environment->addDelimiterProcessor(new EmphasisDelimiterProcessor('*'));
        }
        if ($environment->getConfiguration()->get('commonmark/use_underscore')) {
            $environment->addDelimiterProcessor(new EmphasisDelimiterProcessor('_'));
        }
    }
}
