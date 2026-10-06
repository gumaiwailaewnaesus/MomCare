<?php
session_start();

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "mother"
) {
    header("Location: login.php");
    exit();
}

$fullname = $_SESSION["fullname"];
?>

<!DOCTYPE html>
<html lang="th">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>หน้าหลักคุณแม่ | MomCare</title>


    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Arial", sans-serif;
        }


        body {

            min-height: 100vh;

            background: linear-gradient(
                135deg,
                #fff7fa,
                #fff0f5,
                #fdf5ff
            );

            color: #555;

        }


        /* =========================
           Navbar
        ========================= */

        .navbar {

            background: rgba(255, 255, 255, 0.95);

            padding: 18px 7%;

            display: flex;

            justify-content: space-between;

            align-items: center;

            box-shadow:
                0 3px 20px
                rgba(210, 130, 160, 0.10);

        }


        .logo {

            color: #df7895;

            font-size: 25px;

            font-weight: bold;

        }


        .logout {

            color: #df7895;

            text-decoration: none;

            padding: 9px 18px;

            border-radius: 20px;

            transition: 0.3s;

        }


        .logout:hover {

            background: #fff0f5;

        }


        /* =========================
           Container
        ========================= */

        .container {

            max-width: 1050px;

            margin: 0 auto;

            padding: 45px 25px 60px;

        }


        /* =========================
           Welcome
        ========================= */

        .welcome {

            background: linear-gradient(
                135deg,
                #ffffff,
                #fff7fa
            );

            padding: 35px;

            border-radius: 30px;

            margin-bottom: 25px;

            box-shadow:
                0 10px 30px
                rgba(220, 130, 160, 0.10);

            position: relative;

            overflow: hidden;

        }


        .welcome::after {

            content: "🌸";

            position: absolute;

            right: 35px;

            top: 25px;

            font-size: 65px;

            opacity: 0.35;

        }


        .welcome h1 {

            color: #df7895;

            font-size: 28px;

            margin-bottom: 10px;

        }


        .welcome p {

            color: #888;

            font-size: 16px;

        }


        /* =========================
           Tracking Card
        ========================= */

        .tracking {

            background: linear-gradient(
                135deg,
                #f8dce7,
                #f4e2f3
            );

            padding: 30px;

            border-radius: 30px;

            margin-bottom: 30px;

            box-shadow:
                0 10px 25px
                rgba(220, 130, 160, 0.12);

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;

        }


        .tracking-text h2 {

            color: #bd6682;

            margin-bottom: 10px;

            font-size: 22px;

        }


        .tracking-text p {

            color: #777;

            margin-bottom: 15px;

        }


        .tracking-button {

            display: inline-block;

            background: #df7895;

            color: white;

            padding: 11px 22px;

            border-radius: 25px;

            text-decoration: none;

            transition: 0.3s;

        }


        .tracking-button:hover {

            background: #c96380;

            transform: translateY(-2px);

        }


        .tracking-icon {

            font-size: 75px;

        }


        /* =========================
           Menu
        ========================= */

        .section-title {

            color: #bd6682;

            font-size: 21px;

            margin-bottom: 18px;

        }


        .cards {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 20px;

        }


        .card {

            background: rgba(
                255,
                255,
                255,
                0.95
            );

            padding: 30px;

            border-radius: 28px;

            text-align: center;

            text-decoration: none;

            box-shadow:
                0 8px 25px
                rgba(220, 130, 160, 0.08);

            transition: 0.3s;

            border: 1px solid
                rgba(255, 220, 230, 0.5);

        }


        .card:hover {

            transform: translateY(-7px);

            box-shadow:
                0 15px 30px
                rgba(220, 130, 160, 0.15);

        }


        .card-icon {

            width: 70px;

            height: 70px;

            margin: 0 auto 15px;

            border-radius: 50%;

            background: #fff0f5;

            display: flex;

            justify-content: center;

            align-items: center;

            font-size: 35px;

        }


        .card h2 {

            color: #df7895;

            font-size: 20px;

            margin-bottom: 8px;

        }


        .card p {

            color: #999;

            font-size: 14px;

            line-height: 1.6;

        }


        /* =========================
           Footer
        ========================= */

        .footer {

            text-align: center;

            margin-top: 45px;

            color: #aaa;

            font-size: 13px;

        }


        /* =========================
           Responsive
        ========================= */

        @media (max-width: 700px) {

            .tracking {

                flex-direction: column;

                text-align: center;

            }


            .cards {

                grid-template-columns:
                    1fr;

            }


            .welcome h1 {

                font-size: 23px;

            }

        }

        /* =========================
           QR Code
        ========================= */

        .qr-card {

            background: rgba(
                255,
                255,
                255,
                0.95
            );

            padding: 30px;

            border-radius: 28px;

            text-align: center;

            box-shadow:
                0 8px 25px
                rgba(220, 130, 160, 0.08);

            border: 1px solid
                rgba(255, 220, 230, 0.5);

        }


        .qr-card h2 {

            color: #df7895;

            font-size: 20px;

            margin-bottom: 8px;

        }


        .qr-card p {

            color: #999;

            font-size: 14px;

            margin-bottom: 15px;

        }


        .qr-code {

            width: 160px;

            height: 160px;

            object-fit: contain;

        }

    </style>

