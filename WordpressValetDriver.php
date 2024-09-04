<?php

namespace Valet\Drivers\Custom;

use Valet\Drivers\ValetDriver;

class WordpressValetDriver extends ValetDriver
{
    private const SITES = ['houtje-touwtje'];

    private const FILE_CONTENT_TYPE_HEADERS = [
        'css' => 'text/css',
        'js' => 'text/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'pdf' => 'application/pdf',
        'zip' => 'application/zip',
        'doc' => 'application/msword',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'gif' => 'image/gif',
        'png' => 'image/png',
        'jpeg' => 'image/jpg',
        'jpg' => 'image/jpg',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'ogv' => 'video/ogg',
        'flv' => 'video/x-flv',
        'avi' => 'video/x-msvideo',
        'wmv' => 'video/x-ms-wmv',
        'webp' => 'image/webp',
        'woff' => 'application/font-woff',
        'woff2' => 'application/font-woff2',
        'ttf' => 'application/font-ttf',
        'otf' => 'application/font-otf',
        'eot' => 'application/vnd.ms-fontobject',
        'sfnt' => 'application/font-sfnt',
        'webmanifest' => 'application/manifest+json',
        'appcache' => 'text/cache-manifest',
        'html' => 'text/html',
        'htm' => 'text/html',
        'txt' => 'text/plain',
        'md' => 'text/markdown',
        'markdown' => 'text/markdown',
        'csv' => 'text/csv',
        'tsv' => 'text/tab-separated-values',
        'ics' => 'text/calendar',
        'vcf' => 'text/vcard',
        'yaml' => 'text/yaml',
        'yml' => 'text/yaml',
        'jsonld' => 'application/ld+json',
        'rdf' => 'application/rdf+xml',
        'rss' => 'application/rss+xml',
        'atom' => 'application/atom+xml',
        'opml' => 'text/x-opml',
        'sgml' => 'text/sgml',
        'xhtml' => 'application/xhtml+xml',
        'webapp' => 'application/x-web-app-manifest+json',
    ];

    private const URI_REWRITE_RULES = [
        '/^\/static\/lib\/js\/embed\.min\.js$/' => '/wp-includes/js/wp-embed.min.js',
        '/^\/static\/lib\/(.*)$/' => '/wp-includes/$1',
        '/^\/file\/(.*)$/' => '/wp-content/uploads/$1',
        '/^\/static\/ext\/(.*)$/' => '/wp-content/plugins/$1',
        '/^\/static\/(.*)$/' => '/wp-content/themes/flatsome-child/$1',
        '/^\/flatsome\/(.*)$/' => '/wp-content/themes/flatsome/$1',
        '/^\/ajax$/' => '/wp-admin/admin-ajax.php',
    ];

    /**
     * Determine if the driver serves the request.
     */
    public function serves(string $sitePath, string $siteName, string $uri): bool
    {
        return in_array($siteName, self::SITES, true);
    }

    /**
     * Determine if the incoming request is for a static file.
     */
    public function frontControllerPath(string $sitePath, string $siteName, string $uri): ?string
    {
        if ($uri === '/') {
            return $sitePath . '/index.php';
        }

        if (substr($uri, -1) === '/') {
            $uri .= 'index.php';
        }

        return $sitePath . $uri;
    }

    /**
     * Get the fully resolved path to the application's front controller.
     */
    public function isStaticFile(string $sitePath, string $siteName, string $uri): ?string
    {
        foreach (self::URI_REWRITE_RULES as $pattern => $replacement) {
            if (preg_match($pattern, $uri)) {
                $uri = preg_replace($pattern, $replacement, $uri);
                $fileExtension = pathinfo($uri, PATHINFO_EXTENSION);

                if (!$fileExtension) {
                    return $sitePath . $uri;
                }

                if (isset(self::FILE_CONTENT_TYPE_HEADERS[$fileExtension])) {
                    header(self::FILE_CONTENT_TYPE_HEADERS[$fileExtension]);
                }

                return $sitePath . $uri;
            }
        }

        return null;
    }
}
