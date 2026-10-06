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
   เมื่อกดบันทึก
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    /* =========================
       รับข้อมูลคุณแม่
    ========================= */

    $id_card =
        trim($_POST["id_card"]);

    $fullname =
        trim($_POST["fullname"]);

    $birth_date =
        $_POST["birth_date"];

    $phone =
        trim($_POST["phone"]);

    $delivery_date =
        $_POST["delivery_date"];

    $delivery_type =
        trim($_POST["delivery_type"]);


    /* =========================
       รับข้อมูลบุตร
    ========================= */

    $baby_name =
        trim($_POST["baby_name"]);

    $baby_birth_date =
        $_POST["baby_birth_date"];

    $gender =
        trim($_POST["gender"]);

    $birth_weight =
        $_POST["birth_weight"];

    $delivery_method =
        trim($_POST["delivery_method"]);



    /* =========================
       1. สร้างบัญชีคุณแม่ใน users
    ========================= */

    $user_sql = "

        INSERT INTO users

        (
            id_card,
            fullname,
            role
        )

        VALUES (?, ?, 'mother')

    ";


    $user_stmt =
        $conn->prepare($user_sql);


    if (!$user_stmt) {

        die(
            "เกิดข้อผิดพลาดในการเตรียมข้อมูล users: "
            . $conn->error
        );

    }


    $user_stmt->bind_param(

        "ss",

        $id_card,

        $fullname

    );


    if (!$user_stmt->execute()) {

        die(
            "เกิดข้อผิดพลาดในการสร้างบัญชีคุณแม่: "
            . $user_stmt->error
        );

    }


    /* =========================
       2. รับ user_id ที่สร้างใหม่
    ========================= */

    $mother_id_user =
        $conn->insert_id;



    /* =========================
       3. เพิ่มข้อมูลคุณแม่ใน mothers
    ========================= */

    $mother_sql = "

        INSERT INTO mothers

        (
            user_id,
            id_card,
            fullname,
            birth_date,
            phone,
            delivery_date,
            delivery_type
        )

        VALUES (?, ?, ?, ?, ?, ?, ?)

    ";


    $mother_stmt =
        $conn->prepare($mother_sql);


    if (!$mother_stmt) {

        die(
            "เกิดข้อผิดพลาดในการเตรียมข้อมูล mothers: "
            . $conn->error
        );

    }


    $mother_stmt->bind_param(

        "issssss",

        $mother_id_user,

        $id_card,

        $fullname,

        $birth_date,

        $phone,

        $delivery_date,

        $delivery_type

    );


    if (!$mother_stmt->execute()) {

        die(
            "เกิดข้อผิดพลาดในการเพิ่มข้อมูลคุณแม่: "
            . $mother_stmt->error
        );

    }



    /* =========================
       4. ดึง mother_id
    ========================= */

    $mother_id =
        $conn->insert_id;



    /* =========================
       5. เพิ่มข้อมูลบุตร
    ========================= */

    if ($baby_name !== "") {


        $baby_sql = "

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


        $baby_stmt =
            $conn->prepare($baby_sql);


        if (!$baby_stmt) {

            die(
                "เกิดข้อผิดพลาดในการเตรียมข้อมูลบุตร: "
                . $conn->error
            );

        }


        $baby_stmt->bind_param(

            "isssds",

            $mother_id,

            $baby_name,

            $baby_birth_date,

            $gender,

            $birth_weight,

            $delivery_method

        );


        if (!$baby_stmt->execute()) {

            die(
                "เกิดข้อผิดพลาดในการเพิ่มข้อมูลบุตร: "
                . $baby_stmt->error
            );

        }

    }



    /* =========================
       บันทึกสำเร็จ
    ========================= */

    header(
        "Location: nurse_dashboard.php?success=1"
    );

    exit();

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

    เพิ่มข้อมูลคุณแม่ | MomCare

</title>


<style>


* {

    box-sizing:
        border-box;

    margin:
        0;

    padding:
        0;

    font-family:
        Arial, sans-serif;

}


body {

    min-height:
        100vh;

    background:
        linear-gradient(
            135deg,
            #fff7fa,
            #fff0f5,
            #fdf5ff
        );

    color:
        #555;

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

    display:
        flex;

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

    color:
        #df7895;

    font-size:
        25px;

    font-weight:
        bold;

}


.back {

    color:
        #df7895;

    text-decoration:
        none;

    font-weight:
        bold;

}


.back:hover {

    color:
        #bd6682;

}


/* =========================
   Container
========================= */


.container {

    max-width:
        900px;

    margin:
        auto;

    padding:
        40px 20px 60px;

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


.header-icon {

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
            0.10
        );

}


.section-title {

    color:
        #bd6682;

    margin-bottom:
        20px;

    font-size:
        21px;

}


/* =========================
   Form
========================= */


.form-grid {

    display:
        grid;

    grid-template-columns:
        repeat(
            2,
            1fr
        );

    gap:
        18px;

}


