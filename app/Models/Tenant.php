<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'owner_name',
        'email',
        'phone',
        'plan_tier',
        'payment_settings',
        'is_active',
    ];

    protected $casts = [
        'payment_settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function galleries()
    {
        return $this->hasMany(GalleryItem::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }
}
