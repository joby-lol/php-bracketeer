# Bracketeer

Bracketeer is a web-authoring markup language designed to combine the best features of Markdown and wiki-inspired tags
that use double square brackets, and are designed to be as easy as possible to extend and integrate into your own
projects. There is also a unified system for resolving links and embedded content that is designed to be easily
integrated into your own CMS.

## Development status

This library is still very much under **active development** and should not be used in production.

## Installation

Install with `composer require joby/bracketeer`

Note that this language is **very much** designed to be integrated into a larger CMS of some kind. Its primary
benefit is integrating with your own URL/slug resolution system, for linking to CMS content page. For example, if you
are building your own CMS, it could integrate in a way that wiki links, Markdown links, and bbcode links _all_ share a
common content ID resolution system, and all behave consistently.

## Basic usage

```php
$b = new Joby\Bracketeer\Bracketeer;
$rendered = $b->parse("# Bracketeer Markdown document");
// the rendered document can be echoed directly as a string
echo $rendered;
// front matter may also be included, which can be retrieved via
var_dump($rendered->getFrontMatter());
```

## Basic text formatting

For basic text formatting, you can use Markdown, exactly like you're probably used to from ... kind of everywhere else
on the internet.

## Wiki-style links

One straightforward and succinct way to make links in Bracketeer is using wiki-style links, like `[[link]]`. In
Bracketeer, the link is by default treated as URL. It is also possible to plug in your own slug-to-URL resolver so that
your own CMS can take a given slug and convert it into a URL and link title.

Wiki-style links can also include an optional alternative display text, which will override any default, like
`[[link_slug|Alternative display text]]`

Wiki-style links can also be made to open in a new window by appending a carat character `^` to the URL/slug, for
example `[[slug^]]` would create a link to "slug" that opens in a new window, and `[[slug^|Display text]]` would do the
same, with the display text set to "Display text".

## Bracketeer tags

Bracketeer tags are also available, and can be easily added to the parser to cover your individual use cases. Their
syntax is similar to wiki-style links, but they have a string between the first two brackets to indicate their tag name.
For example, the built-in "link" Bracketeer tag might look something like `[link[url_goes_here|Link display text]]`.
There is also a built-in block-level "embed" bracketeer tag that can be easily plugged into your own embeddable content
locating system.

There are also built-in block-level Bracketeer embed handlers planned for embedding media from various online sources,
such as YouTube and Vimeo.

## Advanced Markdown

Above and beyond standard Markdown text formatting, Bracketeer always includes the following Markdown extensions:

* [Description Lists](https://commonmark.thephpleague.com/2.5/extensions/description-lists/)
* [Footnotes](https://commonmark.thephpleague.com/2.5/extensions/footnotes/)
* [Heading Permalinks](https://commonmark.thephpleague.com/2.5/extensions/heading-permalinks/)
* [Table of Contents](https://commonmark.thephpleague.com/2.5/extensions/table-of-contents/) (place a TOC in a document
  with `[TOC]` tag, note that while this looks sort of like a Bracketeer tag, it is not)
* [Tables](https://commonmark.thephpleague.com/2.5/extensions/tables/)

## Why not bbCode tags?

bbCode-style tags are very popular and widely used, but they were not used in the project because they are complex to
parse and can be ambiguous. The syntax of Bracketeer tags is designed to be as simple as possible and to be easy to
parse using only regular expressions. This is also a strategy for long-term maintainability of both this project and
projects that maintain it, because it will be both easier to maintain this project and to replace it if necessary in
the event that it stops being maintained.

### Additional available Markdown extensions

The following extensions are available but disabled by default:

* [Attributes](https://commonmark.thephpleague.com/2.5/extensions/attributes/) (config to enable:
  `"enable_attributes" => true`)
* [Front Matter](https://commonmark.thephpleague.com/2.5/extensions/front-matter/) (config to enable:
  `"enable_front_matter" => true`)
* [Smart Punctuation](https://commonmark.thephpleague.com/2.5/extensions/smart-punctuation/) (config to enable:
  `"enable_smart_punctuation" => true`)

### HTML

By default, any HTML included in content will be stripped completely. To enable HTML input, set the config option
`"html_input" => "allow"`. This will enable HTML input -- with some caveats. You can also escape HTML input so that it
displays the code as it was entered with `"html_input" => "escape"`.

By default, Bracketeer matches the GFM spec and when `html_input` is set to `allow` all tags are allowed except `title`,
`textarea`, `style`, `xmp`, `iframe`, `noembed`, `noframes`, `script`, and `plaintext`. If you would like to configure
this differently, you can specify your own tag blocklist via config:

```
$config = [
    'disallowed_raw_html' => [
        'disallowed_tags' => ['title', 'textarea', 'style', 'xmp', 'iframe', 'noembed', 'noframes', 'script', 'plaintext'],
    ],
];
```

## Architecture

This library functions as a set of extensions for the [League CommonMark](https://commonmark.thephpleague.com/) package.
By default, it enables a subset of Markdown using that library's own internal parsing and rendering tools. It then
extends it with its own extension to enable wiki-style links and both inline and block Bracketeer tags.

While it is technically possible to use the included extensions directly, there are some features that will not be
autoconfigured. Primarily this will impact wiki-style `[[link/path]]` links, as these will not be hooked into the main
Bracketeer object which helps tie linking and embedding into your own path/slug/url-to-content resolution systems.
