<?php

namespace App\Http\Controllers;

use App\Mail\NewOrderNotification;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\PresaleItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PresaleController extends Controller
{
    public function show(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        if (!$tenant) {
            abort(404, 'Bakery not found.');
        }

        $presaleSettings = $tenant->normalizedPresaleSettings();
        if (empty($presaleSettings['enabled'])) {
            abort(404, 'Presale not available.');
        }

        $items = PresaleItem::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('storefront.presale', compact('tenant', 'items', 'presaleSettings'));
    }

    public function submit(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        if (!$tenant) {
            return response()->json(['success' => false, 'message' => 'Bakery not found.'], 404);
        }

        $presaleSettings = $tenant->normalizedPresaleSettings();
        if (empty($presaleSettings['enabled'])) {
            return response()->json(['success' => false, 'message' => 'Presale is not currently available.'], 404);
        }

        if (empty($tenant->normalizedPaymentMethods())) {
            return response()->json([
                'success' => false,
                'message' => 'This bakery has not set up a way to accept payment yet. Please check back soon.',
            ], 422);
        }

        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_email' => 'required|email|max:255',
            'client_phone' => 'required|string|max:50',
            'due_date' => 'required|date_format:Y-m-d',
            'fulfillment_type' => 'required|string|in:pickup,delivery',
            'delivery_address' => 'nullable|string|max:500',
            'special_notes' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.presale_item_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validated['fulfillment_type'] === 'delivery') {
            if (empty($presaleSettings['delivery_enabled'])) {
                return response()->json(['success' => false, 'message' => 'Delivery is not available for this presale.'], 422);
            }
            if (empty($validated['delivery_address'])) {
                return response()->json(['success' => false, 'message' => 'Please enter a delivery address.'], 422);
            }
        }

        // Pickup window: date must fall inside [pickup_start_date, pickup_end_date]
        // and not land on one of the tenant's recurring closed weekdays.
        $dueDate = Carbon::parse($validated['due_date'])->startOfDay();
        $windowStart = $presaleSettings['pickup_start_date'] ? Carbon::parse($presaleSettings['pickup_start_date'])->startOfDay() : null;
        $windowEnd = $presaleSettings['pickup_end_date'] ? Carbon::parse($presaleSettings['pickup_end_date'])->startOfDay() : null;

        if (($windowStart && $dueDate->lt($windowStart)) || ($windowEnd && $dueDate->gt($windowEnd))) {
            return response()->json(['success' => false, 'message' => 'Please choose a date within the presale pickup window.'], 422);
        }

        $recurringClosed = $tenant->booking_settings['recurring_closed_days'] ?? [];
        if (is_array($recurringClosed) && in_array($dueDate->dayOfWeek, $recurringClosed, true)) {
            return response()->json(['success' => false, 'message' => 'That date is not available. Please choose another date.'], 422);
        }

        // Recompute pricing server-side from the DB — never trust client price/qty.
        $itemIds = collect($validated['items'])->pluck('presale_item_id')->unique();
        $dbItems = PresaleItem::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->whereIn('id', $itemIds)
            ->get()
            ->keyBy('id');

        $lineItems = [];
        $subtotal = 0.0;

        foreach ($validated['items'] as $line) {
            $item = $dbItems->get($line['presale_item_id']);
            if (!$item) {
                return response()->json(['success' => false, 'message' => 'One of the selected items is no longer available.'], 422);
            }

            $qty = (int) $line['quantity'];
            if ($qty < $item->min_quantity) {
                return response()->json([
                    'success' => false,
                    'message' => "{$item->name} requires a minimum of {$item->min_quantity} ({$item->unit_label}).",
                ], 422);
            }

            $lineTotal = round((float) $item->price * $qty, 2);
            $subtotal += $lineTotal;

            $lineItems[] = [
                'name' => $item->name,
                'quantity' => $qty,
                'unit_label' => $item->unit_label,
                'price' => (float) $item->price,
                'line_total' => $lineTotal,
            ];
        }

        $subtotal = round($subtotal, 2);
        $taxRate = (float) ($presaleSettings['tax_rate'] ?? 0);
        $taxAmount = round($subtotal * ($taxRate / 100), 2);
        $deliveryFee = $validated['fulfillment_type'] === 'delivery' ? round((float) ($presaleSettings['delivery_fee'] ?? 0), 2) : 0.0;
        $total = round($subtotal + $taxAmount + $deliveryFee, 2);

        $clientName = strip_tags(trim($validated['client_name']));
        $clientEmail = filter_var(trim($validated['client_email']), FILTER_SANITIZE_EMAIL);
        $clientPhone = strip_tags(trim($validated['client_phone']));
        $deliveryAddress = !empty($validated['delivery_address']) ? strip_tags(trim($validated['delivery_address'])) : null;
        $specialNotes = !empty($validated['special_notes']) ? strip_tags(trim($validated['special_notes'])) : null;

        $customer = Customer::findOrCreateFromOrder($tenant->id, $clientName, $clientEmail, $clientPhone);

        $orderNumber = strtoupper(substr($tenant->slug, 0, 2)) . '-' . rand(1000, 9999);

        $order = Order::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'order_number' => $orderNumber,
            'client_name' => $clientName,
            'client_email' => $clientEmail,
            'client_phone' => $clientPhone,
            'due_date' => $dueDate->format('Y-m-d'),
            'fulfillment_type' => $validated['fulfillment_type'],
            'delivery_address' => $deliveryAddress,
            'special_notes' => $specialNotes,
            'items' => $lineItems,
            'total_price' => $total,
            'deposit_amount' => 0,
            'status' => 'new',
            'source' => 'presale',
        ]);

        $customer->recordOrder($total);

        $invoiceNumber = 'INV-' . strtoupper(Str::random(6));
        $invoice = Invoice::create([
            'tenant_id' => $tenant->id,
            'order_id' => $order->id,
            'invoice_number' => $invoiceNumber,
            'client_name' => $clientName,
            'client_email' => $clientEmail,
            'subtotal' => $subtotal,
            'fee_amount' => $deliveryFee,
            'fee_label' => $deliveryFee > 0 ? 'Delivery Fee' : null,
            'misc_amount' => $taxAmount,
            'misc_label' => $taxAmount > 0 ? 'Sales Tax (' . rtrim(rtrim(number_format($taxRate, 2), '0'), '.') . '%)' : null,
            'total_amount' => $total,
            'deposit_amount' => 0,
            'status' => 'unpaid',
        ]);

        $order->update(['status' => 'invoiced']);

        try {
            $routingEmail = $tenant->email ?? config('mail.from.address', 'orders@doughmain.pro');
            Mail::to($routingEmail)->send(new NewOrderNotification($order, $tenant));
        } catch (\Exception $e) {
            Log::error('Presale baker notification error: ' . $e->getMessage());
        }

        try {
            $paymentSettings = $tenant->normalizedPaymentMethods();
            Mail::send('emails.invoice', [
                'invoice' => $invoice,
                'tenant' => $tenant,
                'paymentSettings' => $paymentSettings,
            ], function ($message) use ($invoice, $tenant) {
                $message->to($invoice->client_email)
                    ->subject('Invoice ' . $invoice->invoice_number . ' from ' . $tenant->name)
                    ->from(config('mail.from.address'), $tenant->name);
            });
        } catch (\Exception $e) {
            Log::error('Presale invoice email error: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Presale order submitted!',
            'order_number' => $order->order_number,
            'invoice_number' => $invoice->invoice_number,
            'redirect' => route('invoices.show', $invoice->invoice_number),
        ]);
    }
}
