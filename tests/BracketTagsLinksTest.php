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

use PHPUnit\Framework\TestCase;

class BracketTagsLinksTest extends TestCase
{
    protected Bracketeer $parser;

    public function testBasicBracketTags()
    {
        // basic internal link
        $this->assertStringContainsString(
            '<a href="slug" title="[slug]">slug</a>',
            $this->parser->parse('[link[slug]]')
        );
        // link with spaces
        $this->assertStringContainsString(
            '<a href="my-page" title="[my-page]">my-page</a>',
            $this->parser->parse('[link[my-page]]')
        );
    }

    public function testBracketLinksWithTitles()
    {
        $this->assertStringContainsString(
            '<a href="about-page" title="About Us [about-page]">About Us</a>',
            $this->parser->parse('[link[about-page|About Us]]')
        );
        $this->assertStringContainsString(
            '<a href="page" title="Title &amp; Stuff [page]">Title & Stuff</a>',
            $this->parser->parse('[link[page|Title & Stuff]]')
        );
    }

    public function testBracketLinksWithNewWindows()
    {
        $this->assertStringContainsString(
            '<a href="contact" title="[contact]" target="_blank" rel="noopener noreferrer">contact</a>',
            $this->parser->parse('[link[contact^]]')
        );
        $this->assertStringContainsString(
            '<a href="page" title="Click Me [page]" target="_blank" rel="noopener noreferrer">Click Me</a>',
            $this->parser->parse('[link[page^|Click Me]]')
        );
    }

    public function testExternalBracketLinks()
    {
        $this->assertStringContainsString(
            '<a href="https://example.com" title="[https://example.com]">https://example.com</a>',
            $this->parser->parse('[link[https://example.com]]')
        );
        $this->assertStringContainsString(
            '<a href="https://example.com" title="Example Site [https://example.com]" target="_blank" rel="noopener noreferrer">Example Site</a>',
            $this->parser->parse('[link[https://example.com^|Example Site]]')
        );
    }

    public function testBracketLinkEdgeCases()
    {
        // empty link
        $this->assertStringContainsString(
            '[[]]',
            $this->parser->parse('[[]]')
        );
        // Special characters in URL
        $this->assertStringContainsString(
            '<a href="special!@#$%" title="[special!@#$%]">special!@#$%</a>',
            $this->parser->parse('[link[special!@#$%]]')
        );
    }

    public function testBracketLinkSafety()
    {
        // Test unsafe links are allowed when configured
        // note that in this test class unsafe links are allowed for testing by default
        $this->assertStringContainsString(
            '<a href="javascript:alert(1)"',
            $this->parser->parse('[link[javascript:alert(1)]]')
        );
        // Test unsafe links are not allowed when configured
        // we need to turn it off because by default in this test unsafe links are allowed for testing
        $unsafeParser = new Bracketeer(['allow_unsafe_links' => false]);
        $this->assertStringContainsString(
            'potentially unsafe link',
            $unsafeParser->parse('[link[javascript:alert(1)]]')
        );
    }

    public function testBracketLinksInMarkdownStructures()
    {
        // In headers
        $output = $this->parser->parse('# About [link[contact]]');
        $this->assertStringContainsString('<h1>', $output);
        $this->assertStringContainsString('</h1>', $output);
        $this->assertStringContainsString(
            'About <a href="contact" title="[contact]">contact</a>',
            $output
        );
        // in lists
        $this->assertStringContainsString(
            '<li><a href="contact" title="[contact]">contact</a></li>',
            $this->parser->parse('* [link[contact]]')
        );
        $this->assertStringContainsString(
            '<li><a href="contact" title="[contact]">contact</a></li>',
            $this->parser->parse('1. [link[contact]]')
        );
        // in emphasis with additional text inside the emphasis
        $this->assertStringContainsString(
            '<em><a href="test" title="[test]">test</a> foo</em>',
            $this->parser->parse('*[link[test]] foo*')
        );
        // works inside emphasis
        $this->assertStringContainsString(
            '<em><a href="test" title="[test]">test</a></em>',
            $this->parser->parse('*[link[test]]*')
        );
    }

    protected function setUp(): void
    {
        $this->parser = new Bracketeer([
            'allow_unsafe_links' => true,
        ]);
    }
}