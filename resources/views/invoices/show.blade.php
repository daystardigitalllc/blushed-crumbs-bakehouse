<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }} - {{ $tenant->name }}</title>
    <!-- Favicon -->
    @if(isset($tenant) && $tenant->logo_path)
        <link rel="icon" href="{{ asset($tenant->logo_path) }}">
    @else
        <link rel="icon" href="{{ asset('images/favicon.png') }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: #faf6f8;
            color: #2c2428;
            line-height: 1.5;
            padding: 20px 12px;
        }
        .invoice-container {
            max-width: 720px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.06);
            border: 1px solid #f0e4ea;
            overflow: hidden;
        }
        .invoice-header {
            background: linear-gradient(135deg, #fff7fa 0%, #ffe6f0 100%);
            padding: 36px 32px;
            border-bottom: 2px solid #fce8f0;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 20px;
        }
        .bakery-brand h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.8rem;
            color: #e67399;
            font-weight: 800;
            margin-bottom: 4px;
        }
        .bakery-brand p {
            color: #666;
            font-size: 0.9rem;
        }
        .invoice-meta {
            text-align: right;
        }
        .invoice-meta .invoice-num {
            font-family: monospace;
            font-size: 1.25rem;
            font-weight: 700;
            color: #333;
        }
        .invoice-meta .date-text {
            font-size: 0.85rem;
            color: #777;
            margin-top: 4px;
        }
        .status-badge {
            display: inline-block;
            margin-top: 8px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-unpaid { background: #fffbe6; color: #faad14; border: 1px solid #ffe58f; }
        .status-deposit_paid { background: #e6f7ff; color: #1890ff; border: 1px solid #91d5ff; }
        .status-paid_in_full { background: #f6ffed; color: #52c41a; border: 1px solid #b7eb8f; }
        .status-cancelled { background: #fff1f0; color: #f5222d; border: 1px solid #ffa39e; }

        .invoice-body {
            padding: 32px;
        }
        .client-card {
            background: #fff7fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 28px;
            border: 1px solid #fcedf3;
        }
        .client-card h3 {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            margin-bottom: 8px;
        }
        .client-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2c2428;
        }
        .client-email {
            font-size: 0.9rem;
            color: #666;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 28px;
        }
        .summary-table th {
            text-align: left;
            padding: 12px 8px;
            border-bottom: 2px solid #f0e4ea;
            font-size: 0.85rem;
            color: #888;
            text-transform: uppercase;
        }
        .summary-table td {
            padding: 16px 8px;
            border-bottom: 1px solid #f4e8ee;
            font-size: 0.95rem;
        }

        .totals-box {
            background: #fdfafc;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #f4e4eb;
            margin-bottom: 32px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 0.95rem;
        }
        .total-row.grand-total {
            border-top: 2px solid #f0d8e2;
            padding-top: 14px;
            margin-top: 6px;
            font-size: 1.25rem;
            font-weight: 800;
            color: #e67399;
        }
        .total-row.deposit-row {
            color: #1890ff;
            font-weight: 700;
        }

        .payment-section {
            background: #ffffff;
            border: 2px solid #e67399;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .payment-section h3 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.2rem;
            color: #5c1d37;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .payment-methods-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px;
            margin-top: 16px;
        }
        .payment-card {
            background: #fff7fa;
            border: 1px solid #f7d6e3;
            border-radius: 12px;
            padding: 16px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .payment-card .method-name {
            font-weight: 700;
            font-size: 0.95rem;
            color: #5c1d37;
            margin-bottom: 4px;
        }
        .payment-card .method-handle {
            font-family: monospace;
            font-size: 1rem;
            color: #333;
            background: #ffffff;
            padding: 6px 10px;
            border-radius: 6px;
            border: 1px solid #ecd0dc;
            margin-bottom: 10px;
            word-break: break-all;
        }
        .btn-copy {
            background: #e67399;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }
        .btn-copy:hover {
            background: #d65c85;
        }

        .notes-section {
            background: #fffbe6;
            border: 1px solid #ffe58f;
            border-radius: 12px;
            padding: 16px 20px;
            font-size: 0.9rem;
            color: #873800;
            margin-bottom: 24px;
        }

        .footer-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            border-top: 1px solid #f0e4ea;
        }
        .btn-print {
            background: #f0f0f0;
            color: #444;
            border: 1px solid #ddd;
            padding: 10px 18px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
        }

        /* Toast notification */
        .toast-msg {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #333;
            color: #fff;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: none;
            z-index: 99999;
        }

        @media print {
            body { background: white; padding: 0; }
            .invoice-container { box-shadow: none; border: none; }
            .footer-actions { display: none; }
        }
    </style>
</head>
<body>

<div class="invoice-container">
    <!-- HEADER -->
    <div class="invoice-header">
        <div class="bakery-brand">
            <h1>🧁 {{ $tenant->name }}</h1>
            <p>{{ $tenant->phone ?? 'Custom Cake & Bakehouse Studio' }}</p>
            <p>{{ $tenant->email }}</p>
        </div>
        <div class="invoice-meta">
            <div class="invoice-num">{{ $invoice->invoice_number }}</div>
            <div class="date-text">Issued: {{ $invoice->created_at ? $invoice->created_at->format('M d, Y') : date('M d, Y') }}</div>
            @if($invoice->due_date)
                <div class="date-text"><strong>Due Date:</strong> {{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}</div>
            @endif
            <div>
                <span class="status-badge status-{{ $invoice->status }}">
                    {{ str_replace('_', ' ', strtoupper($invoice->status)) }}
                </span>
            </div>
        </div>
    </div>

    <!-- BODY -->
    <div class="invoice-body">
        <!-- CLIENT CARD -->
        <div class="client-card">
            <h3>Billed To</h3>
            <div class="client-name">{{ $invoice->client_name }}</div>
            <div class="client-email">{{ $invoice->client_email }}</div>
        </div>

        <!-- ITEMS / BREAKDOWN -->
        @if($invoice->order && !empty($invoice->order->items))
            <table class="summary-table">
                <thead>
                    <tr>
                        <th>Item Description</th>
                        <th style="text-align:right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->order->items as $item)
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td style="text-align:right; font-weight:600;">${{ number_format($item['price'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- TOTALS BREAKDOWN -->
        <div class="totals-box">
            <div class="total-row">
                <span>Total Order Amount</span>
                <span>${{ number_format($invoice->total_amount, 2) }}</span>
            </div>

            @if($invoice->deposit_amount > 0)
                <div class="total-row deposit-row">
                    <span>50% Required Deposit</span>
                    <span>${{ number_format($invoice->deposit_amount, 2) }}</span>
                </div>
            @endif

            @php
                $paidSoFar = 0;
                if ($invoice->status == 'deposit_paid') {
                    $paidSoFar = $invoice->deposit_amount;
                } elseif ($invoice->status == 'paid_in_full') {
                    $paidSoFar = $invoice->total_amount;
                }
                $remainingBalance = max(0, $invoice->total_amount - $paidSoFar);
            @endphp

            @if($paidSoFar > 0)
                <div class="total-row" style="color:#52c41a; font-weight:700;">
                    <span>Amount Paid to Date</span>
                    <span>-${{ number_format($paidSoFar, 2) }}</span>
                </div>
            @endif

            <div class="total-row grand-total">
                <span>Balance Due</span>
                <span>${{ number_format($remainingBalance, 2) }}</span>
            </div>
        </div>

        <!-- BAKER NOTES / INSTRUCTIONS -->
        @if(!empty($invoice->notes))
            <div class="notes-section">
                <strong>📝 Baker's Payment Notes:</strong>
                <p style="margin-top:4px;">{{ $invoice->notes }}</p>
            </div>
        @endif

        <!-- BAKER PAYMENT METHODS -->
        <div class="payment-section">
            <h3>💳 How to Pay</h3>
            <p style="font-size:0.9rem; color:#666;">Please use one of the payment handles below to submit your payment. Once received, the baker will confirm your deposit/payment!</p>

            <div class="payment-methods-grid">
                @forelse($paymentSettings as $pmKey => $pm)
                    @php
                        $pmName = is_array($pm) ? ($pm['name'] ?? ucfirst($pmKey)) : ucfirst($pmKey);
                        $pmHandle = is_array($pm) ? ($pm['handle'] ?? $pm) : $pm;
                    @endphp
                    <div class="payment-card">
                        <div>
                            <div class="method-name">{{ $pmName }}</div>
                            <div class="method-handle">{{ $pmHandle }}</div>
                        </div>
                        <button class="btn-copy" onclick="copyHandle('{{ addslashes($pmHandle) }}', '{{ addslashes($pmName) }}')">📋 Copy {{ $pmName }} Handle</button>
                    </div>
                @empty
                    <!-- FALLBACK DEMO HANDLES IF BAKER HASN'T CUSTOMIZED YET -->
                    <div class="payment-card">
                        <div>
                            <div class="method-name">Venmo</div>
                            <div class="method-handle">@BlushedCrumbs</div>
                        </div>
                        <button class="btn-copy" onclick="copyHandle('@BlushedCrumbs', 'Venmo')">📋 Copy Venmo Handle</button>
                    </div>
                    <div class="payment-card">
                        <div>
                            <div class="method-name">CashApp</div>
                            <div class="method-handle">$BlushedCrumbs</div>
                        </div>
                        <button class="btn-copy" onclick="copyHandle('$BlushedCrumbs', 'CashApp')">📋 Copy CashApp Handle</button>
                    </div>
                    <div class="payment-card">
                        <div>
                            <div class="method-name">Zelle</div>
                            <div class="method-handle">payments@blushedcrumbs.com</div>
                        </div>
                        <button class="btn-copy" onclick="copyHandle('payments@blushedcrumbs.com', 'Zelle')">📋 Copy Zelle Email</button>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- FOOTER ACTIONS -->
        <div class="footer-actions">
            <button class="btn-print" onclick="window.print()">🖨️ Print Invoice</button>
            <span style="font-size:0.85rem; color:#888;">Thank you for your business! 🧁</span>
        </div>
    </div>
</div>

<div id="toast" class="toast-msg"></div>

<script>
    function copyHandle(handleText, name) {
        try {
            const textarea = document.createElement('textarea');
            textarea.value = handleText;
            textarea.style.position = 'fixed';
            textarea.style.left = '-9999px';
            textarea.style.top = '-9999px';
            document.body.appendChild(textarea);
            textarea.focus();
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);

            showToast(`${name} handle "${handleText}" copied to clipboard!`);
        } catch(e) {
            alert(`Copy ${name} handle: ` + handleText);
        }
    }

    function showToast(msg) {
        const toast = document.getElementById('toast');
        toast.innerText = msg;
        toast.style.display = 'block';
        setTimeout(() => {
            toast.style.display = 'none';
        }, 3000);
    }
</script>

</body>
</html>
