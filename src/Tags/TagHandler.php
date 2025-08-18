<?php

namespace Joby\Bracketeer\Tags;

use Stringable;

/**
 * Interface for handlers that can render Bracketeer tags, such as {{{link|link_slug|Link text}}}. These handlers are
 * passed a tag name and zero or more string arguments, as many as were in the tag as arguments. They should return a
 * string or Stringable that will be rendered as the tag's content.
 */
interface TagHandler
{
    public function render(string $tag, string ...$args): string|Stringable;
}