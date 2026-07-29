<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $invoice->invoice_no }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        @page {
            margin: 20px 20px 54px 20px;
        }

        body {
            margin: 0;
            padding: 0 0 70px 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #0f172a;
            background: #ffffff;
        }

        .page {
            border: 1px solid #d8dee9;
            background: #ffffff;
        }

        .top-bar {
            height: 12px;
            background: #111827;
            width: 100%;
        }

        .bottom-bar-fixed {
            position: fixed;
            left: 20px;
            right: 20px;
            bottom: 0;
            height: 10px;
            background: #111827;
        }

        .footer-fixed {
            position: fixed;
            left: 20px;
            right: 20px;
            bottom: 14px;
            padding-top: 8px;
            border-top: 1px solid #d8dee9;
            text-align: center;
            color: #111827;
            font-size: 11px;
            font-weight: bold;
            background: #ffffff;
        }

        .content {
            padding: 18px 20px 18px 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table {
            width: 100%;
        }

        .header-table td {
            vertical-align: top;
        }

        .header-left {
            width: 55%;
        }

        .header-right {
            width: 45%;
            text-align: right;
        }

        .header-logo {
            margin-bottom: 12px;
        }

        .header-logo img {
            max-width: 210px;
            max-height: 64px;
            width: auto;
            height: auto;
        }

        .company-fallback {
            font-size: 30px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #111827;
            margin-bottom: 12px;
        }

        .company-fallback .accent {
            color: #06b6d4;
        }

        .company-details {
            color: #4b5563;
            font-size: 12px;
            line-height: 1.75;
        }

        .company-details div {
            margin-bottom: 2px;
        }

        .company-details strong {
            color: #0f172a;
        }

        .invoice-box {
            text-align: right;
        }

        .invoice-title {
            display: inline-block;
            border-radius: 4px;
            background: #111827;
            padding: 10px 14px;
            color: #ffffff;
            font-size: 22px;
            line-height: 1;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 18px;
            margin-top: 10px;
        }

        .meta-table {
            margin-left: auto;
            width: 300px;
            border: 1px solid #e5e7eb;
            background: #f8fafc;
            padding: 8px 12px;
        }

        .meta-table td {
            padding: 4px 0;
            vertical-align: top;
            font-size: 12px;
            line-height: 1.55;
        }

        .meta-label {
            color: #6b7280;
            text-align: left;
            padding-right: 12px;
            white-space: nowrap;
            font-size: 12px;
        }

        .meta-value {
            text-align: right;
            font-weight: bold;
            color: #111827;
            white-space: nowrap;
            font-size: 12px;
        }

        .section-title {
            margin-top: 22px;
            margin-bottom: 10px;
            padding: 8px 0 7px 0;
            border-bottom: 2px solid #111827;
            color: #111827;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .info-box {
            border: 1px solid #e5e7eb;
            background: #f8fafc;
            border-radius: 4px;
        }

        .info-box td {
            vertical-align: top;
            padding: 14px 16px;
        }

        .info-line {
            margin-bottom: 6px;
            line-height: 1.45;
        }

        .info-label {
            font-weight: bold;
            color: #64748b;
        }

        .info-right {
            text-align: right;
        }

        .items-table {
            margin-top: 4px;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #e5e7eb;
            padding: 10px 10px;
            vertical-align: top;
        }

        .items-table th {
            background: #111827;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            font-size: 11px;
            letter-spacing: 0.04em;
        }

        .items-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .summary-wrap {
            margin-top: 18px;
            width: 100%;
        }

        .summary-box {
            width: 390px;
            margin-left: auto;
            border: 1px solid #d8dee9;
            background: #f8fafc;
            padding: 12px 14px;
            border-radius: 4px;
        }

        .summary-table td {
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .summary-table td:first-child {
            color: #475569;
        }

        .summary-table td:last-child {
            text-align: right;
            font-weight: bold;
            color: #0f172a;
        }

        .grand-total td {
            background: #111827;
            color: #ffffff !important;
            font-weight: bold !important;
            font-size: 13px;
            padding: 10px 12px;
            border-bottom: none;
        }

        .note-box {
            border: 1px solid #e5e7eb;
            background: #f8fafc;
            padding: 12px 14px;
            border-radius: 4px;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    @php
        $deliveryMethodLabels = [
            'cash_on_delivery' => 'Cash on Delivery',
            'paid_delivery' => 'Paid Delivery',
            'pickme_flash' => 'PickMe Flash',
            'uber_flash' => 'Uber Flash',
        ];

        $deliveryAgentLabels = [
            'domex' => 'Domex',
            'pickme' => 'PickMe',
        ];

        $deliveryPaymentStatusLabels = [
            'paid' => 'Paid',
            'non_paid' => 'Non-Paid',
        ];

        $deliveryEnabled = (bool) ($invoice->delivery_enabled ?? false);
    @endphp

    <div class="page">
        <div class="top-bar"></div>

        <div class="content">
            <table class="header-table">
                <tr>
                    <td class="header-left">
                        <div class="header-logo">
                            @if(!empty($shop['logo_base64']))
                                <img src="{{ $shop['logo_base64'] }}" alt="Logo">
                            @elseif(!empty($shop['logo_path']))
                                <img src="{{ $shop['logo_path'] }}" alt="Logo">
                            @else
                                <div class="company-fallback"><span class="accent">Deze</span>Store</div>
                            @endif
                        </div>

                        <div class="company-details">
                            @foreach(($shop['address_lines'] ?? []) as $line)
                                <div>{{ $line }}</div>
                            @endforeach

                            @if(!empty($shop['phone']))
                                <div><strong>Phone:</strong> {{ $shop['phone'] }}</div>
                            @endif

                            @if(!empty($shop['website']))
                                <div><strong>Web:</strong> {{ $shop['website'] }}</div>
                            @endif
                        </div>
                    </td>

                    <td class="header-right">
                        <div class="invoice-box">
                            <div class="invoice-title">ORDER INVOICE</div>

                            <table class="meta-table">
                                <tr>
                                    <td class="meta-label">Date:</td>
                                    <td class="meta-value">{{ optional($invoice->invoice_date)->format('Y-m-d') }}</td>
                                </tr>
                                <tr>
                                    <td class="meta-label">Invoice No:</td>
                                    <td class="meta-value">{{ $invoice->invoice_no }}</td>
                                </tr>
                                <tr>
                                    <td class="meta-label">Payment Type:</td>
                                    <td class="meta-value">{{ ucfirst(str_replace('_', ' ', $invoice->payment_type ?? 'unpaid')) }}</td>
                                </tr>
                                <tr>
                                    <td class="meta-label">Status:</td>
                                    <td class="meta-value">{{ ucfirst($invoice->status ?? 'draft') }}</td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>

            <div class="section-title">Bill To</div>

            <table class="info-box">
                <tr>
                    <td width="50%">
                        <div class="info-line"><span class="info-label">Name:</span> {{ $invoice->customer_name }}</div>
                        <div class="info-line"><span class="info-label">Contact:</span> {{ $invoice->customer_contact_number }}</div>

                        @if($invoice->customer_address)
                            <div class="info-line"><span class="info-label">Address:</span> {{ $invoice->customer_address }}</div>
                        @endif

                        @if($invoice->customer_email)
                            <div class="info-line"><span class="info-label">Email:</span> {{ $invoice->customer_email }}</div>
                        @endif
                    </td>

                    <td width="50%" class="info-right">
                        @if($deliveryEnabled)
                            @if($invoice->delivery_method)
                                <div class="info-line">
                                    <span class="info-label">Delivery Type:</span>
                                    {{ $deliveryMethodLabels[$invoice->delivery_method] ?? ucfirst(str_replace('_', ' ', $invoice->delivery_method)) }}
                                </div>
                            @endif

                            @if($invoice->delivery_agent)
                                <div class="info-line">
                                    <span class="info-label">Delivery Agent:</span>
                                    {{ $deliveryAgentLabels[$invoice->delivery_agent] ?? ucfirst(str_replace('_', ' ', $invoice->delivery_agent)) }}
                                </div>
                            @endif

                            @if($invoice->delivery_payment_status)
                                <div class="info-line">
                                    <span class="info-label">Payment Status:</span>
                                    {{ $deliveryPaymentStatusLabels[$invoice->delivery_payment_status] ?? ucfirst(str_replace('_', ' ', $invoice->delivery_payment_status)) }}
                                </div>
                            @endif

                            @if($invoice->tracking_id)
                                <div class="info-line"><span class="info-label">Tracking ID:</span> {{ $invoice->tracking_id }}</div>
                            @endif

                            <div class="info-line">
                                <span class="info-label">Delivery Amount:</span>
                                {{ number_format((float) ($invoice->delivery_amount ?? 0), 2) }}
                            </div>
                        @else
                            @if($invoice->ship_date)
                                <div class="info-line"><span class="info-label">Ship Date:</span> {{ optional($invoice->ship_date)->format('Y-m-d') }}</div>
                            @endif

                            @if($invoice->ship_via)
                                <div class="info-line"><span class="info-label">Ship Via:</span> {{ $invoice->ship_via }}</div>
                            @endif
                        @endif
                    </td>
                </tr>
            </table>

            <div class="section-title">Items</div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th width="7%">ITEM</th>
                        <th width="47%">DESCRIPTION</th>
                        <th width="8%" class="text-center">QTY</th>
                        <th width="14%" class="text-right">UNIT PRICE</th>
                        <th width="8%" class="text-center">%</th>
                        <th width="16%" class="text-right">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoice->items as $item)
                        <tr>
                            <td class="text-center">{{ $item->item_no }}</td>
                            <td>{{ $item->description }}</td>
                            <td class="text-center">{{ $item->qty }}</td>
                            <td class="text-right">{{ number_format((float) $item->regular_price, 2) }}</td>
                            <td class="text-center">
                                {{ $item->discount_type === 'percentage'
                                    ? rtrim(rtrim(number_format((float) ($item->discount_percent_display ?? $item->discount_value), 2), '0'), '.')
                                    : '-' }}
                            </td>
                            <td class="text-right">{{ number_format((float) $item->line_total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No invoice items added.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="summary-wrap">
                <div class="summary-box">
                    <table class="summary-table">
                        <tr>
                            <td>Subtotal</td>
                            <td>{{ number_format((float) $invoice->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Total Discount</td>
                            <td>{{ number_format((float) $invoice->total_discount, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Tax</td>
                            <td>{{ number_format((float) $invoice->tax_amount, 2) }}</td>
                        </tr>
                        <tr class="grand-total">
                            <td>Grand Total</td>
                            <td>{{ number_format((float) $invoice->grand_total, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Cash Paid</td>
                            <td>{{ number_format((float) $invoice->cash_paid, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Card Paid</td>
                            <td>{{ number_format((float) $invoice->card_paid, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Advance Amount</td>
                            <td>{{ number_format((float) $invoice->advance_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Delivery Amount</td>
                            <td>{{ number_format((float) ($invoice->delivery_amount ?? 0), 2) }}</td>
                        </tr>
                        <tr>
                            <td>Total Paid</td>
                            <td>{{ number_format((float) $invoice->paid_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Balance Due</td>
                            <td>{{ number_format((float) $invoice->balance_due, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($invoice->notes)
                <div class="section-title">Notes</div>
                <div class="note-box">{{ $invoice->notes }}</div>
            @endif

            @if($invoice->terms)
                <div class="section-title">Terms / Remarks</div>
                <div class="note-box">{{ $invoice->terms }}</div>
            @endif
        </div>
    </div>

    <div class="footer-fixed">
        {{ $shop['website'] ?? 'www.dezestore.com' }}
    </div>

    <div class="bottom-bar-fixed"></div>
</body>
</html>
