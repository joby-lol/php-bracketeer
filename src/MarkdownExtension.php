<?php

namespace Joby\bbMark;

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
class MarkdownExtension implements ConfigurableExtensionInterface
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
    }

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment
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
            ->addRenderer(CoreNode\Inline\Newline::class, new CoreRenderer\Inline\NewlineRenderer())
            ->addRenderer(Node\Inline\Strong::class, new Renderer\Inline\StrongRenderer())
            // TODO: explore removing textrenderer here and replacing it with a custom one that does bbcode and wiki tags
            ->addRenderer(CoreNode\Inline\Text::class, new CoreRenderer\Inline\TextRenderer());
        if ($environment->getConfiguration()->get('commonmark/use_asterisk')) {
            $environment->addDelimiterProcessor(new EmphasisDelimiterProcessor('*'));
        }
        if ($environment->getConfiguration()->get('commonmark/use_underscore')) {
            $environment->addDelimiterProcessor(new EmphasisDelimiterProcessor('_'));
        }
    }
}
