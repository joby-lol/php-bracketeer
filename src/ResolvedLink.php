<?php

namespace Joby\Bracketeer;

/**
 * Class to represent a link that has been resolved by a system resolver.
 */
class ResolvedLink
{
    /**
     * @var string The full resolved URL
     */
    public readonly string $url;
    /**
     * @var string|null The title of the link, if available, to be used in the link's title attribute
     */
    public readonly string|null $title;
    /**
     * @var string|null The default text to be used for the link, if available
     */
    public readonly string|null $default_text;
    /**
     * @var bool|null Whether the link should open in a new window, if there should be an opinion on this
     */
    public readonly bool|null $new_window;
    /**
     * @var bool Whether the link is considered "trusted" and should not be checked for malicious content. Null will
     *      use the main config option.
     */
    public readonly bool|null $trusted;

    /**
     * @param string      $url          The full resolved URL
     * @param string|null $title        The title of the link, if available, to be used in the link's title attribute'
     * @param string|null $default_text The default text to be used for the link, if available
     * @param bool|null   $new_window   Whether the link should open in a new window, if there should be an opinion
     * @param bool        $trusted      Whether the link is considered "trusted" and should not be checked for
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