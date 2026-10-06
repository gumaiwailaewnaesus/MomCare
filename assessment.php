<?php

session_start();

require_once "db.php";


if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "mother"
) {
    header("Location: login.php");
    exit();
}


$fullname = $_SESSION["fullname"];
$user_id = $_SESSION["user_id"];


// ดึงข้อมูลคุณแม่
$sql = "SELECT mother_id
        FROM mothers
        WHERE user_id = ?";


$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result_mother = $stmt->get_result();

$mother = $result_mother->fetch_assoc();


if (!$mother) {

    die("
        ไม่พบข้อมูลคุณแม่ในระบบ<br>
        USER ID = ".$user_id."
    ");

}


$mother_id = $mother["mother_id"];



$period = $_GET["period"] ?? "";

$period_name = "";

switch ($period) {

    case "7days":
        $period_name = "7 วันหลังคลอด";
        break;

    case "14days":
        $period_name = "14 วันหลังคลอด";
        break;

    case "3months":
        $period_name = "3 เดือนหลังคลอด";
        break;

    case "6months":
        $period_name = "6 เดือนหลังคลอด";
        break;

    default:
        $period_name = "การประเมิน";
        break;
}


$result = "";
$score = 0;
$already_done = false;


// =========================
// เมื่อกดส่งแบบประเมิน
// =========================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // เช็กเฉพาะแบบประเมินตามระยะ
    if ($period != "now") {

        $check_sql = "
            SELECT assessment_id
            FROM assessment_results
            WHERE mother_id = ?
            AND period = ?
        ";

        $check_stmt = $conn->prepare($check_sql);

        $check_stmt->bind_param(
            "is",
            $mother_id,
            $period
        );

        $check_stmt->execute();

        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {

            echo "
            <script>
            alert('คุณแม่ได้ทำแบบประเมินช่วงนี้แล้วค่ะ 💗');
            window.history.back();
            </script>
            ";

            exit();
        }

    }

    // คำนวณคะแนนต่อ...
    for ($i = 1; $i <= 10; $i++) {

        if (isset($_POST["q".$i])) {

            $score += intval($_POST["q".$i]);

        }

    }

    // แปลผล

   if ($score <= 13) {

    $result = "ปกติ";

} elseif ($score <= 25) {

    $result = "ควรติดตาม";

} else {

    $result = "มีความเสี่ยง";

}


    // บันทึกผล

    $sql = "
        INSERT INTO assessment_results
        (mother_id, period, score, result)
        VALUES (?, ?, ?, ?)
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
    die("Prepare Error: " . $conn->error);
}

$stmt->bind_param(
    "isis",
    $mother_id,
    $period,
    $score,
    $result
);

    if ($stmt->execute()) {

 $assessment_id = $conn->insert_id;

 // สร้างแจ้งเตือนหลังประเมิน

$title = "ผลการประเมินสุขภาพหลังคลอด";


if ($result == "ปกติ") {

    $message = "ผลการประเมินของคุณแม่อยู่ในระดับปกติค่ะ 💗";

} elseif ($result == "ควรติดตาม") {

    $message = "พบอาการที่ควรติดตาม แนะนำให้คุณแม่ดูแลสุขภาพอย่างต่อเนื่องค่ะ 🌸";

} else {

    $message = "พบความเสี่ยงจากการประเมิน แนะนำให้ติดต่อพยาบาลเพื่อรับคำแนะนำค่ะ 👩🏻‍⚕️";

}


$noti_sql = "
INSERT INTO notifications
(mother_id, title, message)
VALUES (?, ?, ?)
";


$noti_stmt = $conn->prepare($noti_sql);


$noti_stmt->bind_param(
    "iss",
    $mother_id,
    $title,
    $message
);


$noti_stmt->execute();

 header("Location: assessment_result.php?id=".$assessment_id);

exit();


    } else {


        die("บันทึกไม่สำเร็จ: ".$stmt->error);


    }


}



// ตรวจสอบว่าระยะนี้เคยทำแล้วหรือยัง
if ($period != "now") {

$check_sql = "
    SELECT assessment_id
    FROM assessment_results
    WHERE mother_id = ?
    AND period = ?
";


$check_stmt = $conn->prepare($check_sql);


$check_stmt->bind_param(
    "is",
    $mother_id,
    $period
);


$check_stmt->execute();


$check_result = $check_stmt->get_result();


$already_done = ($check_result->num_rows > 0);

}

?>

<!DOCTYPE html>

<html lang="th">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>ประเมินอาการ | MomCare</title>


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

    max-width: 850px;

    margin: 0 auto;

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


.header-icon {

    font-size: 55px;

    margin-bottom: 10px;

}


.header h1 {

    color: #df7895;

    margin-bottom: 10px;

}


.header p {

    color: #888;

    line-height: 1.7;

}


