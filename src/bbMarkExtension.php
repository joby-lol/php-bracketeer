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
