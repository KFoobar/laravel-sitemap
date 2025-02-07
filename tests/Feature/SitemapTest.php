<?php

namespace KFoobar\Sitemap\Tests\Feature;

use KFoobar\Sitemap\Factories\SitemapFactory;
use PHPUnit\Framework\TestCase;

class SitemapTest extends TestCase
{
    protected string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = public_path();

        if (!is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        if (is_dir($this->path)) {
            array_map('unlink', glob($this->path . '/*'));
            rmdir($this->path);
        }

        parent::tearDown();
    }

    public function test_it_can_generate_a_single_sitemap()
    {
        $factory = new SitemapFactory();

        $factory->add('https://example.com/page1', '2023-01-01')
            ->add('https://example.com/page2', '2023-01-02')
            ->generate();

        $this->assertFileExists($this->path . '/sitemap.xml');

        $content = file_get_contents($this->path . '/sitemap.xml');
        $this->assertStringContainsString('<loc>https://example.com/page1</loc>', $content);
        $this->assertStringContainsString('<loc>https://example.com/page2</loc>', $content);
    }

    public function test_it_can_generate_a_sitemap_index_with_multiple_sitemaps()
    {
        $factory = new SitemapFactory();

        $factory->setMaxSize(10);

        for ($i = 1; $i <= 20; $i++) {
            $factory->add("https://example.com/page{$i}", '2023-01-01', 'posts');
        }

        $factory->generate();

        $this->assertFileExists($this->path . '/sitemap.xml');
        $this->assertFileExists($this->path . '/sitemap_posts_1.xml');
        $this->assertFileExists($this->path . '/sitemap_posts_2.xml');

        $content = file_get_contents($this->path . '/sitemap.xml');
        $this->assertStringContainsString('sitemap_posts_1.xml</loc>', $content);
        $this->assertStringContainsString('sitemap_posts_2.xml</loc>', $content);
    }

    public function test_it_handles_empty_sitemap_gracefully()
    {
        $factory = new SitemapFactory();

        $factory->generate();

        $this->assertFileExists($this->path . '/sitemap.xml');

        $content = file_get_contents($this->path . '/sitemap.xml');
        $this->assertStringNotContainsString('<urlset', $content);
        $this->assertStringNotContainsString('<url>', $content);
    }
}
