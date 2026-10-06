<?php

session_start();
require_once "db.php";


if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit();

}


if (!isset($_GET["id"])) {

    die("ไม่พบผลการประเมิน");

}


$assessment_id = intval($_GET["id"]);



$sql = "
SELECT 
    ar.score,
    ar.result,
    ar.created_at,
    m.fullname
FROM assessment_results ar
JOIN mothers m
ON ar.mother_id = m.mother_id
WHERE ar.assessment_id = ?
";



$stmt = $conn->prepare($sql);


$stmt->bind_param(
    "i",
    $assessment_id
);


$stmt->execute();


$data = $stmt->get_result()->fetch_assoc();



if (!$data) {

    die("ไม่พบผลการประเมิน");

}



$fullname = $data["fullname"];

$score = $data["score"];

$result = $data["result"];




if ($result == "ปกติ") {


    $icon = "🟢";

    $message = "โดยรวมยังไม่พบสัญญาณความเสี่ยงที่น่ากังวลค่ะ 🌸";


} elseif ($result == "ควรติดตาม") {


    $icon = "🟡";

    $message = "แนะนำให้คุณแม่ติดตามอาการอย่างต่อเนื่อง และประเมินอาการตามกำหนดค่ะ 💗";


} else {


    $icon = "🔴";

    $message = "ผลการประเมินพบว่าคุณแม่ควรได้รับการติดตามจากพยาบาลค่ะ 👩🏻‍⚕️";


}


?>


<!DOCTYPE html>

<html lang="th">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>ผลการประเมิน | MomCare</title>


<style>

* {

    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial,sans-serif;

}


body {

    min-height:100vh;

    background:linear-gradient(
        135deg,
        #fff7fa,
        #fff0f5,
        #fdf5ff
    );

    display:flex;

    justify-content:center;

    align-items:center;

}



.result-container {

    width:90%;

    max-width:600px;

    background:white;

    padding:45px;

    border-radius:30px;

    text-align:center;

    box-shadow:0 10px 35px rgba(220,130,160,0.15);

}



.logo {

    color:#df7895;

    font-size:25px;

    font-weight:bold;

    margin-bottom:25px;

}



.result-icon {

    font-size:75px;

    margin-bottom:15px;

}



h1 {

    color:#df7895;

    margin-bottom:15px;

}



.score {

    font-size:20px;

    color:#777;

    margin:15px 0;

}



.score strong {

    color:#df7895;

    font-size:30px;

}



.message {

    color:#888;

    line-height:1.8;

    margin-bottom:30px;

}



.button {

    display:inline-block;

    background:#df7895;

    color:white;

    padding:13px 25px;

    border-radius:25px;

    text-decoration:none;

    margin:5px;

}



.button.secondary {

    background:#f8dce7;

    color:#bd6682;

}


</style>

</head>


<body>


<div class="result-container">


<div class="logo">

🌸 MomCare

</div>



<div class="result-icon">

<?php echo $icon; ?>

</div>



<h1>

ผลการประเมิน: <?php echo $result; ?>

</h1>



<div class="score">

คะแนนรวมของคุณแม่

<br>

<strong>

<?php echo $score; ?>

</strong>

 / 40 คะแนน

</div>



<div class="message">

<?php echo $message; ?>

</div>



<a href="mother_dashboard.php" class="button">

🏠 กลับหน้าหลัก

</a>



</div>


</body>

</html>