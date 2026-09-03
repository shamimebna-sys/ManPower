<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Access Restricted</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f4f6f9;
            font-family: Arial, sans-serif;
        }

        .container {
            background: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }

        .icon {
            font-size: 70px;
            margin-bottom: 20px;
        }

        h1 {
            color: #e74c3c;
            margin-bottom: 15px;
            font-size: 32px;
        }

        p {
            color: #555;
            font-size: 16px;
            line-height: 1.6;
        }

        .footer {
            margin-top: 25px;
            font-size: 14px;
            color: #999;
        }

        .btn {
            display: inline-block;
            margin-top: 25px;
            padding: 12px 24px;
            background: #3498db;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            transition: 0.3s;
        }

        .btn:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="icon">🔒</div>

        <h1>Access Restricted</h1>

        <p>
            You are not authorized to access this page right now.
            Please contact the administrator for more information.
        </p>

        <a href="/my/logout" class="btn">Go Back Home</a>

        <div class="footer">
            &copy; <?php echo date('Y'); ?> | eujobbd.com
        </div>
    </div>

</body>
</html>