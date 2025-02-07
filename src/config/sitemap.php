<?php

return [

    /**
     * Directory in public for storing sitemap files.
     */
    'directory' => env('SITEMAP_DIRECTORY', ''),

    /**
     * Maximum number of URLs allowed per sitemap file.
     */
    'size' => env('SITEMAP_SIZE', 25000),

];
