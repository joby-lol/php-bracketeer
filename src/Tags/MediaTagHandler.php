<?php

namespace Joby\Bracketeer\Tags;

use Joby\Bracketeer\MediaResolver;
use League\Config\ConfigurationAwareInterface;
use League\Config\ConfigurationInterface;
use Stringable;

class MediaTagHandler implements TagHandler, ConfigurationAwareInterface
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
        // build HTML
        $resolver = $this->config->get('bracketeer')['media_resolver'];
        assert($resolver instanceof MediaResolver);
        return $resolver->render($url, $title);
    }
}
