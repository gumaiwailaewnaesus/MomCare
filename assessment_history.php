<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once "db.php";


/* =========================
   ตรวจสอบการเข้าสู่ระบบ
========================= */

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");

    exit();

}


$user_id = $_SESSION["user_id"];

$sql_mother = "SELECT mother_id
               FROM mothers
               WHERE user_id = ?";

$stmt_mother = $conn->prepare($sql_mother);

$stmt_mother->bind_param("i", $user_id);

$stmt_mother->execute();

$mother = $stmt_mother->get_result()->fetch_assoc();

$mother_id = $mother["mother_id"];

$fullname = $_SESSION["fullname"] ?? "";


/* =========================
   ดึงประวัติการประเมิน
========================= */

$sql = "SELECT *
        FROM assessment_results
        WHERE mother_id = ?
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $mother_id);

$stmt->execute();

$result_data = $stmt->get_result();


?>


<!DOCTYPE html>

<html lang="th">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>ประวัติการประเมิน | MomCare</title>


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

    max-width: 900px;

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


.history-card {

    background: white;

    padding: 25px;

    border-radius: 25px;

    margin-bottom: 18px;

    box-shadow:
        0 7px 25px
        rgba(220,130,160,0.08);

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;

}


.date {

    color: #999;

    font-size: 14px;

    margin-bottom: 8px;

}


.score {

    color: #777;

}


.score strong {

    color: #df7895;

}


.result {

    padding: 10px 18px;

    border-radius: 20px;

    font-weight: bold;

}


.normal {

    background: #e8f7ed;

    color: #4d9b68;

}


.warning {

    background: #fff5d9;

    color: #b58a25;

}


.risk {

    background: #ffe4e8;

    color: #c85c70;

}


.empty {

    background: white;

    padding: 50px 25px;

    border-radius: 25px;

    text-align: center;

    color: #999;

    box-shadow:
        0 7px 25px
        rgba(220,130,160,0.08);

}


.button {

    display: inline-block;

    margin-top: 20px;

    background: #df7895;

    color: white;

    padding: 12px 25px;

    border-radius: 25px;

    text-decoration: none;

}


@media (max-width: 650px) {

    .history-card {

        flex-direction: column;

        align-items: flex-start;

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


        <div class="header-icon">

            📋

        </div>


        <h1>

            ประวัติการประเมิน

        </h1>


        <p>

            ประวัติผลการประเมินสุขภาพของคุณ<?php echo htmlspecialchars($fullname); ?> 💗

        </p>


    </div>



<?php if ($result_data->num_rows > 0): ?>


    <?php while ($row = $result_data->fetch_assoc()): ?>


        <?php

        if ($row["result"] == "ปกติ") {

            $class = "normal";

            $icon = "🟢";

        }

        elseif ($row["result"] == "ควรติดตาม") {

            $class = "warning";

            $icon = "🟡";

        }

        else {

            $class = "risk";

            $icon = "🔴";

        }

        ?>


        <div class="history-card">


            <div>


                <div class="date">

                    📅

                    <?php

                    echo date(
                        "d/m/Y H:i",
                        strtotime($row["created_at"])
                    );

                    ?>

                </div>


                <div class="score">

                    คะแนนรวม:

                    <strong>

                        <?php echo $row["score"]; ?>

                    </strong>

                    / 40 คะแนน

                </div>


            </div>


            <div class="result <?php echo $class; ?>">

                <?php echo $icon; ?>

                <?php echo htmlspecialchars($row["result"]); ?>

            </div>


        </div>


    <?php endwhile; ?>


<?php else: ?>


    <div class="empty">


        📋


        <br><br>


        ยังไม่มีประวัติการประเมินค่ะ 💗


        <br>


        เมื่อคุณแม่ทำแบบประเมินแล้ว

        ผลการประเมินจะแสดงที่หน้านี้ค่ะ


        <br>


        <a
            href="assessment.php"
            class="button"
        >

            📝 ทำแบบประเมิน

        </a>


    </div>


<?php endif; ?>


</div>


</body>

</html>