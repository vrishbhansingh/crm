<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>License Required</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', system-ui, sans-serif; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: #e2e8f0;
            padding: 20px;
        }
        .box {
            max-width: 460px;
            width: 100%;
            background: #ffffff;
            color: #1e293b;
            border-radius: 18px;
            padding: 40px 34px;
            text-align: center;
            box-shadow: 0 30px 60px rgba(0, 0, 0, .4);
        }
        .icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: #fdecec;
            color: #d64545;
            font-size: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        h1 { font-size: 22px; font-weight: 700; margin-bottom: 10px; color: #0f172a; }
        p { font-size: 14px; color: #64748b; line-height: 1.6; }
        .domain {
            margin-top: 18px;
            font-size: 13px;
            background: #f1f5f9;
            border-radius: 10px;
            padding: 10px 14px;
            color: #475569;
            word-break: break-all;
        }
    </style>
</head>

<body>
    <div class="box">
        <div class="icon">&#9888;</div>
        <h1>License Not Active</h1>
        <p>
            This application is not licensed for this domain. Please contact the
            software provider to activate your license for this installation.
        </p>
        <div class="domain">Domain: {{ request()->getHost() }}</div>
    </div>
</body>

</html>
