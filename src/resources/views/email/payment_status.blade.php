<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Inter', Helvetica, Arial, sans-serif;
            background-color: #f4f6f9;
            color: #333333;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: none;
            -ms-text-size-adjust: none;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            margin-top: 30px;
            margin-bottom: 30px;
        }
        .header {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .content {
            padding: 30px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .intro-text {
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 25px;
            color: #555555;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 25px;
        }
        .status-approved {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-rejected {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .details-table th, .details-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
        }
        .details-table th {
            background-color: #f9fafb;
            color: #4b5563;
            font-weight: 600;
            width: 35%;
        }
        .details-table td {
            color: #1f2937;
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #9ca3af;
            line-height: 1.5;
        }
        .footer a {
            color: #3b82f6;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ env('APP_NAME') }}</h1>
        </div>
        <div class="content">
            <div class="greeting">Dear {{ $user->name }},</div>
            <div class="intro-text">
                Your payment approval request has been processed. Please find the details of the transaction below:
            </div>
            
            <div style="text-align: center;">
                @if($payment->status == 'A')
                    <span class="status-badge status-approved">Approved</span>
                @else
                    <span class="status-badge status-rejected">Rejected</span>
                @endif
            </div>

            <table class="details-table">
                <tr>
                    <th>Invoice No</th>
                    <td>{{ $payment->invoice_no ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Transaction No</th>
                    <td>{{ $payment->trx_no ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Amount</th>
                    <td>
                        {{ \App\Helpers\CommonClass::currencySymbol(true, $payment->currency_id, $payment->amount) }}
                    </td>
                </tr>
                @if($payment->exchange_rate && $payment->exchange_rate != 1)
                <tr>
                    <th>Exchange Rate</th>
                    <td>{{ $payment->exchange_rate }}</td>
                </tr>
                <tr>
                    <th>Credited Amount</th>
                    <td>
                        {{ \App\Helpers\CommonClass::currencySymbol(true, null, $payment->amount / $payment->exchange_rate) }}
                    </td>
                </tr>
                @endif
                <tr>
                    <th>Payment Date</th>
                    <td>{{ $payment->payment_date ? \App\Helpers\CommonClass::dateTimeFormat($payment->payment_date, 'date') : 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Remarks</th>
                    <td>{{ $payment->remarks ?? 'N/A' }}</td>
                </tr>
            </table>

            <div class="intro-text" style="margin-top: 20px;">
                If you have any questions or need further assistance, please feel free to reach out to us.
            </div>
        </div>
        <div class="footer">
            <p style="margin: 0 0 5px 0;">Best regards,</p>
            <p style="margin: 0 0 10px 0; font-weight: bold; color: #4b5563;">{{ env('APP_NAME') }} Team</p>
            <p style="margin: 0;"><a href="{{ env('APP_URL') }}">{{ env('APP_URL') }}</a></p>
        </div>
    </div>
</body>
</html>
