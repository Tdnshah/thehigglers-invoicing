<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quotation #{{ $quotation->quotation_number }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ccc; /* Border for screen visibility */
            padding: 40px;
            background: #fff;
        }
        @media print {
            body { padding: 0; background: #fff; }
            .container { border: none; padding: 0; max-width: 100%; margin: 0; }
            .no-print { display: none; }
            
            /* PDF Specific Fixes */
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            table { width: 100%; }
            .header-row { display: table; width: 100%; }
            .company-details { display: table-cell; vertical-align: top; width: 60%; }
            .quotation-title { display: table-cell; vertical-align: top; text-align: right; width: 40%; }
            
            .parties-row { display: table; width: 100%; margin-top: 20px; }
            .party-box { display: table-cell; vertical-align: top; width: 48%; }
            
            .quotation-details-grid { display: block; }
            .quotation-details-grid div { margin-bottom: 5px; }
        }
        .header-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
            align-items: center;
        }
        .company-details h1 { margin: 0 0 5px; font-size: 24px; text-transform: uppercase; color: #444; }
        .company-logo { max-height: 80px; max-width: 200px; margin-bottom: 10px; }
        .quotation-title { text-align: right; }
        .quotation-title h2 { margin: 0; font-size: 20px; text-transform: uppercase; color: #666; }
        
        .parties-row { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .party-box { width: 45%; }
        .box-title { font-weight: bold; text-transform: uppercase; font-size: 11px; color: #888; margin-bottom: 5px; border-bottom: 1px solid #eee; padding-bottom: 3px; }
        
        .quotation-details-grid {
            display: grid;
            grid-template-columns: auto auto;
            gap: 5px 15px;
            margin-bottom: 20px;
        }
        .detail-label { font-weight: bold; color: #555; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #f9fafb; font-weight: bold; text-transform: uppercase; font-size: 11px; color: #555; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .totals-section { display: flex; justify-content: flex-end; }
        .totals-table { width: 40%; }
        .totals-table td { padding: 5px 10px; border-bottom: 1px solid #f0f0f0; }
        .totals-table .total-row td { border-top: 2px solid #333; border-bottom: none; font-weight: bold; font-size: 16px; padding-top: 10px; }
        
        .footer { margin-top: 50px; text-align: center; font-size: 11px; color: #999; border-top: 1px solid #eee; padding-top: 20px; }
        
        /* GST Compliance additions */
        .gst-label { font-size: 11px; color: #666; }
        .badge { display: inline-block; padding: 2px 6px; border: 1px solid #333; border-radius: 4px; font-size: 10px; font-weight: bold; text-transform: uppercase; margin-bottom: 10px; }
        td ol, td ul { margin: 0; padding-left: 1.4em; }
        td ul { list-style-type: disc; }
        td ol { list-style-type: decimal; }
        td ol li, td ul li { margin-bottom: 2px; }
        td strong { font-weight: bold; }
        td em { font-style: italic; }
    </style>
</head>
<body>

    <div class="no-print" style="text-align: center; margin-bottom: 20px; padding: 10px; background: #f3f4f6; border-bottom: 1px solid #d1d5db;">
        <button onclick="window.print()" style="background: #374151; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 14px;">Print</button>
        <a href="{{ route('quotations.download', $quotation) }}" style="background: #4b5563; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 14px; text-decoration: none; margin-left: 10px;">Download PDF</a>
        <a href="{{ url()->previous() }}" style="margin-left: 10px; color: #374151; text-decoration: none;">Back</a>
    </div>

    <div class="container">
        
        <!-- Header -->
        <div class="header-row">
            <div class="company-details">
                @if($quotation->user->company && $quotation->user->company->logo_path)
                    <?php 
                        $path = storage_path('app/public/' . $quotation->user->company->logo_path);
                        $type = pathinfo($path, PATHINFO_EXTENSION);
                        if(file_exists($path)) {
                            $data = file_get_contents($path);
                            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                            echo '<img src="' . $base64 . '" alt="Company Logo" class="company-logo">';
                        }
                    ?>
                @endif
                <h1>{{ $quotation->user->company->name ?? 'Company Name' }}</h1>
                @if($quotation->user->company)
                    <div>{{ $quotation->user->company->address }}</div>
                    <div>Email: {{ $quotation->user->email }}</div>
                    <div class="gst-label">GSTIN: <strong>{{ $quotation->user->company->gst_number }}</strong></div>
                @endif
            </div>
            <div class="quotation-title">
                <h2>Tax Quotation</h2>
                <div style="margin-top: 10px; font-size: 12px;">
                    @if($quotation->quotation_type === 'export')
                        <span class="badge">Export Quotation</span>
                        @if($quotation->lut_number)
                            <br><span style="font-size: 10px;">LUT: {{ $quotation->lut_number }}</span>
                        @endif
                    @elseif($quotation->quotation_type === 'interstate')
                        <span class="badge">Inter-State Supply</span>
                    @else
                        <span class="badge">Intra-State Supply</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Parties -->
        <div class="parties-row">
            <div class="party-box">
                <div class="box-title">Billed To</div>
                <strong>{{ $quotation->client->name }}</strong><br>
                {!! nl2br(e($quotation->client->address)) !!}<br>
                <div class="gst-label" style="margin-top: 5px;">GSTIN: <strong>{{ $quotation->client->gst_number ?? 'N/A' }}</strong></div>
            </div>
            <div class="party-box" style="text-align: right;">
                <div class="box-title" style="text-align: right;">Quotation Details</div>
                <div class="quotation-details-grid" style="justify-content: end;">
                    <div class="detail-label">Quotation No:</div>
                    <div>{{ $quotation->quotation_number }}</div>
                    
                    <div class="detail-label">Date:</div>
                    <div>{{ $quotation->quotation_date->format('d M, Y') }}</div>
                    
                    @if($quotation->valid_until)
                    <div class="detail-label">Valid Until:</div>
                    <div>{{ $quotation->valid_until->format('d M, Y') }}</div>
                    @endif
                    
                    <div class="detail-label">Place of Supply:</div>
                    <div>{{ $quotation->place_of_supply ?? ($quotation->client->gst_number ? substr($quotation->client->gst_number, 0, 2) : 'N/A') }}</div>
                    
                    <div class="detail-label">Currency:</div>
                    <div>{{ $quotation->currency }}</div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 40%;">Description</th>
                    <th class="text-right" style="width: 10%;">HSN/SAC</th>
                    <th class="text-right" style="width: 10%;">Tax Rate</th>
                    <th class="text-center" style="width: 10%;">Qty</th>
                    <th class="text-right" style="width: 15%;">Rate</th>
                    <th class="text-right" style="width: 10%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quotation->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{!! $item->description !!}</td>
                    <td class="text-right">{{ $item->hsn_code ?? '-' }}</td>
                    <td class="text-right">{{ $item->tax_rate }}%</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">
                         @php
                            $taxAmount = ($item->quantity * $item->unit_price) * ($item->tax_rate / 100);
                            $lineTotal = ($item->quantity * $item->unit_price) + $taxAmount;
                         @endphp
                        {{ number_format($lineTotal, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Bottom Section: Bank Details & Totals -->
        <div style="display: flex; justify-content: space-between; margin-top: 20px;">
            
            <!-- Bank Details (Left) -->
            <div style="width: 50%; padding-right: 20px;">
                @if($quotation->user->company && $quotation->user->company->bank_account_number)
                <div style="border: 1px solid #eee; padding: 15px; border-radius: 4px; background: #f9fafb;">
                    <div style="font-weight: bold; text-transform: uppercase; font-size: 11px; color: #555; margin-bottom: 10px; border-bottom: 1px solid #ddd; padding-bottom: 5px;">
                        Bank Details
                    </div>
                    <table style="width: 100%; margin: 0; font-size: 12px;">
                        <tr>
                            <td style="padding: 2px 0; border: none; width: 100px; color: #666;">Bank Name:</td>
                            <td style="padding: 2px 0; border: none; font-weight: bold;">{{ $quotation->user->company->bank_name }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 2px 0; border: none; width: 100px; color: #666;">Account No:</td>
                            <td style="padding: 2px 0; border: none; font-weight: bold;">{{ $quotation->user->company->bank_account_number }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 2px 0; border: none; width: 100px; color: #666;">IFSC Code:</td>
                            <td style="padding: 2px 0; border: none; font-weight: bold;">{{ $quotation->user->company->bank_ifsc }}</td>
                        </tr>
                    </table>
                </div>
                @endif
            </div>

            <!-- Totals (Right) -->
            <div class="totals-section" style="width: 45%;">
                <table class="totals-table" style="width: 100%;">
                    <tr>
                        <td>Sub Total</td>
                        <td class="text-right">{{ number_format($quotation->subtotal, 2) }}</td>
                    </tr>
                    
                    @if($quotation->cgst > 0)
                    <tr>
                        <td>CGST</td>
                        <td class="text-right">{{ number_format($quotation->cgst, 2) }}</td>
                    </tr>
                    <tr>
                        <td>SGST</td>
                        <td class="text-right">{{ number_format($quotation->sgst, 2) }}</td>
                    </tr>
                    @endif
                    
                    @if($quotation->igst > 0)
                    <tr>
                        <td>IGST</td>
                        <td class="text-right">{{ number_format($quotation->igst, 2) }}</td>
                    </tr>
                    @endif
                    
                    <tr class="total-row">
                        <td>Total ({{ $quotation->currency }})</td>
                        <td class="text-right">{{ number_format($quotation->total, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-size: 10px; color: #666; font-style: italic; padding-top: 10px; text-align: right;">
                             Amount in words:<br>
                             {{ Number::spell($quotation->total) }} {{ $quotation->currency }} Only
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This is a computer generated quotation.</p>
            @if($quotation->client_notes)
                <div class="notes">
                    <strong>Notes:</strong> {{ $quotation->client_notes }}
                </div>
            @endif

            @if($quotation->terms_conditions)
                <div class="notes" style="margin-top: 15px;">
                    <strong>Terms & Conditions:</strong><br>
                    <span style="font-size: 11px;">{!! nl2br(e($quotation->terms_conditions)) !!}</span>
                </div>
            @endif
        </div>
        
    </div>
</body>
</html>
