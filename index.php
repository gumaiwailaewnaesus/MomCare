<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>MomCare</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #fff7f9;
            color: #555;
        }

        .navbar {
            background-color: #ffffff;
            padding: 20px 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .logo {
            font-size: 26px;
            font-weight: bold;
            color: #e889a2;
        }

        .nav-button {
            background-color: #e889a2;
            color: white;
            padding: 10px 22px;
            border-radius: 25px;
            text-decoration: none;
        }

        .hero {
            min-height: 80vh;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 40px 20px;
        }

        .hero-content {
            max-width: 700px;
        }

        .icon {
            font-size: 70px;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 48px;
            color: #df7895;
            margin-bottom: 20px;
        }

        .hero p {
            font-size: 20px;
            line-height: 1.8;
            color: #777;
            margin-bottom: 30px;
        }

        .start-button {
            display: inline-block;
            background-color: #e889a2;
            color: white;
            padding: 15px 40px;
            border-radius: 30px;
            text-decoration: none;
            font-size: 18px;
        }

        .start-button:hover {
            background-color: #d96f8c;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #999;
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <div class="logo">🌸 MomCare</div>

    </nav>


    <section class="hero">

        <div class="hero-content">

            <div class="icon">
                🤱🏻
            </div>

            <h1>
                ยินดีต้อนรับสู่ MomCare
            </h1>

            <p>
                ช่วยติดตามสุขภาพและอาการ พร้อมแจ้งเตือนการติดตามตามกำหนดเวลา 🌼
            </p>

            <a href="login.php" class="start-button">
                เข้าสู่ระบบ
            </a>

        </div>

    </section>


    <div class="footer">
        © 2026 MomCare | ระบบติดตามสุขภาพคุณแม่หลังคลอด
    </div>

</body>
</html>