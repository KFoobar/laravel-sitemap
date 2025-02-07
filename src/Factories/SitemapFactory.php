<?php

namespace KFoobar\Sitemap\Factories;

use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use SimpleXMLElement;

class SitemapFactory
{
    /**
     * @var Collection
     */
    protected Collection $urls;

    /**
     * @var Collection
     */
    protected Collection $sitemaps;

    /**
     * @var string
     */
    protected string $directory;

    /**
     * @var int
     */
    protected int $maxSize;

    /**
     * SitemapFactory constructor.
     */
    public function __construct()
    {
        $this->urls = collect();
        $this->sitemaps = collect();
        $this->directory = rtrim(config('sitemap.path', ''), '/');
        $this->maxSize = config('sitemap.size', 25000);
    }

    /**
     * Set the directory.
     *
     * @param  string  $directory
     * @return self
     */
    public function setDirectory(string $directory): self
    {
        $this->directory = rtrim($directory, '/');

        return $this;
    }

    /**
     * Set the maximum size.
     *
     * @param  int  $size
     * @return self
     */
    public function setMaxSize(int $size): self
    {
        $this->maxSize = $size;

        return $this;
    }

    /**
     * Add a URL to the sitemap.
     *
     * @param  string       $url
     * @param  Carbon|DateTime|string|null  $lastModified
     * @param  string       $group
     * @return self
     */
    public function add(string $url, $lastModified = null, string $group = 'default'): self
    {
        $this->urls->push([
            'url' => $url,
            'lastmod' => $this->formatLastModified($lastModified),
            'group' => $group ?: 'default',
        ]);

        return $this;
    }

    /**
     * Generate the sitemap or index file.
     *
     * @return bool
     */
    public function generate(): bool
    {
        $groups = $this->urls->groupBy('group');

        if ($groups->count() === 1 && $this->urls->count() <= $this->maxSize) {
            return $this->buildSitemap($groups->first());
        }

        return $this->buildIndex($groups);
    }

    /**
     * Build the sitemap XML file.
     *
     * @param  Collection  $urls
     * @param  string      $filename
     * @return bool
     */
    protected function buildSitemap(Collection $urls, string $filename = 'sitemap.xml'): bool
    {
        $xml = $this->initializeSitemapXml();

        $urls->each(function ($url) use ($xml) {
            $element = $xml->addChild('url');
            $element->addChild('loc', $url['url']);

            if ($url['lastmod']) {
                $element->addChild('lastmod', $url['lastmod']);
            }
        });

        return $xml->asXML($this->buildFilePath($filename));
    }

    /**
     * Build the sitemap index XML file.
     *
     * @param  Collection  $groups
     * @param  string      $filename
     * @return bool
     */
    protected function buildIndex(Collection $groups, string $filename = 'sitemap.xml'): bool
    {
        $this->sitemaps = collect();

        $groups->each(function ($group, string $name) {
            $group->chunk($this->maxSize)->each(function ($chunk, int $key) use ($name) {
                $chunkFilename = $this->generateChunkFilename($name, $key);

                if ($this->buildSitemap($chunk, $chunkFilename)) {
                    $this->sitemaps->push([
                        'url' => $this->buildSitemapUrl($chunkFilename),
                        'lastmod' => now()->format('Y-m-d'),
                    ]);
                }
            });
        });

        $xml = $this->initializeIndexXml();

        $this->sitemaps->each(function ($sitemap) use ($xml) {
            $element = $xml->addChild('sitemap');
            $element->addChild('loc', $sitemap['url']);
            $element->addChild('lastmod', $sitemap['lastmod']);
        });

        return $xml->asXML($this->buildFilePath($filename));
    }

    /**
     * Build the full file path for a given filename.
     *
     * @param  string  $filename
     * @return string
     */
    protected function buildFilePath(string $filename): string
    {
        return public_path($this->directory . '/' . ltrim($filename, '/'));
    }

    /**
     * Build the full URL for a given sitemap filename.
     *
     * @param  string  $filename
     * @return string
     */
    protected function buildSitemapUrl(string $filename): string
    {
        $directory = rtrim($this->directory, '/');

        return url(($directory ? $directory . '/' : '') . $filename);
    }

    /**
     * Format the last modified date.
     *
     * @param  Carbon|DateTime|string|null  $lastModified
     * @return string|null
     */
    private function formatLastModified($lastModified): ?string
    {
        if ($lastModified instanceof Carbon || $lastModified instanceof DateTime) {
            return $lastModified->format('Y-m-d');
        }

        return is_string($lastModified) && !empty($lastModified) ? $lastModified : null;
    }

    /**
     * Initialize the XML for the sitemap.
     *
     * @return SimpleXMLElement
     */
    private function initializeSitemapXml(): SimpleXMLElement
    {
        return new SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>'
        );
    }

    /**
     * Initialize the XML for the sitemap index.
     *
     * @return SimpleXMLElement
     */
    private function initializeIndexXml(): SimpleXMLElement
    {
        return new SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></sitemapindex>'
        );
    }

    /**
     * Generate a filename for a sitemap chunk.
     *
     * @param  string  $group
     * @param  int     $key
     * @return string
     */
    private function generateChunkFilename(string $group, int $key): string
    {
        return sprintf('sitemap_%s_%s.xml', Str::slug($group), $key + 1);
    }
}
