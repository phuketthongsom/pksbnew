<?php

namespace Tests\Feature;

use App\Services\HtmlSanitizer;
use Tests\TestCase;

class HtmlSanitizerTest extends TestCase
{
    public function test_strips_script_tags(): void
    {
        $clean = app(HtmlSanitizer::class)
            ->clean('<p>Hi</p><script>alert(1)</script>');
        $this->assertStringNotContainsString('<script', $clean);
        $this->assertStringNotContainsString('alert', $clean);
        $this->assertStringContainsString('<p>Hi</p>', $clean);
    }

    public function test_strips_javascript_protocol_links(): void
    {
        $clean = app(HtmlSanitizer::class)
            ->clean('<a href="javascript:alert(1)">click</a>');
        $this->assertStringNotContainsString('javascript:', $clean);
    }

    public function test_strips_inline_event_handlers(): void
    {
        $clean = app(HtmlSanitizer::class)
            ->clean('<img src=x onerror="alert(1)">');
        $this->assertStringNotContainsString('onerror', $clean);
    }

    public function test_keeps_safe_formatting(): void
    {
        $input = '<h2>Title</h2><p><strong>Bold</strong> <em>italic</em></p><ul><li>One</li></ul>';
        $clean = app(HtmlSanitizer::class)->clean($input);
        $this->assertStringContainsString('<h2>Title</h2>', $clean);
        $this->assertStringContainsString('<strong>', $clean);
        $this->assertStringContainsString('<li>One</li>', $clean);
    }
}
