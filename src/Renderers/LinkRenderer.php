<?php

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
