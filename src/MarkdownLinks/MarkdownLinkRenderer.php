<?php

namespace Joby\Bracketeer\MarkdownLinks;

use Joby\Bracketeer\LinkResolver;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\Config\ConfigurationAwareInterface;
use League\Config\ConfigurationInterface;

class MarkdownLinkRenderer implements NodeRendererInterface, ConfigurationAwareInterface
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
        $resolver = $this->config->get('bracketeer')['link_resolver'];
        assert($resolver instanceof LinkResolver);
        return $resolver->render(
            $node->getUrl(),
            $node->getTitle(),
            $node->hasChildren() ? $childRenderer->renderNodes($node->children()) : null,
            false,
        );
    }
}