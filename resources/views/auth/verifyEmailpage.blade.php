<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تحقق من بريدك الإلكتروني</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            text-align: center;
            padding: 50px;
        }

        .container {
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: auto;
            padding: 20px;
        }

        h1 {
            color: #4CAF50;
        }

        p {
            font-size: 16px;
            margin-bottom: 20px;
        }

        .otp-input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btn {
            background: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #45a049;
        }

        .error {
            color: red;
            margin: 10px 0;
        }

        .success {
            color: green;
            margin: 10px 0;
        }
    </style>
</head>
@if (session('error'))
    <div class="error">{{ session('error') }}</div>
@endif


<body>
    <div class="container">
        <h1>تحقق من بريدك الإلكتروني</h1>



        <p>يرجى إدخال رمز OTP الذي تم إرساله إلى بريدك الإلكتروني.</p>

        <form action="{{ url('verify-email') }}" method="POST">
            @csrf
            <input type="text" name="otp" class="otp-input" placeholder="رمز OTP" required>
            <button type="submit" class="btn">تحقق</button>
        </form>
    </div>
</body>

</html>
