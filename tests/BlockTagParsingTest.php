<?php

namespace Joby\Bracketeer;

use PHPUnit\Framework\TestCase;

class BlockTagParsingTest extends TestCase
{
    protected Bracketeer $parser;

    public function testLineStartingWithLinkIsNotBlock()
    {
        $this->assertStringContainsString(
            '<p><a ',
            $this->parser->parse('[[slug]]')
        );
        $this->assertStringContainsString(
            '<p><a ',
            $this->parser->parse('[link[slug]]')
        );
    }

    public function testEmbedsStartingLines()
    {
        // embed tags with extra content around them should be parsed, but rendered inline
        $this->assertStringContainsString(
            '<p><img ',
            $this->parser->parse('[embed[test.jpg]] more stuff')
        );
        $this->assertStringContainsString(
            'more stuff <img',
            $this->parser->parse('more stuff [embed[test.jpg]]')
        );
        // embed tags with no extra content should be rendered as blocks
        $this->assertStringContainsString(
            '<figure',
            $this->parser->parse('[embed[test.jpg]]')
        );
    }

    protected function setUp(): void
    {
        $this->parser = new Bracketeer([
            'allow_unsafe_links' => true,
        ]);
    }
}