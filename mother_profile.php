<?php

session_start();

require_once "db.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);


/* =========================
   ตรวจสอบการเข้าสู่ระบบ
========================= */

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");

    exit();

}


/* =========================
   หา mother_id จาก user_id
========================= */

$user_id = $_SESSION["user_id"];

$getMother = $conn->prepare("
    SELECT mother_id
    FROM mothers
    WHERE user_id = ?
");

$getMother->bind_param("i", $user_id);
$getMother->execute();

$mother_result = $getMother->get_result();
$mother = $mother_result->fetch_assoc();

if (!$mother) {
    die("ไม่พบข้อมูลคุณแม่ในระบบ");
}

$mother_id = $mother["mother_id"];

/* =========================
   ดึงข้อมูลบุตร
========================= */

$baby_sql = "SELECT *
             FROM babies
             WHERE mother_id = ?
             ORDER BY created_at DESC";

$baby_stmt = $conn->prepare($baby_sql);

$baby_stmt->bind_param("i", $mother_id);

$baby_stmt->execute();

$baby_result = $baby_stmt->get_result();


/* =========================
   ดึงข้อมูลคุณแม่
========================= */

$sql = "SELECT *
        FROM mothers
        WHERE mother_id = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $mother_id);

$stmt->execute();

$result = $stmt->get_result();

$mother = $result->fetch_assoc();


/* =========================
   ตรวจสอบข้อมูล
========================= */

if (!$mother) {

    die("ไม่พบข้อมูลคุณแม่");

}


?>


<!DOCTYPE html>

<html lang="th">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>ข้อมูลส่วนตัว | MomCare</title>


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

}


.back:hover {

    background: #fff0f5;

}


/* =========================
   Container
========================= */

.container {

    max-width: 850px;

    margin: auto;

    padding: 45px 25px 60px;

}


/* =========================
   Header
========================= */

.header {

    background: white;

    padding: 35px;

    border-radius: 30px;

    text-align: center;

    margin-bottom: 25px;

    box-shadow:
        0 10px 30px
        rgba(220,130,160,0.10);

}


.profile-icon {

    width: 90px;

    height: 90px;

    margin: 0 auto 15px;

    border-radius: 50%;

    background: #fff0f5;

    display: flex;

    justify-content: center;

    align-items: center;

    font-size: 50px;

}


.header h1 {

    color: #df7895;

    margin-bottom: 8px;

}


.header p {

    color: #999;

}


/* =========================
   Profile Card
========================= */

.profile-card {

    background: white;

    padding: 30px;

    border-radius: 25px;

    box-shadow:
        0 8px 25px
        rgba(220,130,160,0.08);

}


.section-title {

    color: #bd6682;

    font-size: 20px;

    margin-bottom: 20px;

}


.info {

    display: grid;

    grid-template-columns: repeat(2, 1fr);

    gap: 18px;

}


.info-box {

    background: #fff7fa;

    padding: 18px;

    border-radius: 15px;

}


.info-label {

    color: #999;

    font-size: 13px;

    margin-bottom: 7px;

}


.info-value {

    color: #555;

    font-size: 16px;

    font-weight: bold;

}


/* =========================
   Button
========================= */

.button-area {

    text-align: center;

    margin-top: 30px;

}


.button {

    display: inline-block;

    background: #df7895;

    color: white;

    padding: 13px 30px;

    border-radius: 25px;

    text-decoration: none;

    transition: 0.3s;

}


.button:hover {

    background: #c96380;

    transform: translateY(-2px);

}


/* =========================
   Responsive
========================= */

