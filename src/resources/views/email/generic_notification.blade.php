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
            color: #111827;
        }
        .message-title {
            font-size: 16px;
            font-weight: 700;
            color: #1e3a8a;
            margin-bottom: 15px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 8px;
        }
        .message-body {
            font-size: 15px;
            line-height: 1.6;
            color: #4b5563;
            margin-bottom: 25px;
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
            <div class="message-title">{{ $title }}</div>
            <div class="message-body">{!! $body !!}</div>
        </div>
        <div class="footer">
            <p style="margin: 0 0 5px 0;">Best regards,</p>
            <p style="margin: 0 0 10px 0; font-weight: bold; color: #4b5563;">{{ env('APP_NAME') }} Team</p>
            <p style="margin: 0;"><a href="{{ env('APP_URL') }}">{{ env('APP_URL') }}</a></p>
        </div>
    </div>
</body>
</html>
