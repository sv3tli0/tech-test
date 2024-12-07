<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OptionalImage implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (empty($value)) {
            return null;
        }

        return match (true) {
            str_starts_with($value ?: '', 'http') => $value,
            $this->storage()->exists($value) => $this->storage()->url($value),
            default => null
        };
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $str = Str::of(is_string($value) ? $value : '');

        return match (true) {
            $str->startsWith('http') => $value,
            $str->startsWith($this->storage()->path('')) => $str->replace($this->storage()->path(''), ''),
            default => null
        };
    }

    private function storage(): Filesystem
    {
        return Storage::disk('public');
    }
}
