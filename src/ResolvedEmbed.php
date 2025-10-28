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
 * Class to represent an embeddable element that has been resolved by a system resolver.
 */
readonly class ResolvedEmbed
{
    /**
     * @var string The resolved URL of where this media can be viewed or downloaded independently.
     */
    public string $url;
    /**
     * @var string|null The title of the media, if available, to be used in the link's title attribute, or potentially
     *      in other UI locations as needed. May be made publicly visible.
     */
    public string|null $title;
    /**
     * @var string|null The caption of the embed, if available. This is included in the figcaption element of the
     *                  default templates, if provided.
     */
    public string|null $caption;
    /**
     * @var string A string representing the "type" of the media, such as "image", "video", "audio", etc. This class
     *      includes templates for a few types, and if you want to add your own, that is relatively easy by either
     *      overriding the TEMPLATES constant or the entire buildHtml() method in the MediaResolver class.
     */
    public string $type;
    /**
     * @var array<mixed> Any additional data that was passed to the resolver for rendering this media. This is not used by the
     *      default templates but may be useful for your own custom HTML-building processes if you are extending the
     *      MediaResolver class.
     */
    public array $data;
    /**
     * @var bool Whether the media source is considered "trusted" and should not be checked for malicious content. Null
     *      will use the main config option.
     */
    public bool|null $trusted;

    /**
     * @param string      $url      The resolved URL of where this media can be viewed or downloaded independently.
     * @param string|null $title    The title of the media, to be used in the link's title attribute, or potentially in
     *                              other UI locations as needed. May be made publicly visible.
     * @param string|null $caption  The caption of the embed, if available. This is included in the figcaption element
     *                              of the default templates, if provided.
     * @param string      $type     A string representing the "type" of the media, such as "image",
     *                              "video", "audio", etc. This class includes templates for a few types, and if you
     *                              want to add your own that is relatively easy by either overriding the TEMPLATES
     *                              constant or the entire buildHtml() method in the MediaResolver class.
     * @param array<mixed>       $data     Any additional data that was passed to the resolver for rendering this media.
     *                              This is not used by the default templates but may be useful for your own custom
     *                              HTML-building processes if you are extending the MediaResolver class.
     * @param bool|null   $trusted  Whether the media source is considered "trusted" and should not be checked for
     *                              malicious content. Null will use the main config option.
     */
    public function __construct(
        string      $url,
        string|null $title,
        string|null $caption,
        string      $type,
        array       $data = [],
        bool|null   $trusted = false
    )
    {
        $this->url = $url;
        $this->title = $title;
        $this->caption = $caption;
        $this->type = $type;
        $this->data = $data;
        $this->trusted = $trusted;
    }
}