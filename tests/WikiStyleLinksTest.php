<?php

namespace Joby\Bracketeer;

use PHPUnit\Framework\TestCase;

class WikiStyleLinksTest extends TestCase
{
    protected Bracketeer $parser;

    public function testBasicWikiLinks()
    {
        // Basic internal link
        $this->assertStringContainsString(
            '<a href="slug" title="[slug]">slug</a>',
            $this->parser->parse('[[slug]]')
        );

        // Link with spaces
        $this->assertStringContainsString(
            '<a href="my-page" title="[my-page]">my-page</a>',
            $this->parser->parse('[[my-page]]')
        );
    }

    public function testWikiLinksWithTitles()
    {
        // Custom title
        $this->assertStringContainsString(
            '<a href="about-page" title="About Us [about-page]">About Us</a>',
            $this->parser->parse('[[about-page|About Us]]')
        );

        // Title with special characters
        $this->assertStringContainsString(
            '<a href="page" title="Title &amp; Stuff [page]">Title &amp; Stuff</a>',
            $this->parser->parse('[[page|Title & Stuff]]')
        );
    }

    public function testWikiLinksNewWindow()
    {
        // Basic new window
        $this->assertStringContainsString(
            '<a href="contact" title="[contact]" target="_blank">contact</a>',
            $this->parser->parse('[[contact^]]')
        );

        // New window with a specified title
        $this->assertStringContainsString(
            '<a href="page" title="Click Me [page]" target="_blank">Click Me</a>',
            $this->parser->parse('[[page^|Click Me]]')
        );
    }

    public function testExternalWikiLinks()
    {
        // Basic external
        $this->assertStringContainsString(
            '<a href="https://example.com" title="[https://example.com]">https://example.com</a>',
            $this->parser->parse('[[https://example.com]]')
        );

        // External with a title and new window
        $this->assertStringContainsString(
            '<a href="https://example.com" title="Example Site [https://example.com]" target="_blank">Example Site</a>',
            $this->parser->parse('[[https://example.com^|Example Site]]')
        );
    }

    public function testWikiLinkEdgeCases()
    {
        // Empty link
        $this->assertStringContainsString(
            '[[]]',
            $this->parser->parse('[[]]')
        );

        // Special characters in URL
        $this->assertStringContainsString(
            '<a href="special!@#$%" title="[special!@#$%]">special!@#$%</a>',
            $this->parser->parse('[[special!@#$%]]')
        );
    }

    public function testWikiLinkSafety()
    {
        // Test unsafe links are allowed when configured
        // note that in this test class unsafe links are allowed for testing by default
        $this->assertStringContainsString(
            '<a href="javascript:alert(1)"',
            $this->parser->parse('[[javascript:alert(1)]]')
        );
        // Test unsafe links are not allowed when configured
        // we need to turn it off because by default in this test unsafe links are allowed for testing
        $unsafeParser = new Bracketeer(['allow_unsafe_links' => false]);
        $this->assertStringContainsString(
            'potentially unsafe link',
            $unsafeParser->parse('[[javascript:alert(1)]]')
        );
    }

    public function testWikiLinksInMarkdownStructures()
    {
        // In headers
        $output = $this->parser->parse('# About [[contact]]');
        $this->assertStringContainsString('<h1>', $output);
        $this->assertStringContainsString('</h1>', $output);
        $this->assertStringContainsString(
            'About <a href="contact" title="[contact]">contact</a>',
            $output
        );

        // In lists
        $this->assertStringContainsString(
            '<li><a href="item" title="[item]">item</a>',
            $this->parser->parse('1. [[item]]')
        );
        $this->assertStringContainsString(
            '<li><a href="item" title="[item]">item</a>',
            $this->parser->parse('* [[item]]')
        );

        // In emphasis, works with additional text inside the emphasis
        $this->assertStringContainsString(
            '<em><a href="test" title="[test]">test</a> foo</em>',
            $this->parser->parse('*[[test]] foo*')
        );

        // Works inside emphasis, even though emphasis is now parsed differently
        $this->assertStringContainsString(
            '<em><a href="test" title="[test]">test</a></em>',
            $this->parser->parse('*[[test]]*')
        );
    }

    protected function setUp(): void
    {
        $this->parser = new Bracketeer([
            'allow_unsafe_links' => true
        ]);
    }
}