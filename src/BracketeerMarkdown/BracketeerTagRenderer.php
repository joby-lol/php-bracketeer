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

namespace Joby\Bracketeer\BracketeerMarkdown;

use Joby\Bracketeer\ErrorBuilder;
use Joby\Bracketeer\Tags\TagHandler;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\Config\ConfigurationAwareInterface;
use League\Config\ConfigurationInterface;
use RuntimeException;
use Stringable;

class BracketeerTagRenderer implements NodeRendererInterface, ConfigurationAwareInterface
{
    /** @var array<string, TagHandler|class-string<TagHandler>|null> $block_handlers */
    protected array $block_handlers = [];
    /** @var array<string, TagHandler|class-string<TagHandler>|null> $inline_handlers */
    protected array $inline_handlers = [];
    private ConfigurationInterface $config;

    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->config = $configuration;
    }

    /**
     * @param BracketeerTagBlock|BracketeerTagInline $node
     *
     * @noinspection PhpDocSignatureInspection
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string|Stringable
    {

        if ($node instanceof BracketeerTagBlock) {
            $handler = $this->blockHandler($node->tag);
            if (!$handler) return ErrorBuilder::block('No block handler for tag: ' . htmlspecialchars($node->tag));
            return $handler->render($node->tag, $node->parts, true);
        } elseif ($node instanceof BracketeerTagInline) {
            $handler = $this->inlineHandler($node->tag);
            if (!$handler) return ErrorBuilder::inline('No inline handler for tag: ' . htmlspecialchars($node->tag));
            return $handler->render($node->tag, $node->parts, false);
        } else {
            throw new RuntimeException('Invalid node type: ' . $node::class);
        }
    }

    protected function blockHandler(string $tag): TagHandler|null
    {
        if (!isset($this->block_handlers[$tag])) {
            /** @var TagHandler|class-string<TagHandler>|null $handler */
            // @phpstan-ignore-next-line it's definitely an array
            $handler = $this->config->get('bracketeer')['block_tags'][$tag];
            if (is_null($handler)) {
                $this->block_handlers[$tag] = null;
            } else {
                if (is_string($handler)) {
                    $handler = new $handler;
                }
                if ($handler instanceof ConfigurationAwareInterface) {
                    $handler->setConfiguration($this->config);
                }
                $this->block_handlers[$tag] = $handler;
            }
        }
        // @phpstan-ignore-next-line it's definitely set to an object now
        return $this->block_handlers[$tag];
    }

    protected function inlineHandler(string $tag): TagHandler|null
    {
        if (!isset($this->inline_handlers[$tag])) {
            /** @var TagHandler|class-string<TagHandler>|null $handler */
            // @phpstan-ignore-next-line it's definitely an array
            $handler = $this->config->get('bracketeer')['inline_tags'][$tag];
            if (is_null($handler)) {
                $this->inline_handlers[$tag] = null;
            } else {
                if (is_string($handler)) {
                    $handler = new $handler;
                }
                if ($handler instanceof ConfigurationAwareInterface) {
                    $handler->setConfiguration($this->config);
                }
                $this->inline_handlers[$tag] = $handler;
            }
        }
        // @phpstan-ignore-next-line it's definitely set to an object now
        return $this->inline_handlers[$tag];
    }
}