<?php

namespace Valet\Drivers\Custom;

use Valet\Drivers\ValetDriver;

class WordpressValetDriver extends ValetDriver
{
    private const SITES = ['houtje-touwtje'];
    private const FILE_CONTENT_TYPE_HEADERS = [
        'css' => 'Content-Type: text/css',
        'js' => 'Content-Type: text/javascript',
        'json' => 'Content-Type: application/json',
        'xml' => 'Content-Type: application/xml',
        'pdf' => 'Content-Type: application/pdf',
        'zip' => 'Content-Type: application/zip',
        'doc' => 'Content-Type: application/msword',
        'xls' => 'Content-Type: application/vnd.ms-excel',
        'ppt' => 'Content-Type: application/vnd.ms-powerpoint',
        'gif' => 'Content-Type: image/gif',
        'png' => 'Content-Type: image/png',
        'jpeg' => 'Content-Type: image/jpg',
        'jpg' => 'Content-Type: image/jpg',
        'svg' => 'Content-Type: image/svg+xml',
        'ico' => 'Content-Type: image/x-icon',
        'mp3' => 'Content-Type: audio/mpeg',
        'wav' => 'Content-Type: audio/wav',
        'mp4' => 'Content-Type: video/mp4',
        'webm' => 'Content-Type: video/webm',
        'ogv' => 'Content-Type: video/ogg',
        'flv' => 'Content-Type: video/x-flv',
        'avi' => 'Content-Type: video/x-msvideo',
        'wmv' => 'Content-Type: video/x-ms-wmv',
        'webp' => 'Content-Type: image/webp',
        'woff' => 'Content-Type: application/font-woff',
        'woff2' => 'Content-Type: application/font-woff2',
        'ttf' => 'Content-Type: application/font-ttf',
        'otf' => 'Content-Type: application/font-otf',
        'eot' => 'Content-Type: application/vnd.ms-fontobject',
        'sfnt' => 'Content-Type: application/font-sfnt',
        'svgz' => 'Content-Type: image/svg+xml',
        'webmanifest' => 'Content-Type: application/manifest+json',
        'appcache' => 'Content-Type: text/cache-manifest',
        'manifest' => 'Content-Type: text/cache-manifest',
        'html' => 'Content-Type: text/html',
        'htm' => 'Content-Type: text/html',
        'txt' => 'Content-Type: text/plain',
        'md' => 'Content-Type: text/markdown',
        'markdown' => 'Content-Type: text/markdown',
        'csv' => 'Content-Type: text/csv',
        'tsv' => 'Content-Type: text/tab-separated-values',
        'ics' => 'Content-Type: text/calendar',
        'vcf' => 'Content-Type: text/vcard',
        'yaml' => 'Content-Type: text/yaml',
        'yml' => 'Content-Type: text/yaml',
        'jsonld' => 'Content-Type: application/ld+json',
        'rdf' => 'Content-Type: application/rdf+xml',
        'rss' => 'Content-Type: application/rss+xml',
        'atom' => 'Content-Type: application/atom+xml',
        'opml' => 'Content-Type: text/x-opml',
        'sgml' => 'Content-Type: text/sgml',
        'xhtml' => 'Content-Type: application/xhtml+xml',
        'xht' => 'Content-Type: application/xhtml+xml',
        'webapp' => 'Content-Type: application/x-web-app-manifest+json',
        'webm' => 'Content-Type: video/webm'
    ];

    /**
     * Determine if the driver serves the request.
     */
    public function serves(string $sitePath, string $siteName, string $uri): bool
    {
        return in_array($siteName, self::SITES);
    }

    /**
     * Determine if the incoming request is for a static file.
     */
    public function isStaticFile(string $sitePath, string $siteName, string $uri)
    {
        // if (file_exists($staticFilePath = $sitePath . '/public/' . $uri)) {
        //     return $staticFilePath;
        // }
        return false;
    }

    /**
     * Get the fully resolved path to the application's front controller.
     */
    public function frontControllerPath(string $sitePath, string $siteName, string $uri): string
    {
        $indexPath = $sitePath . '/index.php';

        // &iAwZS_rogier_log_in=2021
        $rules = [
            [
                'pattern' => '/^\/index\.php$/',
                'replacement' => null, // No action, matches the rule and stops further processing
            ],
            [
                'pattern' => '/^\/static\/lib\/js\/embed\.min\.js$/',
                'replacement' => '/wp-includes/js/wp-embed.min.js',
            ],
            [
                'pattern' => '/^\/static\/lib\/(.*)$/',
                'replacement' => '/wp-includes/$1',
            ],
            [
                'pattern' => '/^\/file\/(.*)$/',
                'replacement' => '/wp-content/uploads/$1',
            ],
            [
                'pattern' => '/^\/static\/ext\/(.*)$/',
                'replacement' => '/wp-content/plugins/$1',
            ],
            [
                'pattern' => '/^\/static\/(.*)$/',
                'replacement' => '/wp-content/themes/flatsome-child/$1',
            ],
            [
                'pattern' => '/^\/static_main\/style\.css$/',
                'replacement' => '/index.php?parent_wrapper=1',
            ],
            [
                'pattern' => '/^\/flatsome\/(.*)$/',
                'replacement' => '/wp-content/themes/flatsome/$1',
            ],
            [
                'pattern' => '/^\/static_main\/(.*)$/',
                'replacement' => '/wp-content/themes/flatsome/$1',
            ],
            [
                'pattern' => '/^\/ajax$/',
                'replacement' => '/wp-admin/admin-ajax.php',
            ],
            [
                'pattern' => '/^\/wp-content\/themes\/flatsome-child\/screenshot\.png|readme\.html|license\.txt|wp-content\/debug\.log|wp-includes\/$/',
                'replacement' => '/nothing_404_404',
            ],
            [
                'pattern' => '/^\/(((wp-content|wp-includes)\/([A-Za-z0-9\-\_\/]*))|(wp-admin\/(!network\/?)([A-Za-z0-9\-\_\/]+)))(\.txt|\/)$/',
                'replacement' => '/nothing_404_404',
            ],
        ];

        foreach ($rules as $rule) {
            if (preg_match($rule['pattern'], $uri)) {
                if ($rule['replacement'] !== null) {
                    $uri = preg_replace($rule['pattern'], $rule['replacement'], $uri);
                }

                $fileExtension = end(explode('.', $uri));

                if (!$fileExtension) {
                    return $sitePath . $uri;
                }

                if (isset(self::FILE_CONTENT_TYPE_HEADERS[$fileExtension])) {
                    header(self::FILE_CONTENT_TYPE_HEADERS[$fileExtension]);
                }

                $uri = preg_replace('/\?.*/', '', $uri);

                return $sitePath . $uri;
            }
        }

        return $indexPath;
    }
}