</head>


<body>


<!-- =========================
     Navbar
========================= -->

<nav class="navbar">

    <div class="logo">

        🌸 MomCare

    </div>


    <a
        href="logout.php"
        class="logout"
    >

        ออกจากระบบ

    </a>

</nav>



<!-- =========================
     Main
========================= -->

<div class="container">


    <!-- Welcome -->

    <div class="welcome">

        <h1>

            สวัสดีค่ะ คุณ<?php echo htmlspecialchars($fullname); ?> 🤱🏻💗

        </h1>


        <p>

            ยินดีต้อนรับกลับเข้าสู่ MomCare
            ระบบติดตามสุขภาพคุณแม่หลังคลอด 🌷

        </p>

    </div>



    <!-- Tracking -->

    <div class="tracking">


        <div class="tracking-text">

            <h2>

                🌷 การติดตามสุขภาพของคุณแม่

            </h2>


            <p>

                ถึงเวลาประเมินอาการสุขภาพหลังคลอดของคุณแม่แล้วค่ะ

            </p>


            <a
                href="assessment.php?period=now"
                class="tracking-button"
            >

                ประเมินอาการตอนนี้ 💗

            </a>

        </div>

        <div class="tracking-icon">

            🤱🏻

        </div>


    </div>



    <!-- Menu -->

    <h2 class="section-title">

        เมนูของคุณแม่ 🌸

    </h2>



    <div class="cards">


        <!-- ประเมินอาการ -->

        <a
            href="assessment_period.php"
            class="card"
        >

            <div class="card-icon">

                📝

            </div>


            <h2>

                ประเมินอาการ

            </h2>


            <p>

                ทำแบบประเมินสุขภาพหลังคลอด

            </p>

        </a>



        <!-- ประวัติการประเมิน -->

        <a
            href="assessment_history.php"
            class="card"
        >

            <div class="card-icon">

                📋

            </div>


            <h2>

                ประวัติการประเมิน

            </h2>


            <p>

                ดูผลการประเมินสุขภาพย้อนหลัง

            </p>

        </a>



        <!-- การแจ้งเตือน -->

        <a href="mother_notifications.php" class="card">
        

            <div class="card-icon">

                🔔

            </div>


            <h2>

                การแจ้งเตือน

            </h2>


            <p>

                ดูกำหนดการและการแจ้งเตือน

            </p>

        </a>

       
        <!-- ข้อมูลส่วนตัว -->

        <a
            href="mother_profile.php"
            class="card"
        >

            <div class="card-icon">

                👤

            </div>


            <h2>

                ข้อมูลส่วนตัว

            </h2>


            <p>

                ดูข้อมูลส่วนตัวของคุณแม่

            </p>

        </a>

         <!-- ติดต่อพยาบาล -->

        <div class="qr-card">

            <h2>

                📱 ติดต่อพยาบาล

            </h2>

            <p>

                สแกน QR Code เพื่อติดต่อพยาบาล

            </p>

            <img
                src="qrcode.png.jpg"
                alt="QR Code"
                class="qr-code"
            >

        </div>


    </div>



    <div class="footer">

        🌸 MomCare | ดูแลคุณแม่ในทุกช่วงเวลาหลังคลอด 💗

    </div>


</div>


</body>

</html>