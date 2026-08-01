<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Invoice extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'order_id',
        'invoice_number',
        'client_name',
        'client_email',
        'subtotal',
        'total_amount',
        'fee_amount',
        'fee_label',
        'discount_amount',
        'discount_label',
        'misc_amount',
        'misc_label',
        'deposit_amount',
        'status',
        'payment_method_used',
        'due_date',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'misc_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            $allowedStatuses = ['unpaid', 'deposit_paid', 'paid_in_full', 'cancelled'];
            if (empty($invoice->status) || !in_array($invoice->status, $allowedStatuses)) {
                $invoice->status = 'unpaid';
            }
        });

        static::updating(function ($invoice) {
            $allowedStatuses = ['unpaid', 'deposit_paid', 'paid_in_full', 'cancelled'];
            if (empty($invoice->status) || !in_array($invoice->status, $allowedStatuses)) {
                $invoice->status = 'unpaid';
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
