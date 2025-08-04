<?php

namespace Joby\bbMark\Tags;

use League\CommonMark\Node\Node;

class TagNode extends Node
{
    /**
     * @param array<string,string|null> $attributes
     */
    public function __construct(
        protected string      $tag,
        protected string|null $value,
        protected array       $attributes,
        protected bool        $block
    )
    {
        parent::__construct();
    }

    /**
     * The tag name of this shortcode, such as "link" for the tag `[link]`
     */
    public function tag(): string
    {
        return $this->tag;
    }

    /**
     * The main value of this shortcode, which is optionally specified by
     * putting it immediately after the tag name as if the tag name itself were
     * an attribute. For example `[tag="main value"]`
     *
     * Can optionally return a given default value if the main value is not set.
     * Note that unlike regular attributes, the main value will return the
     * default value here if it is set to an empty string.
     */
    public function value(string|null $default = null): string|null
    {
        return $this->value ?? $default;
    }

    /**
     * Check whether a given attribute was explicitly set in the shortcode.
     * Returns true if the attribute exists, even if it has no value or an empty
     * value. Useful for simple feature flag attributes.
     */
    public function hasAttr(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * Retrieve the value of a given attribute from the shortcode. Returns null
     * or an optional default value if the given attribute was not set.
     *
     * Note: This will return null for an attribute that was set with a null
     * value. If you only want to check if an attribute was set at all, and do
     * not care about its value, you should use `hasAttr()` instead.
     */
    public function attr(string $name, string|null $default = null): string|null
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        } else {
            return $default;
        }
    }
}
