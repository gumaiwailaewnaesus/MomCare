<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once "db.php";

include "auto_notification.php";


/* =========================
   ตรวจสอบ Login
========================= */

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] !== "mother"
) {
    header("Location: login.php");
    exit();
}


$user_id = $_SESSION["user_id"];

$fullname = $_SESSION["fullname"] ?? "";


// หา mother_id ของคุณแม่
$getMother = $conn->prepare("
    SELECT mother_id
    FROM mothers
    WHERE user_id = ?
");

$getMother->bind_param("i", $user_id);

$getMother->execute();

$motherData = $getMother->get_result()->fetch_assoc();


$mother_id = $motherData["mother_id"];



/* =========================
   ดึงการแจ้งเตือนของคุณแม่
========================= */

$sql = "SELECT *
        FROM notifications
        WHERE mother_id = ?
        ORDER BY created_at DESC";


$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $mother_id);

$stmt->execute();


$notifications = $stmt->get_result();


?>


<!DOCTYPE html>

<html lang="th">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>การแจ้งเตือน | MomCare</title>


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


.container {

    max-width: 850px;

    margin: auto;

    padding: 45px 25px;

}


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


.header-icon {

    font-size: 55px;

    margin-bottom: 10px;

}


.header h1 {

    color: #df7895;

    margin-bottom: 10px;

}


.header p {

    color: #999;

}


.notification {

    background: white;

    padding: 25px;

    border-radius: 25px;

    margin-bottom: 18px;

    box-shadow:
        0 7px 25px
        rgba(220,130,160,0.08);

    border-left: 5px solid #df7895;

}


.notification-title {

    color: #df7895;

    font-size: 18px;

    font-weight: bold;

    margin-bottom: 8px;

}


.notification-message {

    color: #777;

    line-height: 1.7;

}


.notification-date {

    color: #aaa;

    font-size: 13px;

    margin-top: 12px;

}


.empty {

    background: white;

    padding: 55px 25px;

    border-radius: 25px;

    text-align: center;

    color: #999;

    box-shadow:
        0 7px 25px
        rgba(220,130,160,0.08);

}


.empty-icon {

    font-size: 60px;

    margin-bottom: 15px;

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


        <div class="header-icon">

            🔔

        </div>


        <h1>

            การแจ้งเตือน

        </h1>


        <p>

            สวัสดีค่ะ คุณ<?php echo htmlspecialchars($fullname); ?> 💗

        </p>


    </div>



<?php if ($notifications->num_rows > 0): ?>


    <?php while ($row = $notifications->fetch_assoc()): ?>


        <div class="notification">


            <div class="notification-title">

                🔔

                <?php echo htmlspecialchars($row["title"]); ?>

            </div>


            <div class="notification-message">

                <?php echo htmlspecialchars($row["message"]); ?>

            </div>


            <div class="notification-date">

                📅

                <?php

                echo date(
                    "d/m/Y H:i",
                    strtotime($row["created_at"])
                );

                ?>

            </div>


        </div>


    <?php endwhile; ?>


<?php else: ?>


    <div class="empty">


        <div class="empty-icon">

            🔔

        </div>


        <p>

            ตอนนี้ยังไม่มีการแจ้งเตือนค่ะ 🌸

        </p>


        <br>


        <p>

            หากมีข้อมูลสำคัญเกี่ยวกับการดูแลสุขภาพ

            ระบบจะแจ้งเตือนคุณแม่ที่หน้านี้ค่ะ 💗

        </p>


    </div>


<?php endif; ?>


</div>


</body>

</html>