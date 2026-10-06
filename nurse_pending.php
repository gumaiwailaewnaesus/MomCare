<?php

session_start();

require_once "db.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);


/* =========================
   ตรวจสอบสิทธิ์พยาบาล
========================= */

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] !== "nurse"
) {

    header("Location: login.php");
    exit();

}


$filter = $_GET["filter"] ?? "all";


/* =========================
   ดึงข้อมูลคุณแม่
========================= */

$sql = "

SELECT
m.mother_id,
m.fullname,
m.id_card,
m.birth_date,
m.phone,
m.delivery_date,
m.delivery_type

FROM mothers m

LEFT JOIN assessment_results ar

ON m.mother_id = ar.mother_id

WHERE ar.assessment_id IS NULL

ORDER BY m.delivery_date DESC

";

$result = $conn->query($sql);

if (!$result) {

    die("SQL Error : " . $conn->error);

}

// รัน SQL
$result = $conn->query($sql);


// เช็ก error
if (!$result) {

    die("SQL Error : " . $conn->error);

}

?>

<!DOCTYPE html>
<html lang="th">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    คุณแม่ทั้งหมด | MomCare
</title>


<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family:
        "Arial",
        sans-serif;

    background:
        linear-gradient(
            135deg,
            #fff5f8,
            #fffaf5
        );

    color: #555;

}


/* =========================
   Container
========================= */

.container {

    width: 90%;

    max-width: 1100px;

    margin:
        40px auto;

}


/* =========================
   Header
========================= */

.header {

    background:
        rgba(
            255,
            255,
            255,
            0.95
        );

    padding:
        25px 30px;

    border-radius:
        25px;

    box-shadow:
        0 8px 25px
        rgba(
            220,
            130,
            160,
            0.08
        );

    margin-bottom:
        25px;

}


.logo {

    color:
        #df7895;

    font-size:
        24px;

    font-weight:
        bold;

}


.header h1 {

    color:
        #bd6682;

    margin-bottom:
        5px;

}


.header p {

    color:
        #999;

}


/* =========================
   Back Button
========================= */

.back-button {

    display:
        inline-block;

    text-decoration:
        none;

    color:
        #bd6682;

    background:
        #fff0f5;

    padding:
        10px 18px;

    border-radius:
        15px;

    margin-bottom:
        20px;

    transition:
        0.3s;

}


.back-button:hover {

    background:
        #fbdce7;

}


/* =========================
   Mother Card
========================= */

.mother-card {

    background:
        rgba(
            255,
            255,
            255,
            0.95
        );

    padding:
        25px;

    border-radius:
        25px;

    margin-bottom:
        20px;

    box-shadow:
        0 8px 25px
        rgba(
            220,
            130,
            160,
            0.08
        );

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    transition:
        0.3s;

}


.mother-card:hover {

    transform:
        translateY(
            -4px
        );

    box-shadow:
        0 12px 30px
        rgba(
            220,
            130,
            160,
            0.15
        );

}


/* =========================
   Mother Info
========================= */

.mother-info h2 {

    margin:
        0 0 10px;

    color:
        #bd6682;

}


.mother-info p {

    margin:
        6px 0;

    color:
        #888;

}


/* =========================
   View Button
========================= */

.view-button {

    display:
        inline-block;

    text-decoration:
        none;

    background:
        #df7895;

    color:
        white;

    padding:
        12px 22px;

    border-radius:
        18px;

    transition:
        0.3s;

}


.view-button:hover {

    background:
        #c96180;

    transform:
        scale(
            1.03
        );

}


/* =========================
   Empty
========================= */

.empty {

    text-align:
        center;

    background:
        white;

    padding:
        50px;

    border-radius:
        25px;

    color:
        #999;

}


/* =========================
   Mobile
========================= */

@media (
    max-width: 700px
) {

    .mother-card {

        flex-direction:
            column;

        align-items:
            flex-start;

        gap:
            20px;

    }

    .view-button {

        width:
            100%;

        text-align:
            center;

    }

}

</style>

</head>


<body>


<div class="container">


    <div class="header">


        <div class="logo">

            🌸 MomCare

        </div>


        <h1>

            📋 คุณแม่รอประเมิน

        </h1>


        <p>

            รายชื่อคุณแม่ที่ยังไม่เคยทำแบบประเมิน

        </p>


    </div>


    <a
        href="nurse_dashboard.php"
        class="back-button"
    >

        ← กลับหน้าหลักพยาบาล

    </a>



    <?php if (
        $result &&
        $result->num_rows > 0
    ): ?>


        <?php while (
            $mother =
            $result->fetch_assoc()
        ): ?>


            <div class="mother-card">


                <div class="mother-info">


                    <h2>

                        👩🏻‍🍼

                        <?php

                        echo htmlspecialchars(
                            $mother["fullname"]
                        );

                        ?>

                    </h2>


                    <p>

                        🪪 เลขบัตรประชาชน:

                        <?php

                        echo htmlspecialchars(
                            $mother["id_card"]
                        );

                        ?>

                    </p>


                    <p>

                        📅 วันที่คลอด:

                        <?php

                        echo htmlspecialchars(
                            $mother["delivery_date"]
                        );

                        ?>

                    </p>


                    <p>

                        🏥 วิธีการคลอด:

                        <?php

                        echo htmlspecialchars(
                            $mother["delivery_type"]
                        );

                        ?>

                    </p>


                </div>


                <a
                    href="nurse_mother_detail.php?id=<?php echo $mother["mother_id"]; ?>"
                    class="view-button"
                >

                    ดูข้อมูล →

                </a>


            </div>


        <?php endwhile; ?>


    <?php else: ?>


        <div class="empty">

            👩🏻‍🍼

            <br><br>

            ยังไม่มีข้อมูลคุณแม่ในระบบ

        </div>


    <?php endif; ?>


</div>


</body>

</html>