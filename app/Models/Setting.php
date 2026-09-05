<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, ?string $default = null): ?string
    {
        $fullKey = 'setting.'.$key;
        if (Cache::has($fullKey)) {
            return Cache::get($fullKey) ?? $default;
        }
        $row = static::query()->where('key', $key)->first();
        $v = $row?->value;
        Cache::forever($fullKey, $v);

        return $v ?? $default;
    }

    public static function setValue(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forever('setting.'.$key, $value);
    }
}
