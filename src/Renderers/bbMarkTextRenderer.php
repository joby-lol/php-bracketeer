<?php

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