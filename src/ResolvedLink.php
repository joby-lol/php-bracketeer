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

/**
 * Class to represent a link that has been resolved by a system resolver.
 */
readonly class ResolvedLink
{
    /**
     * @var string The full resolved URL
     */
    public string $url;
    /**
     * @var string|null The title of the link, if available, to be used in the link's title attribute
     */
    public string|null $title;
    /**
     * @var string|null The default text to be used for the link, if available
     */
    public string|null $default_text;
    /**
     * @var bool|null Whether the link should open in a new window, if there should be an opinion on this
     */
    public bool|null $new_window;
    /**
     * @var bool Whether the link is considered "trusted" and should not be checked for malicious content. Null will
     *      use the main config option.
     */
    public bool|null $trusted;

    /**
     * @param string      $url          The full resolved URL
     * @param string|null $title        The title of the link, if available, to be used in the link's title attribute'
     * @param string|null $default_text The default text to be used for the link, if available
     * @param bool|null   $new_window   Whether the link should open in a new window, if there should be an opinion
     * @param bool|null   $trusted      Whether the link is considered "trusted" and should not be checked for
     *                                  malicious content. Null will use the main config option.
     */
    public function __construct(
        string      $url,
        string|null $title,
        string|null $default_text,
        bool|null   $new_window,
        bool|null   $trusted = false
    )
    {
        $this->url = $url;
        $this->title = $title;
        $this->default_text = $default_text;
        $this->new_window = $new_window;
        $this->trusted = $trusted;
    }
}