<?php

namespace Joby\Bracketeer;

use PHPUnit\Framework\TestCase;

/**
 * This class verifies that links entered using Markdown syntax are handled consistently with Bracketeer tags.
 */
class MarkdownLinksTest extends TestCase
{
    protected Bracketeer $parser;

    public function testBasicMarkdownLinks()
    {
        $this->assertStringContainsString(
            '<a href="slug" title="text [slug]">text</a>',
            $this->parser->parse('[text](slug)'),
        );
    }

    protected function setUp(): void
    {
        $this->parser = new Bracketeer([
            'allow_unsafe_links' => true
        ]);
    }
}
