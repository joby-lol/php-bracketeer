<?php

namespace Joby\Bracketeer;

/**
 * Class to represent a media file that has been resolved by a system resolver.
 */
class ResolvedMedia
{
    /**
     * @var string|null The resolved URL of where this media can be viewed or downloaded independently.
     */
    public readonly string $url;
    /**
     * @var string|null The title of the media, if available, to be used in the link's title attribute, or potentially
     *      in other UI locations as needed. May be made publicly visible.
     */
    public readonly string|null $title;
    /**
     * @var string A string representing the "type" of the media, such as "image", "video", "audio", etc. This class
     *      includes templates for a few types, and if you want to add your own that is relatively easy by either
     *      overriding the TEMPLATES constant or the entire buildHtml() method in the MediaResolver class.
     */
    public readonly string $type;
    /**
     * @var array Any additional data that was passed to the resolver for rendering this media. This is not used by the
     *      default templates but may be useful for your own custom HTML-building processes if you are extending the
     *      MediaResolver class.
     */
    public readonly array $data;
    /**
     * @var bool Whether the media source is considered "trusted" and should not be checked for malicious content. Null
     *      will use the main config option.
     */
    public readonly bool|null $trusted;

    /**
     * @param string      $url      The resolved URL of where this media can be viewed or downloaded independently.
     * @param string|null $title    The title of the media, to be used in the link's title attribute, or potentially in
     *                              other UI locations as needed. May be made publicly visible.
     * @param string      $type     A string representing the "type" of the media, such as "image",
     *                              "video", "audio", etc. This class includes templates for a few types, and if you
     *                              want to add your own that is relatively easy by either overriding the TEMPLATES
     *                              constant or the entire buildHtml() method in the MediaResolver class.
     * @param array       $data     Any additional data that was passed to the resolver for rendering this media.
     *                              This is not used by the default templates but may be useful for your own custom
     *                              HTML-building processes if you are extending the MediaResolver class.
     * @param bool|null   $trusted  Whether the media source is considered "trusted" and should not be checked for
     *                              malicious content. Null will use the main config option.
     */
    public function __construct(
        string      $url,
        string|null $title,
        string      $type,
        array       $data = [],
        bool|null   $trusted = false
    )
    {
        $this->url = $url;
        $this->title = $title;
        $this->type = $type;
        $this->data = $data;
        $this->trusted = $trusted;
    }
}