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


/* =========================
   ตรวจสอบ mother_id
========================= */

if (
    !isset($_GET["mother_id"]) ||
    !is_numeric($_GET["mother_id"])
) {

    header("Location: nurse_dashboard.php");

    exit();

}


$mother_id = intval($_GET["mother_id"]);


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
   บันทึกข้อมูลบุตร
========================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
) {

    $baby_name =
        $_POST["baby_name"];

    $birth_date =
        $_POST["birth_date"];

    $gender =
        $_POST["gender"];

    $birth_weight =
        $_POST["birth_weight"];

    $delivery_method =
        $_POST["delivery_method"];


    $insert_sql = "

    INSERT INTO babies

    (
        mother_id,
        baby_name,
        birth_date,
        gender,
        birth_weight,
        delivery_method
    )

    VALUES (?, ?, ?, ?, ?, ?)

    ";


    $insert_stmt =
        $conn->prepare(
            $insert_sql
        );


    $insert_stmt->bind_param(
        "isssds",
        $mother_id,
        $baby_name,
        $birth_date,
        $gender,
        $birth_weight,
        $delivery_method
    );


    if (
        $insert_stmt->execute()
    ) {

        header(
            "Location: nurse_mother_detail.php?id="
            . $mother_id
        );

        exit();

    } else {

        $error =
            "เกิดข้อผิดพลาดในการบันทึกข้อมูล";

    }

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
    เพิ่มข้อมูลบุตร | MomCare
</title>


<style>

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    font-family:
        Arial,
        sans-serif;

    background:
        #fff7fa;

    color:
        #555;

}


.container {

    width: 90%;

    max-width: 800px;

    margin:
        40px auto;

}


.logo {

    font-size:
        24px;

    font-weight:
        bold;

    color:
        #d982a1;

    margin-bottom:
        20px;

}


.back {

    text-decoration:
        none;

    color:
        #d982a1;

}


.card {

    background:
        white;

    padding:
        35px;

    margin-top:
        25px;

    border-radius:
        20px;

    box-shadow:
        0 5px 20px
        rgba(0,0,0,0.08);

}


h1 {

    color:
        #d982a1;

}


.mother-name {

    background:
        #fff0f5;

    padding:
        15px;

    border-radius:
        12px;

    margin-bottom:
        25px;

}


.form-group {

    margin-bottom:
        20px;

}


label {

    display:
        block;

    margin-bottom:
        8px;

    font-weight:
        bold;

}


input,
select {

    width:
        100%;

    padding:
        12px;

    border:
        1px solid #ddd;

    border-radius:
        10px;

    font-size:
        16px;

}


button {

    width:
        100%;

    padding:
        14px;

    border:
        none;

    border-radius:
        12px;

    background:
        #e89bb5;

    color:
        white;

    font-size:
        17px;

    font-weight:
        bold;

    cursor:
        pointer;

}


button:hover {

    background:
        #d982a1;

}


.error {

    color:
        red;

    margin-bottom:
        15px;

}

</style>

</head>


<body>


<div class="container">


    <div class="logo">

        🌸 MomCare

    </div>


    <a
        href="nurse_mother_detail.php?id=<?php echo $mother_id; ?>"
        class="back"
    >

        ← กลับข้อมูลคุณแม่

    </a>


    <div class="card">


        <h1>

            👶 เพิ่มข้อมูลบุตร

        </h1>


        <div class="mother-name">

            👩🏻‍🍼 คุณแม่:

            <strong>

                <?php

                echo htmlspecialchars(
                    $mother["fullname"]
                );

                ?>

            </strong>

        </div>


        <?php if (
            isset($error)
        ): ?>

            <div class="error">

                <?php

                echo $error;

                ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
        >


            <div class="form-group">

                <label>

                    👶 ชื่อบุตร

                </label>

                <input
                    type="text"
                    name="baby_name"
                    required
                >

            </div>


            <div class="form-group">

                <label>

                    🎂 วันเกิด

                </label>

                <input
                    type="date"
                    name="birth_date"
                    required
                >

            </div>


            <div class="form-group">

                <label>

                    ⚧️ เพศ

                </label>

                <select
                    name="gender"
                    required
                >

                    <option value="">

                        -- เลือกเพศ --

                    </option>

                    <option value="ชาย">

                        ชาย

                    </option>

                    <option value="หญิง">

                        หญิง

                    </option>

                </select>

            </div>


            <div class="form-group">

                <label>

                    ⚖️ น้ำหนักแรกเกิด (กิโลกรัม)

                </label>

                <input
                    type="number"
                    name="birth_weight"
                    step="0.01"
                    min="0"
                    required
                >

            </div>


            <div class="form-group">

                <label>

                    🏥 วิธีการคลอด

                </label>

                <select
                    name="delivery_method"
                    required
                >

                    <option value="">

                        -- เลือกวิธีการคลอด --

                    </option>

                    <option value="คลอดธรรมชาติ">

                        คลอดธรรมชาติ

                    </option>

                    <option value="ผ่าคลอด">

                        ผ่าคลอด

                    </option>

                </select>

            </div>


            <button
                type="submit"
            >

                💾 บันทึกข้อมูลบุตร

            </button>


        </form>


    </div>


</div>


</body>

</html>