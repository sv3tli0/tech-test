<?php

namespace App\Vendor\Faker\Provider;

use Faker\Provider\Base;
use Faker\Provider\Color;
use Faker\Provider\Lorem;

/**
 * Replacement for retired placeholder.com
 */
class ImagesProvider extends Base
{
    /**
     * @var string
     */
    public const BASE_URL = 'https://placehold.co';
    public const FORMAT_JPEG = 'jpeg';
    public const FORMAT_PNG = 'png';
    public const FORMAT_SVG = 'svg';
    public const FORMAT_WEBP = 'webp';

    /**
     * @var array
     *
     * @deprecated Categories are no longer used as a list in the placeholder API but referenced as string instead
     */
    protected static $categories = [
        'abstract', 'animals', 'business', 'cats', 'city', 'food', 'nightlife',
        'fashion', 'people', 'nature', 'sports', 'technics', 'transport',
    ];

    /**
     * Generate the URL that will return a random image
     *
     * Set randomize to false to remove the random GET parameter at the end of the url.
     *
     * @example 'https://placehold.co/640x480/CCCCCC/png?text=well+hi+there'
     *
     * @param int         $width
     * @param int         $height
     * @param string|null $category
     * @param bool        $randomize
     * @param string|null $word
     * @param bool        $gray
     * @param string      $format
     *
     * @return string
     */
    public static function imagesUrl(
        $width = 640,
        $height = 480,
        $category = null,
        $randomize = true,
        $word = null,
        $gray = false,
        $format = self::FORMAT_WEBP
    ) {
        // Validate image format
        $imageFormats = static::getFormats();

        if (!in_array(strtolower($format), $imageFormats, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid image format "%s". Allowable formats are: %s',
                $format,
                implode(', ', $imageFormats),
            ));
        }

        $size = sprintf('%dx%d', $width, $height);

        $imageParts = [];

        if ($category !== null) {
            $imageParts[] = $category;
        }

        if ($word !== null) {
            $imageParts[] = $word;
        }

        if ($randomize === true) {
            $imageParts[] = Lorem::word();
        }

        $backgroundColor = $gray === true ? 'CCCCCC' : str_replace('#', '', Color::safeHexColor());

        return sprintf(
            '%s/%s/%s/%s%s',
            self::BASE_URL,
            $size,
            $backgroundColor,
            $format,
            count($imageParts) > 0 ? '?text=' . urlencode(implode(' ', $imageParts)) : '',
        );
    }

    /**
     * Download a remote random image to disk and return its location
     *
     * Requires curl, or allow_url_fopen to be on in php.ini.
     *
     * @example '/path/to/dir/13b73edae8443990be1aa8f1a483bc27.png'
     *
     * @return bool|string
     */
    public static function images(
        $dir = null,
        $width = 640,
        $height = 480,
        $category = null,
        $fullPath = true,
        $randomize = true,
        $word = null,
        $gray = false,
        $format = null,
    ) {
        $dir = null === $dir ? sys_get_temp_dir() : $dir; // GNU/Linux / OS X / Windows compatible

        // Validate directory path
        if (!is_dir($dir) || !is_writable($dir)) {
            throw new \InvalidArgumentException(sprintf('Cannot write to directory "%s"', $dir));
        }

        if (empty($format)) {
            $format = array_rand(self::getFormatConstants());
        }

        // Generate a random filename. Use the server address so that a file
        // generated at the same time on a different server won't have a collision.
        $name = md5(uniqid(empty($_SERVER['SERVER_ADDR']) ? '' : $_SERVER['SERVER_ADDR'], true));
        $filename = sprintf('%s.%s', $name, $format);
        $filepath = $dir . DIRECTORY_SEPARATOR . $filename;

        $url = static::imagesUrl($width, $height, $category, $randomize, $word, $gray, $format);

        // save file
        if (function_exists('curl_exec')) {
            // use cURL
            $fp = fopen($filepath, 'w');
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            $success = curl_exec($ch) && curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200;
            fclose($fp);
            curl_close($ch);

            if (!$success) {
                unlink($filepath);

                // could not contact the distant URL or HTTP error - fail silently.
                return false;
            }
        } elseif (ini_get('allow_url_fopen')) {
            // use remote fopen() via copy()
            $success = copy($url, $filepath);

            if (!$success) {
                return false;
            }
        } else {
            return new \RuntimeException('The image formatter downloads an image from a remote HTTP server. Therefore, it requires that PHP can request remote hosts, either via cURL or fopen()');
        }

        return $fullPath ? $filepath : $filename;
    }

    public static function getFormats(): array
    {
        return array_keys(static::getFormatConstants());
    }

    public static function getFormatConstants(): array
    {
        return [
            static::FORMAT_JPEG => constant('IMAGETYPE_JPEG'),
            static::FORMAT_PNG => constant('IMAGETYPE_PNG'),
            static::FORMAT_SVG => 99,
            static::FORMAT_WEBP => constant('IMAGETYPE_WEBP'),
        ];
    }
}
