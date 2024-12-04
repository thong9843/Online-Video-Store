<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Online Store</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
        }

        main {
            flex: 1;
        }

        footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #333;
            color: #fff;
        }

        .footer-left, .footer-right {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .footer-logo {
            width: 50px;
            height: auto;
            margin-bottom: 10px;
        }

        .footer-right p {
            margin: 0;
        }
    </style>
</head>
<body>

    <footer>
        <div class="footer-left">
            <img src="../pngwing.com.png" alt="Logo" class="footer-logo">
            <p>&copy; 2024 Video Online Store. All rights reserved.</p>
        </div>
        <div class="footer-right">
            <p>Nguyen Chi Tam</p>
            <p>Nguyen Van Thanh</p>
            <p>Le Duy Thong</p>
        </div>
    </footer>
</body>
</html>
