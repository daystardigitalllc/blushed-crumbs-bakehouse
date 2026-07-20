<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GalleryItem extends Model
{
    use HasFactory;

    protected $table = 'galleries';

    protected $fillable = [
        'tenant_id',
        'title',
        'category',
        'image_url',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
