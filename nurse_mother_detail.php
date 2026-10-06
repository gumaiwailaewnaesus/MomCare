<?php

session_start();

require_once "db.php";

/* =========================
   ลบข้อมูลคุณแม่
========================= */

if (isset($_POST["delete_mother"])) {

    // ตรวจสอบสิทธิ์พยาบาล
    if (
        !isset($_SESSION["user_id"]) ||
        $_SESSION["role"] !== "nurse"
    ) {
        header("Location: login.php");
        exit();
    }

    $mother_id = intval($_GET["id"]);

    // ดึง user_id ของคุณแม่ก่อนลบ
    $user_sql = "SELECT user_id FROM mothers WHERE mother_id = ?";
    $user_stmt = $conn->prepare($user_sql);
    $user_stmt->bind_param("i", $mother_id);
    $user_stmt->execute();

    $user_result = $user_stmt->get_result();
    $mother_user = $user_result->fetch_assoc();

    if (!$mother_user) {
        echo "<script>
        alert('ไม่พบข้อมูลคุณแม่ค่ะ');
        window.location.href='nurse_mothers.php';
        </script>";
        exit();
    }

    $user_id = $mother_user["user_id"];

    // เริ่ม Transaction
    $conn->begin_transaction();

    try {

        // ลบผลการประเมิน
        $stmt = $conn->prepare(
            "DELETE FROM assessment_results WHERE mother_id = ?"
        );
        $stmt->bind_param("i", $mother_id);
        $stmt->execute();

        // ลบประวัติการติดตาม
        $stmt = $conn->prepare(
            "DELETE FROM follow_up WHERE mother_id = ?"
        );
        $stmt->bind_param("i", $mother_id);
        $stmt->execute();

        // ลบการแจ้งเตือน
        $stmt = $conn->prepare(
            "DELETE FROM notifications WHERE mother_id = ?"
        );
        $stmt->bind_param("i", $mother_id);
        $stmt->execute();

        // ลบข้อมูลบุตร
        $stmt = $conn->prepare(
            "DELETE FROM babies WHERE mother_id = ?"
        );
        $stmt->bind_param("i", $mother_id);
        $stmt->execute();

        // ลบข้อมูลคุณแม่
        $stmt = $conn->prepare(
            "DELETE FROM mothers WHERE mother_id = ?"
        );
        $stmt->bind_param("i", $mother_id);
        $stmt->execute();

        // ลบบัญชีผู้ใช้ของคุณแม่
        $stmt = $conn->prepare(
            "DELETE FROM users WHERE user_id = ? AND role = 'mother'"
        );
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // ยืนยันการลบทั้งหมด
        $conn->commit();

        echo "<script>
        alert('ลบข้อมูลคุณแม่เรียบร้อยแล้วค่ะ 💗');
        window.location.href='nurse_mothers.php';
        </script>";
        exit();

    } catch (Exception $e) {

        // ถ้าเกิดปัญหา ยกเลิกการลบทั้งหมด
        $conn->rollback();

        echo "<script>
        alert('ไม่สามารถลบข้อมูลได้ กรุณาลองใหม่ค่ะ');
        window.location.href='nurse_mother_detail.php?id=$mother_id';
        </script>";
        exit();
    }
}

if(isset($_POST["save_follow"])){

    $mother_id = $_GET["id"];
    $nurse_id = $_SESSION["user_id"];
    $detail = $_POST["detail"];

    $sql_follow = "

    INSERT INTO follow_up
    (
        mother_id,
        nurse_id,
        detail
    )

    VALUES
    (
        '$mother_id',
        '$nurse_id',
        '$detail'
    )

    ";


  if($conn->query($sql_follow)){

    $title = "📝 บันทึกการติดตามจากพยาบาล";

    $message = $detail;

    $sql_notify = "

    INSERT INTO notifications
    (
        mother_id,
        title,
        message
    )

    VALUES
    (
        '$mother_id',
        '$title',
        '$message'
    )

    ";

    $conn->query($sql_notify);

    echo "<script>
    alert('บันทึกการติดตามเรียบร้อย 💗');
    window.location.href='nurse_mother_detail.php?id=$mother_id';
    </script>";

}
    

}

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


/* =========================
   ตรวจสอบ mother_id
========================= */

if (
    !isset($_GET["id"]) ||
    !is_numeric($_GET["id"])
) {

    header("Location: nurse_dashboard.php");

    exit();

}


$mother_id = intval($_GET["id"]);


/* =========================
   ดึงข้อมูลคุณแม่
========================= */

$sql = "

SELECT *

FROM mothers

WHERE mother_id = ?

";


$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $mother_id
);

$stmt->execute();

$result = $stmt->get_result();


if (
    $result->num_rows === 0
) {

    echo "ไม่พบข้อมูลคุณแม่";

    exit();

}


$mother = $result->fetch_assoc();


/* =========================
   ดึงข้อมูลบุตร
========================= */

$baby_sql = "

SELECT *

FROM babies

WHERE mother_id = ?

ORDER BY created_at DESC

";


$baby_stmt =
    $conn->prepare(
        $baby_sql
    );


