# bbMark

bbMark is a web-authoring markup language designed to combine the best features of Markdown and bbCode-style shortcodes
into one intuitive and flexible plaintext web-authoring language that can be easily integrated into a variety of
contexts.

## Development status

This library is still very much under **active development** and should not be used in production. There is likely to be
a large update in the future in which the architecture changes significantly around how bbcode and wiki link tags are
parsed.

## Installation

Install with `composer install joby/bbmark`

Note that this language is **very much** designed to be integrated into a larger CMS of some kind. Its primary
benefit is integrating with your own URL/slug resolution system, for both links to pages
and embedding media. For example, if you are building your own CMS, it could integrate in a way that wiki links,
Markdown links, and bbcode links _all_ share a common content ID resolution system, and all behave consistently. If your
CMS has similar features, bbMark is also capable of integrating media IDs for embedding media using a variety of
different tags.

## Basic usage

```php
$bb = new Joby\bbMarkParser;
$rendered = $bb->parse("# bbMark document");
// the rendered document can be echoed directly as a string
echo $rendered;
// front matter may also be included, which can be retrieved via
var_dump($rendered->getFrontMatter());
```

## Basic text formatting

For basic text formatting, you can use Markdown, exactly like you're probably used to from ... kind of everywhere else
on
the internet.

## Wiki-style links

One straightforward and succinct way to make links in bbMark is using wiki-style links, like `[[link]]`. In bbMark the
link is
by default treated as URL. It is also possible to plug in your own slug-to-URL resolver so that your own CMS can take a
given slug and convert it into a URL and link title.

Wiki-style links can also include an optional alternative display text, which will override any default, like
`[[link_slug|Alternative display text]]`

Wiki-style links can also be made to open in a new window by appending a carat character `^` to the URL/slug, for
example `[[slug^]]` would create a link to "slug" that opens in a new window, and `[[slug^|Display text]]` would do the
same, with the display text set to "Display text".

## Advanced Markdown

Above and beyond standard Markdown text formatting, bbMark always includes the following Markdown extensions:

* [Description Lists](https://commonmark.thephpleague.com/2.5/extensions/description-lists/)
* [Footnotes](https://commonmark.thephpleague.com/2.5/extensions/footnotes/)
* [Heading Permalinks](https://commonmark.thephpleague.com/2.5/extensions/heading-permalinks/)
* [Table of Contents](https://commonmark.thephpleague.com/2.5/extensions/table-of-contents/) (place a TOC in a document
  with `[TOC]` tag)
* [Tables](https://commonmark.thephpleague.com/2.5/extensions/tables/)

### Markdown extensions disabled by default

The following extensions are available, but disabled by default:

* [Attributes](https://commonmark.thephpleague.com/2.5/extensions/attributes/) (config to enable:
  `"enable_attribuates" => true`)
* [Front Matter](https://commonmark.thephpleague.com/2.5/extensions/front-matter/) (config to enable:
  `"enable_front_matter" => true`)
* [Smart Punctuation](https://commonmark.thephpleague.com/2.5/extensions/smart-punctuation/) (config to enable:
  `"enable_smart_punctuation" => true`)

### HTML

By default, any HTML included in content will be stripped completely. To enable HTML input, set the config option
`"html_input" => "allow"`. This will enable HTML input with some caveats. You can also escape HTML input so that it
displays the code as it was entered with `"html_input" => "escape"`.

By default, bbMark matches the GFM spec and all tags are allowed except `title`, `textarea`, `style`, `xmp`, `iframe`,
`noembed`, `noframes`, `script`, and `plaintext`. If you would like to configure this differently, you can specify your
own tag blocklist via config:

```
$config = [
    'disallowed_raw_html' => [
        'disallowed_tags' => ['title', 'textarea', 'style', 'xmp', 'iframe', 'noembed', 'noframes', 'script', 'plaintext'],
    ],
];
```

## Architecture

This library functions as a set of extensions for the [League CommonMark](https://commonmark.thephpleague.com/) package.
This package enables a subset of Markdown using that library's own internal parsing and rendering tools. It then extends
it with its own extension to enable shortcodes and wiki links.

Internally, all shortcodes are parsed by either `InlineTagParser` or `BlockTagParser` into `TagNode` objects in the
CommonMark AST. They are then all rendered through `TagRenderer` which calls an appropriate `TagBuilderInterface` object
that does the actual rendering.

While it is technically possible to use the included extensions directly, there are some features that will not be
autoconfigured. Primarily this will impact wiki-style `[[link/path]]` tags and `[link="path/to/url"]`
`[embed="/path/to/content"]` bbCode-style links/embeds, as these will not be hooked into the main bbMark object which
helps tie linking and embedding into your own path/slug/url-to-content resolution systems.

### Extending

To add a tag, it should be created by extending either `TagBuilderInterface`. That object will be passed a `TagNode`
object containing the tag name, parameters from the AST, and contained content if applicable.