<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    public function test_homepage_renders_and_contains_branding(): void
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('Phuket Smart Bus', false);
    }

    public function test_blog_index_renders(): void
    {
        $this->get('/blog')->assertStatus(200);
    }

    public function test_locale_prefix_serves_thai_html_lang(): void
    {
        $this->get('/th')
            ->assertStatus(200)
            ->assertSee('<html lang="th"', false);
    }

    public function test_unknown_locale_prefix_404s(): void
    {
        // 'jp' isn't a supported locale — should fall through to 404
        $this->get('/jp')->assertStatus(404);
    }

    public function test_sitemap_returns_xml_with_all_locales(): void
    {
        $response = $this->get('/sitemap.xml');
        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml; charset=utf-8');
        $body = $response->getContent();
        // Each public path should appear once per locale (en bare + th + zh + ru)
        $this->assertStringContainsString('/blog</loc>', $body);
        $this->assertStringContainsString('/th/blog</loc>', $body);
        $this->assertStringContainsString('hreflang="zh"', $body);
    }

    public function test_security_headers_are_set(): void
    {
        $this->get('/')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}