$baby_stmt->bind_param(
    "i",
    $mother_id
);


$baby_stmt->execute();


$baby_result =
    $baby_stmt->get_result();


/* =========================
   ดึงประวัติการประเมิน
========================= */

$assessment_sql = "

SELECT *

FROM assessment_results

WHERE mother_id = ?

ORDER BY created_at DESC

";


$assessment_stmt =
    $conn->prepare(
        $assessment_sql
    );


$assessment_stmt->bind_param(
    "i",
    $mother_id
);


$assessment_stmt->execute();


$assessment_result =
    $assessment_stmt->get_result();

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

    ข้อมูลคุณแม่ | MomCare

</title>


<style>
    .section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 30px;
    margin-bottom: 20px;
}

.delete-mother-btn {
    display: block;
    width: 180px;
    margin: 15px auto 0;
    padding: 10px 15px;
    background: #e05a6f;
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}

.delete-mother-btn:hover {
    background: #c94459;
    transform: translateY(-1px);
}

.add-baby-btn {
    display: inline-block;
    padding: 10px 18px;
    background: #e89bb5;
    color: white;
    text-decoration: none;
    border-radius: 12px;
    font-weight: bold;
    transition: 0.3s;
}

.add-baby-btn:hover {
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


.back {

    color: #df7895;

    text-decoration: none;

    font-weight: bold;

}


.back:hover {

    color: #bd6682;

}


/* =========================
   Container
========================= */


.container {

    max-width:
        1000px;

    margin:
        0 auto;

    padding:
        40px 25px 60px;

}


/* =========================
   Header
========================= */


.header {

    text-align:
        center;

    margin-bottom:
        30px;

}


.profile-icon {

    font-size:
        60px;

    margin-bottom:
        10px;

}


.header h1 {

    color:
        #df7895;

    margin-bottom:
        8px;

}


.header p {

    color:
        #999;

}


/* =========================
   Card
========================= */


.card {

    background:
        rgba(
            255,
            255,
            255,
            0.95
        );

    padding:
        30px;

    border-radius:
        25px;

    margin-bottom:
        25px;

    box-shadow:
        0 8px 25px
        rgba(
            220,
            130,
            160,
            0.08
        );

}


/* =========================
   Section Title
========================= */


.section-title {

    color:
        #bd6682;

    font-size:
        21px;

    margin-bottom:
        20px;

}


/* =========================
   Information
========================= */


.info {

    display:
        grid;

    grid-template-columns:
        repeat(
            2,
            1fr
        );

    gap:
        15px;

}


.info-box {

    background:
        #fff7fa;

    padding:
        16px;

    border-radius:
        18px;

}


.info-label {

    color:
        #aaa;

    font-size:
        13px;

    margin-bottom:
        6px;

}


.info-value {

    color:
        #666;

    font-size:
        16px;

    font-weight:
        bold;

}


/* =========================
   Baby
========================= */


.baby-card {

    background:
        #fff7fa;

    padding:
        20px;

    border-radius:
        20px;

    margin-bottom:
        15px;

}


.baby-title {

    color:
        #df7895;

    font-size:
        18px;

    font-weight:
        bold;

    margin-bottom:
        15px;

}


/* =========================
   Assessment
========================= */


.assessment-card {

    background:
        #fff7fa;

    padding:
        20px;

    border-radius:
        20px;

    margin-bottom:
        15px;

}


.assessment-row {

    display:
        flex;

    justify-content:
        space-between;

    flex-wrap:
        wrap;

    gap:
        10px;

}


.score {

    color:
        #df7895;

    font-size:
        20px;

    font-weight:
        bold;

}


.result {

    font-weight:
        bold;

}


.date {

    color:
        #999;

    font-size:
        13px;

}


.normal {

    color:
        #4caf50;

}


.warning {

    color:
        #e6a700;

}


.risk {

    color:
        #e05a6f;

}


/* =========================
   Empty
========================= */


.empty {

    text-align:
        center;

    color:
        #aaa;

    padding:
        25px;

}


/* =========================
   Footer
========================= */


.footer {

    text-align:
        center;

    color:
        #aaa;

    font-size:
        13px;

    margin-top:
        40px;

}


/* =========================
   Responsive
========================= */


@media (
    max-width: 700px
) {


    .info {

        grid-template-columns:
            1fr;

    }


    .navbar {

        padding:
            18px 20px;

    }


    .container {

        padding:
            30px 15px;

    }


}


</style>


</head>


<body>


<!-- =========================
     Navbar
========================= -->


<div class="navbar">


    <div class="logo">

        🌸 MomCare

    </div>


    <a
        href="nurse_dashboard.php"
        class="back"
    >

        ← กลับหน้าหลักพยาบาล

    </a>


</div>



<div class="container">


<!-- =========================
     Header
========================= -->


<div class="header">


    <div class="profile-icon">

        👩🏻‍🍼

    </div>


    <h1>
    ข้อมูลคุณแม่
</h1>

<p>
    รายละเอียดข้อมูลและประวัติสุขภาพหลังคลอด
</p>




</div>



<!-- =========================
     Mother Information
========================= -->


<div class="card">


    <h2 class="section-title">

        👤 ข้อมูลส่วนตัวคุณแม่

    </h2>


    <div class="info">


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



        <div class="info-box">


            <div class="info-label">

                เลขบัตรประชาชน

            </div>


            <div class="info-value">

                <?php

                $id_card =
                    $mother["id_card"];

                echo substr(
                    $id_card,
                    0,
                    1
                );

                echo "***********";

                echo substr(
                    $id_card,
                    -1
                );

                ?>

            </div>


        </div>



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



        <div class="info-box">


            <div class="info-label">

                วิธีการคลอด

            </div>


            <div class="info-value">

                <?php

                echo htmlspecialchars(
                    $mother["delivery_type"]
                );

                ?>

            </div>


        </div>


       </div>

    <form
        method="POST"
        onsubmit="return confirm(
            '⚠️ ต้องการลบข้อมูลคุณแม่คนนี้ใช่หรือไม่?\n\nข้อมูลคุณแม่ ข้อมูลบุตร ประวัติการประเมิน การติดตาม และการแจ้งเตือนจะถูกลบทั้งหมด และไม่สามารถกู้คืนได้ค่ะ'
        );"
    >

        <button
            type="submit"
            name="delete_mother"
            class="delete-mother-btn"
        >
            🗑️ ลบข้อมูลคุณแม่
        </button>

    </form>

