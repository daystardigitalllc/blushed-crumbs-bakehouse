<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'order_number',
        'client_name',
        'client_email',
        'client_phone',
        'due_date',
        'time_slot',
        'fulfillment_type',
        'delivery_address',
        'items',
        'flavors',
        'frosting',
        'fillings',
        'special_notes',
        'allergies',
        'social_follows',
        'inspiration_files',
        'subtotal',
        'discount_amount',
        'total_price',
        'deposit_amount',
        'deposit_paid',
        'status',
    ];

    protected $casts = [
        'items' => 'array',
        'flavors' => 'array',
        'frosting' => 'array',
        'fillings' => 'array',
        'social_follows' => 'array',
        'inspiration_files' => 'array',
        'due_date' => 'date',
        'deposit_paid' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
