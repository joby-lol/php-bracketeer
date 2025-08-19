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

use League\CommonMark\Util\RegexHelper;
use League\Config\ConfigurationInterface;
use Stringable;

/**
 * This class is used to resolve embeddable elements in tags, and to provide a consistent single point for rendering
 * them into HTML. This is also where you add your own custom embed resolvers if you wish, for example to hook into your
 * own CMS's page IDs or slugs so they can be used as links.
 */
class EmbedResolver
{
    /**
     * Default templates for rendering embeddable elements. They may include placeholders for the URL and title, and can
     * be overridden by adding your own templates to the TEMPLATES constant in a subclass. If you want to add your own
     * templates in a more flexible way, you can override the buildHtml() method in this class to handle converting
     * ResolvedMedia objects to HTML using any logic you want.
     *
     * @var array<string, string>
     */
    const  TEMPLATES_BLOCK = [
        'image' => '<figure class="bracketeer-embed bracketeer-embed--image"><img src="{url}" alt="{title}" />{caption}</figure>',
        'video' => '<figure class="bracketeer-embed bracketeer-embed--video"><video src="{url}" controls></video>{caption}</figure>',
        'audio' => '<figure class="bracketeer-embed bracketeer-embed--audio"><audio src="{url}" controls></audio>{caption}</figure>',
        'link' => '<figure class="bracketeer-embed bracketeer-embed--link"><a href="{url}">{title}</a>{caption}</figure>',
    ];
    const  TEMPLATES_INLINE = [
        'image' => '<img src="{url}" alt="{title}" />',
        'link' => '<a href="{url}">{title}</a>',
    ];
    const  DEFAULT_BLOCK_TEMPLATE = '<figure class="bracketeer-embed bracketeer-embed--link"><a href="{url}">{title}</a>{caption}</figure>';
    const  DEFAULT_INLINE_TEMPLATE = '<a href="{url}">{title}</a>';
    protected ConfigurationInterface $config;
    protected array $resolvers = [];

    /**
     * @param callable(string):ResolvedLink|null ...$resolvers
     */
    public function __construct(callable ...$resolvers)
    {
        $this->resolvers = $resolvers;
    }

    public function addResolver(callable $resolver): static
    {
        $this->resolvers[] = $resolver;
        return $this;
    }

    public function resolve(string $url): ResolvedEmbed|null
    {
        foreach ($this->resolvers as $resolver) {
            $resolved = $resolver($url);
            if ($resolved) return $resolved;
        }
        return $this->defaultResolve($url);
    }

    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->config = $configuration;
    }

    public function buildHtml(ResolvedEmbed $media, string|null $title, bool $block): string|Stringable|null
    {
        if ($block) $template = self::TEMPLATES_BLOCK[$media->type] ?? self::DEFAULT_BLOCK_TEMPLATE;
        else $template = self::TEMPLATES_INLINE[$media->type] ?? self::DEFAULT_INLINE_TEMPLATE;
        if (!$template) return null;
        return str_replace(
            [
                '{url}',
                '{title}',
                '{caption}',
            ],
            [
                $media->url,
                $title ?? $media->title,
                $media->caption ? sprintf('<figcaption>%s</figcaption>', $media->caption) : '',
            ],
            $template
        );
    }

    public function render(string $url, string|null $title, bool $block): string|Stringable
    {
        $resolved = $this->resolve($url);
        if (!$resolved) return ErrorBuilder::block('Media could not be resolved');
        // forbid unsafe links
        if (!$resolved->trusted && !$this->config->get('allow_unsafe_links')) {
            if (RegexHelper::isLinkPotentiallyUnsafe($url)) {
                return ErrorBuilder::error('Untrusted and potentially unsafe media', $block);
            }
        }
        // render HTML
        return $this->buildHtml($resolved, $title, $block)
            ?? ErrorBuilder::error('Media could not be rendered', $block);
    }

    protected function defaultResolve(string $url): ResolvedEmbed|null
    {
        $title = basename($url) ?: $url;
        $type = 'link';
        $extension = strtolower(pathinfo($url, PATHINFO_EXTENSION));
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) $type = 'image';
        elseif (in_array($extension, ['mp4', 'webm', 'ogg', 'avi', 'mov', 'wmv', 'flv'])) $type = 'video';
        elseif (in_array($extension, ['mp3', 'wav', 'ogg', 'flac', 'aac'])) $type = 'audio';
        return new ResolvedEmbed($url, $title, null, $type);
    }
}