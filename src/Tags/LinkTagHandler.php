<?php

namespace Joby\Bracketeer\Tags;

use Joby\Bracketeer\LinkResolver;
use League\Config\ConfigurationAwareInterface;
use League\Config\ConfigurationInterface;
use Stringable;

class LinkTagHandler implements TagHandler, ConfigurationAwareInterface
{
    protected ConfigurationInterface $config;

    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->config = $configuration;
    }

    public function render(string $tag, string ...$args): string|Stringable
    {
        $url = $args[0];
        $title = $args[1] ?? null;
        if (str_ends_with($url, '^')) {
            $url = substr($url, 0, strlen($url) - 1);
            $new_window = true;
        } else {
            $new_window = false;
        }
        // build HTML
        $resolver = $this->config->get('bracketeer')['link_resolver'];
        assert($resolver instanceof LinkResolver);
        return $resolver->render($url, null, $title, $new_window);
    }
}