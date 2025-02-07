# Laravel Sitemap Generator

A Laravel package for generating sitemaps in your application.

By default, the package generates a `sitemap.xml` file, but you can choose to group links into different sitemaps. If you organize URLs into multiple groups or exceed the limit set in the configuration, the package will automatically create multiple sitemap files along with an index sitemap to reference them.

## Installation

Install the package via Composer:

```bash
composer require kfoobar/laravel-sitemap
```

Publish the configuration file:

```bash
php artisan vendor:publish --provider="KFoobar\Sitemap\SitemapServiceProvider"
```

## Configuration

The configuration file `config/sitemap.php` allows you to set:

- **path**: The directory in the public folder where the sitemaps will be stored.
- **size**: The maximum number of URLs per sitemap file (default is 25000).

## Usage

This package provides two functions: **add()** and **generate()**.

**add()** accepts three parameters:
- **url**, URL of the page.
- **lastModified**, indicating when the page was last updated.
- **group**, allowing you to group URLs to create multiple sitemaps.

The **generate()** function creates the sitemap file based on the added URLs.

Note: The **priority** and **frequency** fields are not used because Google does not consider these values.

### Using the Facade

Add URLs and generate the sitemap using the Facade:

```php
use KFoobar\Sitemap\Facades\Sitemap;

Sitemap::add('https://example.com', now(), 'default');
Sitemap::generate();
```

### Manually Instantiating the Class

You can also instantiate the SitemapFactory class manually. This approach gives you direct control over the instance.

```php
use KFoobar\Sitemap\SitemapFactory;

(new SitemapFactory)
    ->add('https://example.com')
    ->add('https://example.com/about')
    ->generate();
```

### Example

```php
use KFoobar\Sitemap\SitemapFactory;

$sitemap = new SitemapFactory;

foreach (Post::all() as $post) {
    $url = route('posts.show', $post);
    $lastModified = $post->updated_at->toIso8601String();

    $sitemap->add($url, $lastModified);
}

$sitemap->generate();
```

## Contributing

Contributions are welcome!

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
