<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BellSetting extends Model
{
    protected $table = 'bell_settings';

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get setting value by key
     */
    public static function getValue($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set setting value by key
     */
    public static function setValue($key, $value)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}