</div>



<!-- =========================
     Baby Information
========================= -->

<div class="card">


    <div class="section-header">

    <h2 class="section-title">
        👶 ข้อมูลบุตร
    </h2>

    <a
        href="add_baby.php?mother_id=<?php echo $mother_id; ?>"
        class="add-baby-btn"
    >
        ➕ เพิ่มข้อมูลบุตร
    </a>

</div>


    <?php if (
        $baby_result->num_rows > 0
    ): ?>


        <?php while (
            $baby =
            $baby_result->fetch_assoc()
        ): ?>


            <div class="baby-card">


                <div class="baby-title">

                    👶

                    <?php

                    echo htmlspecialchars(
                        $baby["baby_name"]
                    );

                    ?>

                </div>


                <div class="info">


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


                </div>


            </div>


        <?php endwhile; ?>


    <?php else: ?>


        <div class="empty">

            👶 ยังไม่มีข้อมูลบุตรในระบบค่ะ 🌸

        </div>


    <?php endif; ?>


</div>

<!-- =========================
     Follow Up
========================= -->

<div class="card">

<h2 class="section-title">
    📝 บันทึกการติดตาม
</h2>


<form method="POST">


<textarea
    name="detail"
    placeholder="รายละเอียดการติดตาม เช่น อาการของคุณแม่ คำแนะนำจากพยาบาล"
    style="
    width:100%;
    height:120px;
    padding:15px;
    border-radius:15px;
    border:1px solid #ddd;
    "
    required></textarea>


<br><br>


<button
type="submit"
name="save_follow"
style="
background:#df7895;
color:white;
border:none;
padding:12px 25px;
border-radius:20px;
cursor:pointer;
">

บันทึกการติดตาม 💗

</button>


</form>


</div>

<!-- =========================
     Assessment History
========================= -->


<div class="card">


    <h2 class="section-title">

        📋 ประวัติการประเมิน

    </h2>


    <?php if (
        $assessment_result->num_rows > 0
    ): ?>


        <?php while (
            $assessment =
            $assessment_result->fetch_assoc()
        ): ?>


            <div class="assessment-card">


                <div class="assessment-row">


                    <div>


                        <div class="date">

                            📅 วันที่ประเมิน

                        </div>


                        <div>

                            <?php

                            echo htmlspecialchars(
                                $assessment["created_at"]
                            );

                            ?>

                        </div>


                    </div>



                    <div>


                        <div class="date">

                            คะแนน

                        </div>


                        <div class="score">

                            <?php

                            echo htmlspecialchars(
                                $assessment["score"]
                            );

                            ?>

                            / 50

                        </div>


                    </div>



                    <div>


                        <div class="date">

                            ผลประเมิน

                        </div>


                        <?php


                        $result_class =
                            "normal";


                        if (
                            strpos(
                                $assessment["result"],
                                "ควรติดตาม"
                            ) !== false
                        ) {

                            $result_class =
                                "warning";

                        }


                        if (
                            strpos(
                                $assessment["result"],
                                "เสี่ยง"
                            ) !== false
                        ) {

                            $result_class =
                                "risk";

                        }


                        ?>


                        <div
                            class="result
                            <?php

                            echo $result_class;

                            ?>"
                        >

                            <?php

                            echo htmlspecialchars(
                                $assessment["result"]
                            );

                            ?>

                        </div>


                    </div>


                </div>


            </div>


        <?php endwhile; ?>


    <?php else: ?>


        <div class="empty">

            📋 คุณแม่ยังไม่มีประวัติการประเมินค่ะ 🌸

        </div>


    <?php endif; ?>


</div>



<div class="footer">

    🌸 MomCare |

    ระบบติดตามสุขภาพคุณแม่หลังคลอด 💗

</div>


</body>


</html>