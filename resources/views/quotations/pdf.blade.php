<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1f2937; }
        .header { display: table; width: 100%; margin-bottom: 24px; }
        .header .company { display: table-cell; width: 60%; vertical-align: top; }
        .header .meta { display: table-cell; width: 40%; vertical-align: top; text-align: right; }
        .company-name { font-size: 18px; font-weight: bold; margin-bottom: 4px; }
        .quote-title { font-size: 20px; font-weight: bold; color: #2563eb; margin-bottom: 6px; }
        .muted { color: #6b7280; }
        .bill-to { margin-bottom: 20px; }
        .bill-to .label { font-size: 10px; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; margin-bottom: 4px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.items th { background: #f3f4f6; text-align: left; padding: 8px 10px; font-size: 10.5px; text-transform: uppercase; color: #6b7280; }
        table.items td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; }
        table.items td.num, table.items th.num { text-align: right; }
        .totals { width: 260px; margin-left: auto; }
        .totals td { padding: 4px 10px; }
        .totals tr.grand td { font-weight: bold; font-size: 14px; border-top: 2px solid #1f2937; }
        .terms { margin-top: 24px; font-size: 11px; color: #374151; white-space: pre-wrap; }
        .terms .label { font-size: 10px; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; margin-bottom: 4px; }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 10px; font-size: 10px; text-transform: uppercase; font-weight: bold; background: #eff6ff; color: #1d4ed8; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">
            @if($company?->company_logo)
                <img src="{{ public_path($company->company_logo) }}" style="max-height:50px;margin-bottom:8px;">
            @endif
            <div class="company-name">{{ $company->company_name ?? 'Your Company' }}</div>
            <div class="muted">{{ $company?->address }}{{ $company?->city ? ', '.$company->city : '' }}{{ $company?->state ? ', '.$company->state : '' }} {{ $company?->pincode }}</div>
            @if($company?->gst_number)<div class="muted">GSTIN: {{ $company->gst_number }}</div>@endif
        </div>
        <div class="meta">
            <div class="quote-title">QUOTATION</div>
            <div>{{ $quotation->quotation_number }} <span class="muted">(v{{ $quotation->version }})</span></div>
            <div class="muted">{{ $quotation->created_at->format('d M Y') }}</div>
            @if($quotation->valid_until)<div class="muted">Valid until {{ $quotation->valid_until->format('d M Y') }}</div>@endif
            <div style="margin-top:6px;"><span class="status-badge">{{ strtoupper($quotation->status) }}</span></div>
        </div>
    </div>

    <div class="bill-to">
        <div class="label">Quoted To</div>
        <div><strong>{{ $customer['name'] ?? 'N/A' }}</strong></div>
        @if($customer['company'] ?? null)<div>{{ $customer['company'] }}</div>@endif
        @if($customer['email'] ?? null)<div class="muted">{{ $customer['email'] }}</div>@endif
        @if($customer['phone'] ?? null)<div class="muted">{{ $customer['phone'] }}</div>@endif
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>#</th>
                <th>Description</th>
                <th>UOM</th>
                <th class="num">Qty</th>
                <th class="num">Unit Price</th>
                <th class="num">Discount</th>
                <th class="num">Tax</th>
                <th class="num">Line Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->description }}</td>
                <td>{{ $item->uom }}</td>
                <td class="num">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                <td class="num">{{ number_format($item->unit_price, 2) }}</td>
                <td class="num">{{ $item->discount_percent }}%</td>
                <td class="num">{{ $item->tax_percent }}%</td>
                <td class="num">{{ $quotation->currency }} {{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Sub Total</td><td class="num">{{ $quotation->currency }} {{ number_format($quotation->sub_total, 2) }}</td></tr>
        <tr><td>Discount</td><td class="num">- {{ $quotation->currency }} {{ number_format($quotation->discount_amount, 2) }}</td></tr>
        <tr><td>Tax</td><td class="num">{{ $quotation->currency }} {{ number_format($quotation->tax_amount, 2) }}</td></tr>
        <tr class="grand"><td>Total</td><td class="num">{{ $quotation->currency }} {{ number_format($quotation->total_amount, 2) }}</td></tr>
    </table>

    @if($quotation->terms_conditions)
    <div class="terms">
        <div class="label">Terms &amp; Conditions</div>
        {{ $quotation->terms_conditions }}
    </div>
    @endif
</body>
</html>
