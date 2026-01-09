<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionImage extends Model
{
    protected $fillable = ['inspection_id', 'image_path'];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }
}