/* =========================
   Result Box
========================= */

.result-box {

    background: white;

    padding: 35px;

    border-radius: 30px;

    text-align: center;

    margin-bottom: 25px;

    box-shadow:
        0 10px 30px
        rgba(220,130,160,0.12);

    border: 2px solid #f8dce7;

}


.result-icon {

    font-size: 60px;

    margin-bottom: 10px;

}


.result-box h2 {

    color: #df7895;

    margin-bottom: 15px;

}


.result-box p {

    color: #777;

    line-height: 1.8;

    margin-bottom: 5px;

}


.result-box strong {

    color: #df7895;

    font-size: 20px;

}


/* =========================
   Section Title
========================= */

.section-title {

    color: #bd6682;

    font-size: 20px;

    margin: 25px 0 15px;

}


/* =========================
   Question Card
========================= */

.question-card {

    background: white;

    padding: 30px;

    border-radius: 25px;

    margin-bottom: 20px;

    box-shadow:
        0 7px 25px
        rgba(220,130,160,0.08);

}


.question-number {

    color: #df7895;

    font-size: 14px;

    margin-bottom: 10px;

    font-weight: bold;

}


.question {

    color: #555;

    font-size: 18px;

    font-weight: bold;

    line-height: 1.6;

    margin-bottom: 20px;

}


/* =========================
   Options
========================= */

.option {

    display: block;

    padding: 15px 18px;

    border: 1px solid #f1d2dc;

    border-radius: 15px;

    margin-bottom: 10px;

    cursor: pointer;

    transition: 0.3s;

}


.option:hover {

    background: #fff5f8;

    border-color: #e889a2;

    transform: translateX(3px);

}


.option input {

    margin-right: 10px;

    accent-color: #df7895;

}


/* =========================
   Submit Button
========================= */

.submit-button {

    width: 100%;

    padding: 16px;

    border: none;

    border-radius: 25px;

    background: #df7895;

    color: white;

    font-size: 17px;

    cursor: pointer;

    margin-top: 10px;

    transition: 0.3s;

}


.submit-button:hover {

    background: #c96380;

    transform: translateY(-2px);

}


/* =========================
   Note
========================= */

.note {

    text-align: center;

    color: #999;

    font-size: 13px;

    line-height: 1.7;

    margin-top: 20px;

}


/* =========================
   Responsive
========================= */

