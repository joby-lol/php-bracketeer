<?php

namespace Joby\bbMark;

use Joby\bbMark\Nodes\Link;
use Joby\bbMark\Renderers\LinkRenderer;
use Joby\bbMark\TagBuilders\TagBuilderInterface;
use Joby\bbMark\Tags\InlineTagParser;
use Joby\bbMark\Tags\TagNode;
use Joby\bbMark\Tags\TagRenderer;
use Joby\bbMark\WikiTags\InlineWikiTagParser;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ConfigurableExtensionInterface;
use League\Config\ConfigurationBuilderInterface;
use Nette\Schema\Expect;

class bbMarkExtension implements ConfigurableExtensionInterface
{
    public function configureSchema(ConfigurationBuilderInterface $builder): void
    {
        $builder->addSchema('bbmark', Expect::structure([
            'tags' => Expect::arrayOf(
                Expect::string()->assert(fn($class) => is_a($class, TagBuilderInterface::class, true)),
                Expect::string()->pattern('[a-z][a-z0-9]*')
            )
        ]));
    }

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment
            ->addInlineParser(
                new InlineTagParser,
                -100
            )
            ->addInlineParser(
                new InlineWikiTagParser,
                -100
            )
            ->addRenderer(
                TagNode::class,
                new TagRenderer()
            )
            ->addRenderer(
                Link::class,
                new LinkRenderer
            );
    }
}
