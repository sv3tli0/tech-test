<?php

namespace App\Vendor\Faker\Provider;

use Faker\Provider\Base;
use Faker\Provider\Color;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class PlaceholdCoProvider extends Base
{
    public const string BASE_URL = 'https://placehold.co';

    public const string FORMAT_JPEG = 'jpeg';

    public const string FORMAT_PNG = 'png';

    public const string FORMAT_SVG = 'svg';

    public const string FORMAT_WEBP = 'webp';

    public const array FORMATS = [
        self::FORMAT_JPEG,
        self::FORMAT_SVG,
        self::FORMAT_PNG,
        self::FORMAT_WEBP,
    ];

    /**
     * @param ...$config
     * @return string
     */
    public static function pcImageUrl(...$config): string {
        extract(self::warm($config));

        return  vsprintf(
            '%s/%s/%s%s/%s%s',
            [
                self::BASE_URL, // url
                "{$width}x{$height}", // size
                $format === 'svg' ? '' : '000000/', // overlay
                str_replace('#', '', Color::safeHexColor()), // backgroundColor
                $format, // format
                $word ? "?text={$word}" : '', // text
            ]
        );
    }

    /**
     * Generates on random one of following: image | imageUrl | null
     *
     * @param bool $url
     * @param bool $image
     * @param bool $nullable
     * @param ...$config
     * @return string|null
     */
    public static function randomOptionalImage(
        bool $url = true,
        bool $image = true,
        bool $nullable = true,
        ...$config
    ): ?string {
        $types = array_filter([
            'url' => $url,
            'image' => $image,
            'nullable' => $nullable,
        ]);

        extract(self::warm($config));

        return empty($types) ? null : match (array_rand($types)) {
            'url' => self::pcImageUrl($format, $width, $height, $word),
            'image' => self::pcImage($dir, $format, $width, $height, $word, $fullPath),
            default => null,
        };
    }

    /**
     * @param ...$config
     * @throw RuntimeException
     * @return string
     */
    public static function pcImage(...$config): string {
        extract(self::warm($config));

        try {
            $filename = Str::random(30).'.'.$format;
            $filepath = $dir.DIRECTORY_SEPARATOR.$filename;

            file_put_contents($filepath, Http::withHeader('accept', 'application/octet-stream')
                ->get(self::pcImageUrl($format, $width, $height, $word))
                ->getBody()
            );

            return $fullPath ? $filepath : $filename;
        } catch (\Exception $e) {
            throw new RuntimeException('Unable to download the placehold.co image!');
        }
    }

    private static function warm(array $dirtyConfig): array
    {
        return [
            'format' => self::validFormat($dirtyConfig['format'] ?? ''),
            'width' => intval(data_get($dirtyConfig, 'width', 640)),
            'height' => intval(data_get($dirtyConfig, 'height', 480)),
            'dir' => self::getDirPath($dirtyConfig['url'] ?? null),
            'word' => self::wordOrRandom($dirtyConfig['wordOrRandom'] ?? true),
            'fullPath' => (bool) data_get($dirtyConfig, 'fullPath', false),
        ];
    }

    private static function wordOrRandom(bool|string $wordOrRandom): ?string
    {
        return match ($wordOrRandom) {
            false => null,
            true => Str::random(mt_rand(6,15)),
            default => $wordOrRandom
        };
    }

    private static function validFormat(mixed $format): string
    {
        $format = Str::lower(is_string($format) ? $format : '');

        return in_array($format, self::FORMATS) ? $format : self::FORMATS[array_rand(self::FORMATS)];
    }

    private static function getDirPath(?string $dir): string
    {
        $dir = empty($dir) ? Storage::disk('public')->path('images') : $dir;

        if (! is_dir($dir) || ! is_writable($dir)) {
            throw new RuntimeException("Cannot write to directory {$dir}");
        }

        return $dir;
    }
}