@media (max-width: 700px) {

    .container {

        padding: 30px 15px;

    }


    .header {

        padding: 25px 20px;

    }


    .question-card {

        padding: 22px;

    }


    .question {

        font-size: 16px;

    }

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
        href="mother_dashboard.php"
        class="back"
    >

        ← กลับหน้าหลัก

    </a>


</nav>



<div class="container">


<!-- =========================
     Header
========================= -->

<div class="header">


    <div class="header-icon">

        📝

    </div>


    <h1>

        แบบประเมินอาการหลังคลอด

    </h1>


    <p>

        สวัสดีค่ะ คุณ<?php echo htmlspecialchars($fullname); ?> 🤱🏻💗

        <br>

        กรุณาเลือกคำตอบที่ตรงกับอาการของคุณแม่มากที่สุด

    </p>


</div>



<!-- =========================
     Result
========================= -->

<?php if ($result != ""): ?>


<div class="result-box">


<?php if ($result == "ปกติ"): ?>


    <div class="result-icon">

        🟢

    </div>


    <h2>

        ผลการประเมิน: ปกติ

    </h2>


    <p>

        คะแนนรวมของคุณแม่คือ

        <strong>

            <?php echo $score; ?>

        </strong>

        คะแนน

    </p>


    <p>

        โดยรวมยังไม่พบสัญญาณความเสี่ยงที่น่ากังวลค่ะ 🌸

    </p>



<?php elseif ($result == "ควรติดตาม"): ?>


    <div class="result-icon">

        🟡

    </div>


    <h2>

        ผลการประเมิน: ควรติดตาม

    </h2>


    <p>

        คะแนนรวมของคุณแม่คือ

        <strong>

            <?php echo $score; ?>

        </strong>

        คะแนน

    </p>


    <p>

        แนะนำให้ติดตามอาการอย่างต่อเนื่อง
        และทำแบบประเมินตามกำหนดค่ะ 💗

    </p>



<?php else: ?>


    <div class="result-icon">

        🔴

    </div>


    <h2>

        ผลการประเมิน: มีความเสี่ยง

    </h2>


    <p>

        คะแนนรวมของคุณแม่คือ

        <strong>

            <?php echo $score; ?>

        </strong>

        คะแนน

    </p>


    <p>

        ระบบจะแจ้งเตือนพยาบาล
        เพื่อให้ติดตามอาการของคุณแม่ค่ะ 👩🏻‍⚕️

    </p>


<?php endif; ?>


</div>


<?php endif; ?>

<form method="POST">

<?php if ($already_done): ?>

<div class="result-box">

    <div class="result-icon">
        ✅
    </div>

    <h2>
        คุณได้ทำแบบประเมินระยะนี้แล้ว
    </h2>

    <p>
        ระยะ <strong><?php echo $period_name; ?></strong>
        ได้รับการประเมินเรียบร้อยแล้วค่ะ 🌸
    </p>

    <p>
        ไม่สามารถทำแบบประเมินซ้ำในระยะเดียวกันได้
    </p>

<br><br>

<a href="assessment_period.php" class="submit-button">
    ← กลับไปเลือกระยะการประเมิน
</a>

<br><br>

</div>

<?php else: ?>
<!-- =========================
     Form
========================= -->

<form
    action="assessment_result.php"
    method="POST"
>



<!-- =========================
     ด้านร่างกาย
========================= -->

<h2 class="section-title">
    <form method="POST" action="">

    🩷 อาการทางร่างกาย

</h2>



<!-- Question 1 -->

<div class="question-card">

<form method="POST">
<div class="question-number">

    คำถามที่ 1

</div>


<div class="question">

    คุณแม่มีอาการปวดบริเวณแผลหลังคลอดหรือไม่?

</div>


<label class="option">

    <input
        type="radio"
        name="q1"
        value="0"
        required
    >

    ไม่มี

</label>


<label class="option">

    <input
        type="radio"
        name="q1"
        value="1"
    >

    เล็กน้อย

</label>


<label class="option">

    <input
        type="radio"
        name="q1"
        value="2"
    >

    ปานกลาง

</label>


<label class="option">

    <input
        type="radio"
        name="q1"
        value="3"
    >

    มาก

</label>


<label class="option">

    <input
        type="radio"
        name="q1"
        value="4"
    >

    มากที่สุด

</label>


</div>



<!-- Question 2 -->

<div class="question-card">


<div class="question-number">

    คำถามที่ 2

</div>


<div class="question">

    คุณแม่มีอาการปวดท้องน้อยหรือมดลูกบีบตัวมากผิดปกติหรือไม่?

</div>


<label class="option">

    <input type="radio" name="q2" value="0" required>

    ไม่มี

</label>


<label class="option">

    <input type="radio" name="q2" value="1">

    เล็กน้อย

</label>


<label class="option">

    <input type="radio" name="q2" value="2">

    ปานกลาง

</label>


<label class="option">

    <input type="radio" name="q2" value="3">

    มาก

</label>


<label class="option">

    <input type="radio" name="q2" value="4">

    มากที่สุด

</label>


</div>



<!-- Question 3 -->

<div class="question-card">


<div class="question-number">

    คำถามที่ 3

</div>


<div class="question">

    คุณแม่มีเลือดหรือน้ำคาวปลาออกมากผิดปกติหรือไม่?

</div>


<label class="option">

    <input type="radio" name="q3" value="0" required>

    ไม่มี

</label>


<label class="option">

    <input type="radio" name="q3" value="1">

    เล็กน้อย

</label>


<label class="option">

    <input type="radio" name="q3" value="2">

    ปานกลาง

</label>


<label class="option">

    <input type="radio" name="q3" value="3">

    มาก

</label>


<label class="option">

    <input type="radio" name="q3" value="4">

    มากที่สุด

</label>


</div>



<!-- Question 4 -->

<div class="question-card">


<div class="question-number">

    คำถามที่ 4

</div>


<div class="question">

    คุณแม่มีไข้ หนาวสั่น หรือรู้สึกไม่สบายผิดปกติหรือไม่?

</div>


<label class="option">

    <input type="radio" name="q4" value="0" required>

    ไม่มี

</label>


<label class="option">

    <input type="radio" name="q4" value="1">

    เล็กน้อย

</label>


<label class="option">

    <input type="radio" name="q4" value="2">

    ปานกลาง

</label>


<label class="option">

    <input type="radio" name="q4" value="3">

    มาก

</label>


<label class="option">

    <input type="radio" name="q4" value="4">

    มากที่สุด

</label>


</div>



<!-- =========================
     การให้นม
========================= -->

<h2 class="section-title">

    🍼 การให้นมบุตร

</h2>



<!-- Question 5 -->

<div class="question-card">


<div class="question-number">

    คำถามที่ 5

</div>


<div class="question">

    คุณแม่มีอาการเจ็บ คัด ตึง หรือผิดปกติบริเวณเต้านมหรือไม่?

</div>


<label class="option">

    <input type="radio" name="q5" value="0" required>

    ไม่มี

</label>


<label class="option">

    <input type="radio" name="q5" value="1">

    เล็กน้อย

</label>


<label class="option">

    <input type="radio" name="q5" value="2">

    ปานกลาง

</label>


<label class="option">

    <input type="radio" name="q5" value="3">

    มาก

</label>


<label class="option">

    <input type="radio" name="q5" value="4">

    มากที่สุด

</label>


</div>



<!-- Question 6 -->

<div class="question-card">


<div class="question-number">

    คำถามที่ 6

</div>


<div class="question">

    คุณแม่มีปัญหาเกี่ยวกับการให้นมบุตรหรือไม่?

</div>


<label class="option">

    <input type="radio" name="q6" value="0" required>

    ไม่มี

</label>


<label class="option">

    <input type="radio" name="q6" value="1">

    เล็กน้อย

</label>


<label class="option">

    <input type="radio" name="q6" value="2">

    ปานกลาง

</label>


<label class="option">

    <input type="radio" name="q6" value="3">

    มาก

</label>


<label class="option">

    <input type="radio" name="q6" value="4">

    มากที่สุด

</label>


</div>



<!-- Question 7 -->

<div class="question-card">


<div class="question-number">

    คำถามที่ 7

</div>


<div class="question">

    คุณแม่รู้สึกว่าน้ำนมไหลน้อยหรือไม่เพียงพอต่อความต้องการของบุตรหรือไม่?

</div>


<label class="option">

    <input type="radio" name="q7" value="0" required>

    ไม่เลย

</label>


<label class="option">

    <input type="radio" name="q7" value="1">

    เล็กน้อย

</label>


<label class="option">

    <input type="radio" name="q7" value="2">

    ปานกลาง

</label>


<label class="option">

    <input type="radio" name="q7" value="3">

    มาก

</label>


<label class="option">

    <input type="radio" name="q7" value="4">

    มากที่สุด

</label>


</div>



<!-- =========================
     การพักผ่อน
========================= -->

<h2 class="section-title">

    😴 การพักผ่อนและความเหนื่อยล้า

</h2>



<!-- Question 8 -->

<div class="question-card">


<div class="question-number">

    คำถามที่ 8

</div>


<div class="question">

    คุณแม่รู้สึกเหนื่อย อ่อนเพลีย หรือพักผ่อนไม่เพียงพอหรือไม่?

</div>


<label class="option">

    <input type="radio" name="q8" value="0" required>

    ไม่เลย

</label>


<label class="option">

    <input type="radio" name="q8" value="1">

    เล็กน้อย

</label>


<label class="option">

    <input type="radio" name="q8" value="2">

    ปานกลาง

</label>


<label class="option">

    <input type="radio" name="q8" value="3">

    มาก

</label>


<label class="option">

    <input type="radio" name="q8" value="4">

    มากที่สุด

</label>


</div>



<!-- =========================
     อารมณ์
========================= -->

<h2 class="section-title">

    💗 อารมณ์และความรู้สึก

</h2>



<!-- Question 9 -->

<div class="question-card">


<div class="question-number">

    คำถามที่ 9

</div>


<div class="question">

    คุณแม่รู้สึกเศร้า หดหู่ กังวล หรือเครียดมากกว่าปกติหรือไม่?

</div>


<label class="option">

    <input type="radio" name="q9" value="0" required>

    ไม่เลย

</label>


<label class="option">

    <input type="radio" name="q9" value="1">

    เล็กน้อย

</label>


<label class="option">

    <input type="radio" name="q9" value="2">

    ปานกลาง

</label>


<label class="option">

    <input type="radio" name="q9" value="3">

    มาก

</label>


<label class="option">

    <input type="radio" name="q9" value="4">

    มากที่สุด

</label>


</div>



<!-- Question 10 -->

<div class="question-card">


<div class="question-number">

    คำถามที่ 10

</div>


<div class="question">

    คุณแม่รู้สึกว่าสามารถดูแลตนเองและบุตรได้ดีเพียงใด?

</div>


<label class="option">

    <input type="radio" name="q10" value="0" required>

    ดีมาก

</label>


<label class="option">

    <input type="radio" name="q10" value="1">

    ดี

</label>


<label class="option">

    <input type="radio" name="q10" value="2">

    ปานกลาง

</label>


<label class="option">

    <input type="radio" name="q10" value="3">

    ค่อนข้างยาก

</label>


<label class="option">

    <input type="radio" name="q10" value="4">

    ยากมาก

</label>


</div>



<!-- =========================
     Submit
========================= -->

<button
    type="submit"
    class="submit-button"
>

    ส่งแบบประเมิน 💗

</button>


</form>

<?php endif; ?>



<div class="note">

    🌸 กรุณาตอบตามอาการที่เกิดขึ้นจริง เพื่อให้ระบบสามารถประเมินและติดตามสุขภาพของคุณแม่ได้อย่างเหมาะสม

    <br>

    🔒 ข้อมูลการประเมินของคุณแม่จะถูกเก็บเป็นความลับ

</div>


</div>


</body>

</html>