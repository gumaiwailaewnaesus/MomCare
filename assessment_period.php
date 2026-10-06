<?php

session_start();

if (!isset($_SESSION["user_id"])) {
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

<title>เลือกระยะการประเมิน | MomCare</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
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

    background: rgba(255,255,255,0.95);

    padding: 18px 7%;

    display: flex;

    justify-content: space-between;

    align-items: center;

    box-shadow:
        0 3px 20px
        rgba(210,130,160,0.10);

}


.logo {

    color: #df7895;

    font-size: 25px;

    font-weight: bold;

}


.back {

    color: #df7895;

    text-decoration: none;

    padding: 9px 18px;

    border-radius: 20px;

    transition: 0.3s;

}


.back:hover {

    background: #fff0f5;

}


/* =========================
   Container
========================= */

.container {

    max-width: 900px;

    margin: 0 auto;

    padding: 50px 25px 60px;

}


/* =========================
   Header
========================= */

.header {

    background: white;

    padding: 40px 30px;

    border-radius: 30px;

    text-align: center;

    margin-bottom: 30px;

    box-shadow:
        0 10px 30px
        rgba(220,130,160,0.10);

}


.header-icon {

    font-size: 60px;

    margin-bottom: 10px;

}


.header h1 {

    color: #df7895;

    margin-bottom: 12px;

}


.header p {

    color: #888;

    line-height: 1.7;

}


/* =========================
   Period Grid
========================= */

.period-grid {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 20px;

}


/* =========================
   Period Card
========================= */

.period-card {

    background: white;

    padding: 30px;

    border-radius: 25px;

    text-decoration: none;

    color: #555;

    box-shadow:
        0 8px 25px
        rgba(220,130,160,0.10);

    border: 2px solid transparent;

    transition: 0.3s;

}


.period-card:hover {

    transform: translateY(-5px);

    border-color: #f2b7c8;

    box-shadow:
        0 12px 30px
        rgba(220,130,160,0.18);

}


.period-icon {

    font-size: 45px;

    margin-bottom: 15px;

}


.period-card h2 {

    color: #df7895;

    margin-bottom: 10px;

}


.period-card p {

    color: #888;

    line-height: 1.6;

}


/* =========================
   Footer
========================= */

.footer {

    text-align: center;

    color: #aaa;

    font-size: 13px;

    margin-top: 40px;

}


/* =========================
   Responsive
========================= */

@media (max-width: 700px) {

    .container {

        padding: 30px 15px;

    }


    .period-grid {

        grid-template-columns: 1fr;

    }


    .header {

        padding: 30px 20px;

    }

}

</style>

</head>


<body>


<!-- Navbar -->

<div class="navbar">


    <div class="logo">

        🌸 MomCare

    </div>


    <a
        href="mother_dashboard.php"
        class="back"
    >

        ← กลับหน้าหลัก

    </a>


</div>



<!-- Main -->

<div class="container">


    <!-- Header -->

    <div class="header">


        <div class="header-icon">

            🌸

        </div>


        <h1>

            เลือกระยะการประเมิน

        </h1>


        <p>

            สวัสดีค่ะ คุณ<?php echo htmlspecialchars($fullname); ?> 🤱🏻💗

            <br>

            กรุณาเลือกช่วงเวลาหลังคลอดที่ต้องการประเมิน

        </p>


    </div>



    <!-- Period Cards -->

    <div class="period-grid">


        <!-- 7 วัน -->

        <a
            href="assessment.php?period=7days"
            class="period-card"
        >

            <div class="period-icon">

                🌷

            </div>


            <h2>

                7 วันหลังคลอด

            </h2>


            <p>

                ประเมินอาการในช่วงแรกหลังคลอด
                เพื่อติดตามสุขภาพคุณแม่อย่างใกล้ชิด

            </p>

        </a>



        <!-- 14 วัน -->

        <a
            href="assessment.php?period=14days"
            class="period-card"
        >

            <div class="period-icon">

                🌸

            </div>


            <h2>

                14 วันหลังคลอด

            </h2>


            <p>

                ติดตามอาการหลังคลอดระยะต่อมา
                และประเมินการฟื้นตัวของคุณแม่

            </p>

        </a>



        <!-- 3 เดือน -->

        <a
            href="assessment.php?period=3months"
            class="period-card"
        >

            <div class="period-icon">

                🩷

            </div>


            <h2>

                3 เดือนหลังคลอด

            </h2>


            <p>

                ประเมินสุขภาพร่างกาย
                การพักผ่อน และสภาพอารมณ์

            </p>

        </a>



        <!-- 6 เดือน -->

        <a
            href="assessment.php?period=6months"
            class="period-card"
        >

            <div class="period-icon">

                💗

            </div>


            <h2>

                6 เดือนหลังคลอด

            </h2>


            <p>

                ติดตามสุขภาพคุณแม่ในระยะยาว
                และประเมินการดูแลตนเองและบุตร

            </p>

        </a>


    </div>



    <!-- Footer -->

    <div class="footer">

        🌸 MomCare |

        ระบบติดตามสุขภาพคุณแม่หลังคลอด 💗

    </div>


</div>


</body>

</html>