@media (max-width: 650px) {

    .info {

        grid-template-columns: 1fr;

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



<!-- Container -->

<div class="container">


    <!-- Header -->

    <div class="header">


        <div class="profile-icon">

            👩🏻

        </div>


        <h1>

            ข้อมูลส่วนตัว

        </h1>


        <p>

            ข้อมูลของคุณแม่ในระบบ MomCare 💗

        </p>


    </div>



    <!-- Profile คุณแม่ -->

<div class="profile-card">

    <h2 class="section-title">

        👤 ข้อมูลคุณแม่

    </h2>


    <div class="info">


        <!-- ชื่อ -->

        <div class="info-box">

            <div class="info-label">

                ชื่อ-นามสกุล

            </div>

            <div class="info-value">

                <?php

                echo htmlspecialchars(
                    $mother["fullname"]
                );

                ?>

            </div>

        </div>


        <!-- เลขบัตรประชาชน -->

        <div class="info-box">

            <div class="info-label">

                เลขบัตรประชาชน

            </div>

            <div class="info-value">

                <?php

                $id_card = $mother["id_card"];

                echo substr($id_card, 0, 1);

                echo "***********";

                echo substr($id_card, -1);

                ?>

            </div>

        </div>


        <!-- เบอร์โทร -->

        <div class="info-box">

            <div class="info-label">

                เบอร์โทรศัพท์

            </div>

            <div class="info-value">

                <?php

                echo htmlspecialchars(
                    $mother["phone"]
                );

                ?>

            </div>

        </div>


        <!-- วันเกิด -->

        <div class="info-box">

            <div class="info-label">

                วันเดือนปีเกิด

            </div>

            <div class="info-value">

                <?php

                echo htmlspecialchars(
                    $mother["birth_date"]
                );

                ?>

            </div>

        </div>


        <!-- วันที่คลอด -->

        <div class="info-box">

            <div class="info-label">

                วันที่คลอด

            </div>

            <div class="info-value">

                <?php

                echo htmlspecialchars(
                    $mother["delivery_date"]
                );

                ?>

            </div>

        </div>


    </div>

</div>



<!-- =========================
     ข้อมูลบุตร
========================= -->

<div class="profile-card">


    <h2 class="section-title">

        👶 ข้อมูลบุตร

    </h2>


    <?php if ($baby_result->num_rows > 0): ?>


        <div class="info">


            <?php while ($baby = $baby_result->fetch_assoc()): ?>


                <!-- ชื่อบุตร -->

                <div class="info-box">

                    <div class="info-label">

                        👶 ชื่อบุตร

                    </div>

                    <div class="info-value">

                        <?php

                        echo htmlspecialchars(
                            $baby["baby_name"]
                        );

                        ?>

                    </div>

                </div>


                <!-- วันเกิด -->

                <div class="info-box">

                    <div class="info-label">

                        🎂 วันเกิด

                    </div>

                    <div class="info-value">

                        <?php

                        echo htmlspecialchars(
                            $baby["birth_date"]
                        );

                        ?>

                    </div>

                </div>


                <!-- เพศ -->

                <div class="info-box">

                    <div class="info-label">

                        ⚧️ เพศ

                    </div>

                    <div class="info-value">

                        <?php

                        echo htmlspecialchars(
                            $baby["gender"]
                        );

                        ?>

                    </div>

                </div>


                <!-- น้ำหนักแรกเกิด -->

                <div class="info-box">

                    <div class="info-label">

                        ⚖️ น้ำหนักแรกเกิด

                    </div>

                    <div class="info-value">

                        <?php

                        echo htmlspecialchars(
                            $baby["birth_weight"]
                        );

                        ?>

                        กิโลกรัม

                    </div>

                </div>


                <!-- วิธีการคลอด -->

                <div class="info-box">

                    <div class="info-label">

                        🏥 วิธีการคลอด

                    </div>

                    <div class="info-value">

                        <?php

                        echo htmlspecialchars(
                            $baby["delivery_method"]
                        );

                        ?>

                    </div>

                </div>


            <?php endwhile; ?>


        </div>


    <?php else: ?>


        <p style="color:#999; text-align:center;">

            ยังไม่มีข้อมูลบุตรในระบบค่ะ 🌸

        </p>


    <?php endif; ?>


</div>