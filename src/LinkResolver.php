<?php

namespace Joby\Bracketeer;

use League\CommonMark\Util\HtmlElement;
use League\CommonMark\Util\RegexHelper;
use League\Config\ConfigurationAwareInterface;
use League\Config\ConfigurationInterface;
use Stringable;

/**
 * This class is used to resolve links in tags, and to provide a consistent single point for rendering them into HTML.
 * This is also where you add your own custom link resolvers if you wish, for example to hook into your own CMS's page
 * IDs or slugs so they can be used as links.
 */
class LinkResolver implements ConfigurationAwareInterface
{
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

    public function resolve(string $url): ResolvedLink|null
    {
        foreach ($this->resolvers as $resolver) {
            $resolved = $resolver($url);
            if ($resolved) return $resolved;
        }
        return null;
    }

    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->config = $configuration;
    }

    public function render(string $url, string|null $title, string|null $content, bool $new_window, bool $trusted = false): string|Stringable
    {
        // attempt to resolve using any registered link resolvers
        $resolved = $this->resolve($url);
        if ($resolved) {
            $url = $resolved->url;
            $title = $resolved->title;
            $content = $content ?? $resolved->default_text;
            $new_window = $resolved->new_window ?? $new_window;
            $trusted = $resolved->trusted ?? $trusted;
        }
        // ensure we have content
        if (!$title && !$content) {
            $content = $url;
            $title = sprintf('[%s]', $url);
        } elseif (!$content) {
            $content = $title;
        } elseif (!$title) {
            $title = $content;
        }
        // add URL to title if it's missing and not redundant
        if ($title != $url && !str_ends_with($title, sprintf('[%s]', $url))) {
            $title .= sprintf(' [%s]', $url);
        }
        // forbid unsafe links
        if (!$trusted && !$this->config->get('allow_unsafe_links')) {
            if (RegexHelper::isLinkPotentiallyUnsafe($url)) {
                return ErrorBuilder::inline('Untrusted and potentially unsafe link');
            }
        }
        // generate HTML
        $attrs = [
            'href' => $url,
            'title' => $title,
        ];
        if ($new_window) {
            $attrs['target'] = '_blank';
        }
        $attrs = array_map(html_entity_decode(...), $attrs);
        return new HtmlElement('a', $attrs, $content);
    }
}