.form-group {

    display:
        flex;

    flex-direction:
        column;

}


.form-group.full {

    grid-column:
        1 / -1;

}


label {

    margin-bottom:
        7px;

    color:
        #777;

    font-weight:
        bold;

    font-size:
        14px;

}


input,
select {

    width:
        100%;

    padding:
        13px 15px;

    border:
        1px solid
        #f0cbd7;

    border-radius:
        14px;

    outline:
        none;

    font-size:
        15px;

    color:
        #555;

    background:
        #fffafd;

}


input:focus,
select:focus {

    border-color:
        #df7895;

    box-shadow:
        0 0 0 3px
        rgba(
            223,
            120,
            149,
            0.10
        );

}


/* =========================
   Button
========================= */


.button-area {

    text-align:
        center;

    margin-top:
        10px;

}


.save-button {

    border:
        none;

    background:
        linear-gradient(
            135deg,
            #df7895,
            #e99ab0
        );

    color:
        white;

    padding:
        15px 45px;

    border-radius:
        30px;

    font-size:
        16px;

    font-weight:
        bold;

    cursor:
        pointer;

    box-shadow:
        0 5px 15px
        rgba(
            223,
            120,
            149,
            0.25
        );

}


.save-button:hover {

    transform:
        translateY(-2px);

}


.required {

    color:
        #e05a6f;

}


@media (
    max-width: 700px
) {


    .form-grid {

        grid-template-columns:
            1fr;

    }


    .form-group.full {

        grid-column:
            auto;

    }


    .navbar {

        padding:
            18px 20px;

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


    <div class="header-icon">

        👩🏻‍🍼

    </div>


    <h1>

        เพิ่มข้อมูลคุณแม่

    </h1>


    <p>

        พยาบาลสามารถบันทึกข้อมูลคุณแม่และบุตรเข้าสู่ระบบได้

    </p>


</div>



<form
    method="POST"
>



<!-- =========================
     Mother
========================= -->


<div class="card">


    <h2 class="section-title">

        👩🏻 ข้อมูลคุณแม่

    </h2>


    <div class="form-grid">


        <div class="form-group">


            <label>

                ชื่อ-นามสกุล
                <span class="required">*</span>

            </label>


            <input
                type="text"
                name="fullname"
                required
                placeholder="กรอกชื่อ-นามสกุล"
            >


        </div>



        <div class="form-group">


            <label>

                เลขบัตรประชาชน
                <span class="required">*</span>

            </label>


            <input
                type="text"
                name="id_card"
                maxlength="13"
                minlength="13"
                required
                placeholder="กรอกเลขบัตรประชาชน 13 หลัก"
            >


        </div>



        <div class="form-group">


            <label>

                วันเดือนปีเกิด
                <span class="required">*</span>

            </label>


            <input
                type="date"
                name="birth_date"
                required
            >


        </div>



        <div class="form-group">


            <label>

                เบอร์โทรศัพท์
                <span class="required">*</span>

            </label>


            <input
                type="tel"
                name="phone"
                required
                placeholder="กรอกเบอร์โทรศัพท์"
            >


        </div>



        <div class="form-group">


            <label>

                วันที่คลอด
                <span class="required">*</span>

            </label>


            <input
                type="date"
                name="delivery_date"
                required
            >


        </div>



        <div class="form-group">


            <label>

                วิธีการคลอด
                <span class="required">*</span>

            </label>


            <select
                name="delivery_type"
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


                <option value="ใช้เครื่องมือช่วยคลอด">

                    ใช้เครื่องมือช่วยคลอด

                </option>


            </select>


        </div>


    </div>


</div>



<!-- =========================
     Baby
========================= -->


<div class="card">


    <h2 class="section-title">

        👶 ข้อมูลบุตร

    </h2>


    <div class="form-grid">


        <div class="form-group">


            <label>

                ชื่อบุตร

            </label>


            <input
                type="text"
                name="baby_name"
                placeholder="กรอกชื่อบุตร"
            >


        </div>



        <div class="form-group">


            <label>

                วันเกิดบุตร

            </label>


            <input
                type="date"
                name="baby_birth_date"
            >


        </div>



        <div class="form-group">


            <label>

                เพศ

            </label>


            <select
                name="gender"
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

                น้ำหนักแรกเกิด (กิโลกรัม)

            </label>


            <input
                type="number"
                name="birth_weight"
                step="0.01"
                min="0"
                placeholder="เช่น 3.20"
            >


        </div>



        <div class="form-group full">


            <label>

                วิธีการคลอด

            </label>


            <select
                name="delivery_method"
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


                <option value="ใช้เครื่องมือช่วยคลอด">

                    ใช้เครื่องมือช่วยคลอด

                </option>


            </select>


        </div>


    </div>


</div>



<!-- =========================
     Save
========================= -->


<div class="button-area">


    <button
        type="submit"
        class="save-button"
    >

        💾 บันทึกข้อมูลคุณแม่

    </button>


</div>


</form>


</div>


</body>


</html>