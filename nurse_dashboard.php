<?php

session_start();

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "nurse"
) {
    header("Location: login.php");
    exit();
}

$fullname = $_SESSION["fullname"];

require_once "db.php";

/* =========================
   ข้อมูลกราฟผลประเมิน
========================= */

$chart_sql = "

SELECT 
result,
COUNT(*) AS total

FROM assessment_results

GROUP BY result

";

$chart_result = $conn->query($chart_sql);


$normal = 0;
$follow = 0;
$risk = 0;


while($row = $chart_result->fetch_assoc()){

    if(strpos($row["result"],"ปกติ") !== false){

        $normal = $row["total"];

    }

    elseif(strpos($row["result"],"ควรติดตาม") !== false){

        $follow = $row["total"];

    }

    elseif(strpos($row["result"],"เสี่ยง") !== false){

        $risk = $row["total"];

    }

}

error_reporting(E_ALL);
ini_set('display_errors', 1);


$fullname = $_SESSION["fullname"];


/* =========================
   ดึงข้อมูลคุณแม่ทั้งหมด
   พร้อมผลประเมินล่าสุด
========================= */

$sql = "

SELECT

    m.mother_id,

    m.fullname,

    m.birth_date,

    m.phone,

    m.delivery_date,

    m.delivery_type,

    ar.score,

    ar.result,

    ar.created_at

FROM mothers m


LEFT JOIN assessment_results ar

ON ar.assessment_id = (

    SELECT ar2.assessment_id

    FROM assessment_results ar2

    WHERE ar2.mother_id = m.mother_id

    ORDER BY ar2.created_at DESC

    LIMIT 1

)


ORDER BY m.delivery_date DESC

";


$result = $conn->query($sql);


/* =========================
   นับจำนวนคุณแม่
========================= */

$count_sql = "

SELECT COUNT(*) AS total

FROM mothers

";


$count_result = $conn->query($count_sql);

$count_data = $count_result->fetch_assoc();

$total_mothers = $count_data["total"];

/* =========================
   นับจำนวนคุณแม่ที่มีความเสี่ยง
========================= */

$risk_sql = "

SELECT COUNT(*) AS total_risk

FROM mothers m

JOIN assessment_results ar

ON ar.assessment_id = (

    SELECT ar2.assessment_id

    FROM assessment_results ar2

    WHERE ar2.mother_id = m.mother_id

    ORDER BY ar2.created_at DESC

    LIMIT 1

)

WHERE ar.result = 'มีความเสี่ยง'

";


$risk_result = $conn->query($risk_sql);

$risk_data = $risk_result->fetch_assoc();

$total_risk = $risk_data["total_risk"];

/* =========================
   นับจำนวนคุณแม่รอประเมิน
========================= */

$pending_sql = "
SELECT COUNT(*) AS total_pending
FROM mothers m
LEFT JOIN assessment_results ar
ON m.mother_id = ar.mother_id
WHERE ar.assessment_id IS NULL
";

$pending_result = $conn->query($pending_sql);

$pending_data = $pending_result->fetch_assoc();

$total_pending = $pending_data["total_pending"];

/* =========================
   นับจำนวนคุณแม่ที่ต้องติดตาม
========================= */

$follow_sql = "

SELECT COUNT(*) AS total_follow

FROM mothers m

JOIN assessment_results ar

ON ar.assessment_id = (

    SELECT ar2.assessment_id

    FROM assessment_results ar2

    WHERE ar2.mother_id = m.mother_id

    ORDER BY ar2.created_at DESC

    LIMIT 1

)

WHERE ar.result = 'ควรติดตาม'

";


$follow_result = $conn->query($follow_sql);

$follow_data = $follow_result->fetch_assoc();

$total_follow = $follow_data["total_follow"];
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

    หน้าหลักพยาบาล | MomCare

</title>


<style>
    .top-actions {
    display: flex;
    justify-content: flex-end;
    margin: 20px 0 30px;
}

.add-mother-btn {
    display: inline-block;
    padding: 12px 22px;
    background: #e89bb5;
    color: white;
    text-decoration: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: bold;
    transition: 0.3s;
}

.add-mother-btn:hover {
    background: #d982a1;
    transform: translateY(-2px);
}


* {

    margin: 0;

    padding: 0;

    box-sizing: border-box;

    font-family: Arial, sans-serif;

}


