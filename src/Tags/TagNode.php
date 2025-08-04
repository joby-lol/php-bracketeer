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
