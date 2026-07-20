<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'order_id',
        'invoice_number',
        'client_name',
        'client_email',
        'total_amount',
        'deposit_amount',
        'status',
        'payment_method_used',
        'due_date',
        'notes',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
