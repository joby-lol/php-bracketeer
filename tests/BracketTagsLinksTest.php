<?php

namespace Joby\Bracketeer;

use PHPUnit\Framework\TestCase;

class BracketTagsLinksTest extends TestCase
{
    protected Bracketeer $parser;

    public function testBasicBracketTags()
    {
        // basic internal link
        $this->assertStringContainsString(
            '<a href="slug" title="[slug]">slug</a>',
            $this->parser->parse('{{link|slug}}')
        );
        // link with spaces
        $this->assertStringContainsString(
            '<a href="my-page" title="[my-page]">my-page</a>',
            $this->parser->parse('{{link|my-page}}')
        );
    }

    public function testBracketLinksWithTitles()
    {
        $this->assertStringContainsString(
            '<a href="about-page" title="About Us [about-page]">About Us</a>',
            $this->parser->parse('{{link|about-page|About Us}}')
        );
        $this->assertStringContainsString(
            '<a href="page" title="Title &amp; Stuff [page]">Title &amp; Stuff</a>',
            $this->parser->parse('{{link|page|Title & Stuff}}')
        );
    }

    public function testBracketLinksWithNewWindows()
    {
        $this->assertStringContainsString(
            '<a href="contact" title="[contact]" target="_blank">contact</a>',
            $this->parser->parse('{{link|contact^}}')
        );
        $this->assertStringContainsString(
            '<a href="page" title="Click Me [page]" target="_blank">Click Me</a>',
            $this->parser->parse('{{link|page^|Click Me}}')
        );
    }

    public function testExternalBracketLinks()
    {
        $this->assertStringContainsString(
            '<a href="https://example.com" title="[https://example.com]">https://example.com</a>',
            $this->parser->parse('{{link|https://example.com}}')
        );
        $this->assertStringContainsString(
            '<a href="https://example.com" title="Example Site [https://example.com]" target="_blank">Example Site</a>',
            $this->parser->parse('{{link|https://example.com^|Example Site}}')
        );
    }

    public function testBracketLinkEdgeCases()
    {
        // empty link
        $this->assertStringContainsString(
            '{{}}',
            $this->parser->parse('{{}}')
        );
        // Special characters in URL
        $this->assertStringContainsString(
            '<a href="special!@#$%" title="[special!@#$%]">special!@#$%</a>',
            $this->parser->parse('{{link|special!@#$%}}')
        );
    }

    public function testBracketLinkSafety()
    {
        // Test unsafe links are allowed when configured
        // note that in this test class unsafe links are allowed for testing by default
        $this->assertStringContainsString(
            '<a href="javascript:alert(1)"',
            $this->parser->parse('{{link|javascript:alert(1)}}')
        );
        // Test unsafe links are not allowed when configured
        // we need to turn it off because by default in this test unsafe links are allowed for testing
        $unsafeParser = new Bracketeer(['allow_unsafe_links' => false]);
        $this->assertStringContainsString(
            'potentially unsafe link',
            $unsafeParser->parse('{{link|javascript:alert(1)}}')
        );
    }

    public function testBracketLinksInMarkdownStructures()
    {
        // In headers
        $output = $this->parser->parse('# About {{link|contact}}');
        $this->assertStringContainsString('<h1>', $output);
        $this->assertStringContainsString('</h1>', $output);
        $this->assertStringContainsString(
            'About <a href="contact" title="[contact]">contact</a>',
            $output
        );
        // in lists
        $this->assertStringContainsString(
            '<li><a href="contact" title="[contact]">contact</a></li>',
            $this->parser->parse('* {{link|contact}}')
        );
        $this->assertStringContainsString(
            '<li><a href="contact" title="[contact]">contact</a></li>',
            $this->parser->parse('1. {{link|contact}}')
        );
        // in emphasis with additional text inside the emphasis
        $this->assertStringContainsString(
            '<em><a href="test" title="[test]">test</a> foo</em>',
            $this->parser->parse('*{{link|test}} foo*')
        );
        // works inside emphasis
        $this->assertStringContainsString(
            '<em><a href="test" title="[test]">test</a></em>',
            $this->parser->parse('*{{link|test}}*')
        );
    }

    protected function setUp(): void
    {
        $this->parser = new Bracketeer([
            'allow_unsafe_links' => true,
        ]);
    }
}