body {

    min-height: 100vh;

    background:
        linear-gradient(
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

    background:
        rgba(
            255,
            255,
            255,
            0.95
        );

    padding:
        18px 7%;

    display: flex;

    justify-content:
        space-between;

    align-items:
        center;

    box-shadow:
        0 3px 20px
        rgba(
            210,
            130,
            160,
            0.10
        );

}


.logo {

    color: #df7895;

    font-size: 25px;

    font-weight: bold;

}


.nurse-info {

    display: flex;

    align-items: center;

    gap: 15px;

}


.nurse-name {

    color: #bd6682;

    font-weight: bold;

}


.logout {

    color: #df7895;

    text-decoration: none;

    padding:
        9px 18px;

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

    max-width:
        1100px;

    margin:
        0 auto;

    padding:
        45px 25px 60px;

}


/* =========================
   Welcome
========================= */


.welcome {

    background:
        linear-gradient(
            135deg,
            #ffffff,
            #fff7fa
        );

    padding:
        35px;

    border-radius:
        30px;

    margin-bottom:
        25px;

    box-shadow:
        0 10px 30px
        rgba(
            220,
            130,
            160,
            0.10
        );

}


.welcome h1 {

    color:
        #df7895;

    font-size:
        28px;

    margin-bottom:
        10px;

}


.welcome p {

    color:
        #888;

}


/* =========================
   Statistics
========================= */


.stats {

    display:
        grid;

    grid-template-columns:
        repeat(
            3,
            1fr
        );

    gap:
        20px;

    margin-bottom:
        35px;

}


.stat-card {

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

    text-align:
        center;

    box-shadow:
        0 8px 25px
        rgba(
            220,
            130,
            160,
            0.08
        );

    /* ทำให้การ์ดที่เป็นลิงก์ไม่มีเส้นใต้ */

    text-decoration:
        none;

    /* ใช้สีตัวอักษรเดิม */

    color:
        inherit;

    /* เปลี่ยนเมาส์เป็นรูปมือ */

    cursor:
        pointer;

    /* ทำให้เอฟเฟกต์นุ่มนวล */

    transition:
        0.3s;

}

.stat-card:hover {

    transform:
        translateY(
            -5px
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


.stat-icon {

    font-size:
        38px;

    margin-bottom:
        10px;

}


.stat-number {

    color:
        #df7895;

    font-size:
        30px;

    font-weight:
        bold;

}


.stat-label {

    color:
        #999;

    margin-top:
        5px;

}


/* =========================
   Section Title
========================= */


.section-title {

    color:
        #bd6682;

    font-size:
        22px;

    margin-bottom:
        20px;

}

/* =========================
   Chart
========================= */

.chart-card {

    background: white;

    padding: 30px;

    border-radius: 25px;

    margin-bottom: 35px;

    box-shadow:
        0 8px 25px
        rgba(220,130,160,0.08);

}


.chart-card h2 {

    color: #bd6682;

    text-align: center;

    margin-bottom: 20px;

}


#healthChart {

    max-height: 350px;

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
        18px;

    box-shadow:
        0 8px 25px
        rgba(
            220,
            130,
            160,
            0.08
        );

    border:
        1px solid
        rgba(
            255,
            220,
            230,
            0.5
        );

}


.mother-header {

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    margin-bottom:
        18px;

}


.mother-name {

    color:
        #df7895;

    font-size:
        20px;

    font-weight:
        bold;

}


.mother-data {

    display:
        grid;

    grid-template-columns:
        repeat(
            2,
            1fr
        );

    gap:
        12px;

}


.data-item {

    background:
        #fff7fa;

    padding:
        12px 15px;

    border-radius:
        15px;

}


.data-label {

    color:
        #aaa;

    font-size:
        12px;

    margin-bottom:
        4px;

}


.data-value {

    color:
        #666;

    font-size:
        14px;

}


/* =========================
   Assessment Result
========================= */


.assessment {

    margin-top:
        20px;

    padding:
        18px;

    border-radius:
        18px;

    background:
        #faf5f8;

}


.assessment-title {

    color:
        #bd6682;

    font-weight:
        bold;

    margin-bottom:
        10px;

}


.result-normal {

    color:
        #4caf50;

    font-weight:
        bold;

}


.result-warning {

    color:
        #e6a700;

    font-weight:
        bold;

}


.result-risk {

    color:
        #e05a6f;

    font-weight:
        bold;

}


.no-assessment {

    color:
        #aaa;

    font-size:
        14px;

}


/* =========================
   Button
========================= */


.detail-button {

    display:
        inline-block;

    margin-top:
        18px;

    background:
        #df7895;

    color:
        white;

    padding:
        10px 20px;

    border-radius:
        20px;

    text-decoration:
        none;

    font-size:
        14px;

    transition:
        0.3s;

}


.detail-button:hover {

    background:
        #c96380;

    transform:
        translateY(-2px);

}


/* =========================
   Empty
========================= */
/* =========================
   Add Mother Button
========================= */

.add-mother-button {

    display: inline-block;

    background:
        linear-gradient(
            135deg,
            #df7895,
            #e99ab0
        );

    color: white;

    padding:
        12px 25px;

    border-radius:
        25px;

    text-decoration:
        none;

    font-weight:
        bold;

    font-size:
        15px;

    box-shadow:
        0 5px 15px
        rgba(
            223,
            120,
            149,
            0.20
        );

    transition:
        0.3s;

}

.add-mother-button:hover {

    background:
        linear-gradient(
            135deg,
            #c96380,
            #df7895
        );

    transform:
        translateY(-2px);

}


.empty {

    background:
        white;

    padding:
        40px;

    border-radius:
        25px;

    text-align:
        center;

    color:
        #aaa;

}


/* =========================
   Footer
========================= */


.footer {

    text-align:
        center;

    margin-top:
        45px;

    color:
        #aaa;

    font-size:
        13px;

}


/* =========================
   Responsive
========================= */


@media (
    max-width: 700px
) {


    .stats {

        grid-template-columns:
            1fr;

    }


    .mother-data {

        grid-template-columns:
            1fr;

    }


    .mother-header {

        flex-direction:
            column;

        align-items:
            flex-start;

        gap:
            8px;

    }


    .nurse-info {

        flex-direction:
            column;

        gap:
            5px;

        align-items:
            flex-end;

    }


}


</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>


<body>


<!-- =========================
     Navbar
========================= -->


<div class="navbar">


    <div class="logo">

        🌸 MomCare

    </div>


    <div class="nurse-info">


        <div class="nurse-name">

            👩🏻‍⚕️ คุณ<?php

            echo htmlspecialchars(
                $fullname
            );

            ?>

        </div>


        <a
            href="logout.php"
            class="logout"
        >

            ออกจากระบบ

        </a>


    </div>


</div>



<!-- =========================
     Main
========================= -->


<div class="container">


    <!-- Welcome -->


    <div class="welcome">


        <h1>

            สวัสดีค่ะ คุณ

            <?php

            echo htmlspecialchars(
                $fullname
            );

            ?>

            👩🏻‍⚕️💗

        </h1>


        <p>

            ยินดีต้อนรับเข้าสู่ระบบติดตามสุขภาพคุณแม่หลังคลอด

        </p>


    </div>



    <!-- Statistics -->


    <div class="stats">


    <a
    href="nurse_mothers.php?filter=all"
    class="stat-card"
>

    <div class="stat-icon">

        👩🏻‍🍼

    </div>


    <div class="stat-number">

        <?php

        echo $total_mothers;

        ?>

    </div>


    <div class="stat-label">

        คุณแม่ทั้งหมด

    </div>


</a>

<a
    href="nurse_mothers.php?filter=risk"
    class="stat-card"
>

    <div class="stat-icon">

        🔴

    </div>

    <div class="stat-number">

        <?php
        echo $total_risk;
        ?>

    </div>

    <div class="stat-label">

        คุณแม่ที่มีความเสี่ยง

    </div>

</a>
<a
    href="nurse_pending.php" class="stat-card">



            <div class="stat-icon">

                📋

            </div>


            <div class="stat-number">

                <?php echo $total_pending; ?>

            </div>


            <div class="stat-label">

                รอประเมิน

            </div>
    </a>


 <a href="nurse_follow.php" class="stat-card">
    <div class="stat-icon">
        ⚠️
    </div>

    <div class="stat-number">
        <?php echo $total_follow; ?>
    </div>

    <div class="stat-label">
        ต้องติดตาม
    </div>
</a>

<a href="https://breastfeeding-assessment-system.vercel.app/" class="stat-card">
    <div class="stat-icon">
        🍼
    </div>

    <div class="stat-number">
        →
    </div>

    <div class="stat-label">
        แบบประเมินและติดตามการเลี้ยงลูกด้วยนมแม่
    </div>
</a>

</div>


<div class="chart-card">

<h2>
📊 สรุปผลการประเมินสุขภาพ
</h2>


<canvas id="healthChart"></canvas>


</div>


</div>

    <!-- Mother List -->


    <h2 class="section-title">

        👩🏻‍🍼 รายชื่อคุณแม่ทั้งหมด

    </h2>
    <div class="top-actions">

    <a href="add_mother.php" class="add-mother-btn">
        ➕ เพิ่มข้อมูลแม่
    </a>

</div>



    <?php if ($result && $result->num_rows > 0): ?>


        <?php while (
            $mother =
            $result->fetch_assoc()
        ): ?>


            <div class="mother-card">


                <div class="mother-header">


                    <div class="mother-name">

                        👩🏻

                        <?php

                        echo htmlspecialchars(
                            $mother["fullname"]
                        );

                        ?>

                    </div>


                </div>



                <div class="mother-data">


                    <div class="data-item">


                        <div class="data-label">

                            🎂 วันเกิด

                        </div>


                        <div class="data-value">

                            <?php

                            echo htmlspecialchars(
                                $mother["birth_date"]
                            );

                            ?>

                        </div>


                    </div>



                    <div class="data-item">


                        <div class="data-label">

                            📱 เบอร์โทรศัพท์

                        </div>


                        <div class="data-value">

                            <?php

                            echo htmlspecialchars(
                                $mother["phone"]
                            );

                            ?>

                        </div>


                    </div>



                    <div class="data-item">


                        <div class="data-label">

                            🤱 วันที่คลอด

                        </div>


                        <div class="data-value">

                            <?php

                            echo htmlspecialchars(
                                $mother["delivery_date"]
                            );

                            ?>

                        </div>


                    </div>



                    <div class="data-item">


                        <div class="data-label">

                            🏥 วิธีการคลอด

                        </div>


                        <div class="data-value">

                            <?php

                            echo htmlspecialchars(
                                $mother["delivery_type"]
                            );

                            ?>

                        </div>


                    </div>


                </div>



                <!-- Assessment -->


                <div class="assessment">


                    <div class="assessment-title">

                        📋 ผลการประเมินล่าสุด

                    </div>


                    <?php if (
                        $mother["score"]
                        !== null
                    ): ?>


                        <p>

                            คะแนน:

                            <strong>

                                <?php

                                echo $mother["score"];

                                ?>

                            </strong>

                            คะแนน

                        </p>


                        <p>

                            ผลประเมิน:

                            <?php


                            $result_class =
                                "result-normal";


                            if (
                                strpos(
                                    $mother["result"],
                                    "เฝ้าระวัง"
                                ) !== false
                            ) {

                                $result_class =
                                    "result-warning";

                            }


                            if (
                                strpos(
                                    $mother["result"],
                                    "เสี่ยง"
                                ) !== false
                            ) {

                                $result_class =
                                    "result-risk";

                            }


                            ?>


                            <span
                                class="<?php

                                echo $result_class;

                                ?>"
                            >

                                <?php

                                echo htmlspecialchars(
                                    $mother["result"]
                                );

                                ?>

                            </span>

                        </p>


                        <p>

                            วันที่ประเมิน:

                            <?php

                            echo htmlspecialchars(
                                $mother["created_at"]
                            );

                            ?>

                        </p>


                    <?php else: ?>


                        <p class="no-assessment">

                            ยังไม่มีผลการประเมินค่ะ 🌸

                        </p>


                    <?php endif; ?>


                </div>



                <a

                    href="nurse_mother_detail.php?id=<?php

                    echo $mother["mother_id"];

                    ?>"

                    class="detail-button"

                >

                    🔍 ดูข้อมูลคุณแม่

                </a>


            </div>


        <?php endwhile; ?>


    <?php else: ?>


        <div class="empty">

            🌸 ยังไม่มีข้อมูลคุณแม่ในระบบค่ะ

        </div>


    <?php endif; ?>



    <div class="footer">

        🌸 MomCare |

        ระบบติดตามสุขภาพคุณแม่หลังคลอด 💗

    </div>


</div>

<script>

const ctx = document.getElementById('healthChart');


new Chart(ctx, {

    type: 'doughnut',

    data: {

        labels: [
            'ปกติ',
            'ควรติดตาม',
            'มีความเสี่ยง'
        ],

        datasets: [{

            data: [
                <?php echo $normal; ?>,
                <?php echo $follow; ?>,
                <?php echo $risk; ?>
            ]

        }]

    },

    options: {

        responsive:true

    }

});

</script>

</body>


</html>