<?php

namespace Joby\bbMark;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\CommonMark\Node;
use League\CommonMark\Extension\CommonMark\Parser;
use League\CommonMark\Extension\CommonMark\Renderer;
use League\CommonMark\Extension\ExtensionInterface;

/**
 * An extension to allow including HTML in your markdown.
 */
class HtmlExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment
            ->addBlockStartParser(new Parser\Block\HtmlBlockStartParser(), 40)
            ->addInlineParser(new Parser\Inline\HtmlInlineParser(), 40)
            ->addRenderer(Node\Block\HtmlBlock::class, new Renderer\Block\HtmlBlockRenderer())
            ->addRenderer(Node\Inline\HtmlInline::class, new Renderer\Inline\HtmlInlineRenderer());
    }
}
