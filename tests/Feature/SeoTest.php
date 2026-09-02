<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_includes_core_seo_tags(): void
    {
        $response = $this->get(route('welcome'));

        $response->assertOk();
        $response->assertSee('<html lang="es"', false);
        $response->assertSee('<meta name="description"', false);
        $response->assertSee('<meta name="robots" content="index, follow"', false);
        $response->assertSee('<link rel="canonical"', false);
        $response->assertSee('property="og:title"', false);
        $response->assertSee('property="og:image"', false);
        $response->assertSee('property="og:image:width"', false);
        $response->assertSee('name="twitter:card"', false);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('<h2', false);
        $response->assertSee('Entre Sabores — Red social de maridajes', false);
    }

    public function test_explore_page_includes_seo_tags_and_canonical(): void
    {
        $response = $this->get(route('explore'));

        $response->assertOk();
        $response->assertSee('<link rel="canonical" href="'.route('explore').'"', false);
        $response->assertSee('<h2', false);
    }

    public function test_login_page_includes_meta_description(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('<meta name="description"', false);
        $response->assertSee(config('seo.auth_descriptions.login'), false);
    }

    public function test_robots_txt_lists_sitemap(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertSee('Sitemap: '.route('seo.sitemap'), false);
    }

    public function test_sitemap_xml_lists_public_routes(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee(route('welcome'), false);
        $response->assertSee(route('explore'), false);
        $response->assertSee(route('how-it-works'), false);
        $response->assertSee(route('login'), false);
        $response->assertSee(route('register'), false);
    }

    public function test_security_headers_remove_x_powered_by(): void
    {
        $response = $this->get(route('welcome'));

        $response->assertOk();
        $this->assertFalse($response->headers->has('X-Powered-By'));
    }
}
