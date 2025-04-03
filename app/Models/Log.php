<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Log extends Model
{
    protected $fillable = [
        'package_id',
        'event',
        'message',
    ];

    public static function write(Package $package, string $event, string $message): void
    {
        self::create([
            'package_id' => $package->id,
            'event' => $event,
            'message' => $message,
        ]);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
