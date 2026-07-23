<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'notes',
        'order_count',
        'total_spent',
    ];

    protected $casts = [
        'total_spent' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Find or create a customer record from an incoming order.
     */
    public static function findOrCreateFromOrder(int $tenantId, string $name, ?string $email, ?string $phone): self
    {
        // Try to match by email first (most reliable), then by name+phone
        $customer = null;

        if ($email) {
            $customer = static::where('tenant_id', $tenantId)
                ->where('email', $email)
                ->first();
        }

        if (!$customer && $phone) {
            $customer = static::where('tenant_id', $tenantId)
                ->where('phone', $phone)
                ->where('name', $name)
                ->first();
        }

        if (!$customer) {
            $customer = static::create([
                'tenant_id' => $tenantId,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
            ]);
        }

        return $customer;
    }

    /**
     * Increment order stats after a new order.
     */
    public function recordOrder(float $amount): void
    {
        $this->increment('order_count');
        $this->increment('total_spent', $amount);
    }
}
