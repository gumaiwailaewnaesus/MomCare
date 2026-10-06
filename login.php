<?php

session_start();

require_once "db.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fullname = trim($_POST["fullname"]);
    $birth_date_input = trim($_POST["birth_date"]);


    // ตรวจสอบวันเกิด ต้องเป็นตัวเลข 8 หลัก
    if (!preg_match('/^\d{8}$/', $birth_date_input)) {

        $error = "กรุณากรอกวันเกิดเป็นตัวเลข 8 หลัก เช่น 13062548";

    } else {

        // แยกวัน เดือน ปี พ.ศ.
        $day = substr($birth_date_input, 0, 2);
        $month = substr($birth_date_input, 2, 2);
        $year_be = substr($birth_date_input, 4, 4);


        // แปลง พ.ศ. เป็น ค.ศ.
        $year_ce = (int)$year_be - 543;


        // แปลงเป็นรูปแบบ DATE ของ MySQL
        $birth_date = $year_ce . "-" . $month . "-" . $day;


        /*
         * ค้นหาจากชื่อ + วันเกิด
         * ตรวจสอบทั้งคุณแม่และพยาบาล
         */

        $sql = "
            SELECT
                users.user_id,
                users.fullname,
                users.role
            FROM users

            LEFT JOIN mothers
                ON users.user_id = mothers.user_id

            LEFT JOIN nurses
                ON users.user_id = nurses.user_id

            WHERE users.fullname = ?

            AND (
                mothers.birth_date = ?
                OR nurses.birth_date = ?
            )
        ";


        $stmt = $conn->prepare($sql);


        if (!$stmt) {

            die("Prepare Error: " . $conn->error);

        }


        $stmt->bind_param(
            "sss",
            $fullname,
            $birth_date,
            $birth_date
        );


        $stmt->execute();


        $result = $stmt->get_result();


        if ($result->num_rows == 1) {

            $user = $result->fetch_assoc();


            // เก็บข้อมูลลง Session

            $_SESSION["user_id"]
                = $user["user_id"];

            $_SESSION["fullname"]
                = $user["fullname"];

            $_SESSION["role"]
                = $user["role"];


            // แยกหน้าเข้าตาม Role

            if ($user["role"] == "mother") {

                header(
                    "Location: mother_dashboard.php"
                );

                exit();

            }


            elseif ($user["role"] == "nurse") {

                header(
                    "Location: nurse_dashboard.php"
                );

                exit();

            }


            else {

                $error =
                    "ไม่พบสิทธิ์การเข้าใช้งานค่ะ";

            }

        }


        else {

            $error =
                "ชื่อหรือวันเกิดไม่ถูกต้องค่ะ";

        }

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

    เข้าสู่ระบบ | MomCare

</title>


<style>


* {

    margin: 0;

    padding: 0;

    box-sizing: border-box;

    font-family: Arial, sans-serif;

}


body {

    min-height: 100vh;

    display: flex;

    justify-content: center;

    align-items: center;

    padding: 20px;

    background:
        linear-gradient(
            135deg,
            #fff7fa,
            #fff0f5,
            #fdf5ff
        );

}


.login-card {

    width: 100%;

    max-width: 450px;

    background: white;

    padding: 45px 40px;

    border-radius: 35px;

    text-align: center;

    box-shadow:
        0 15px 40px
        rgba(
            220,
            130,
            160,
            0.15
        );

}


.logo {

    color: #df7895;

    font-size: 30px;

    font-weight: bold;

    margin-bottom: 20px;

}


.icon {

    width: 90px;

    height: 90px;

    margin: 0 auto 20px;

    border-radius: 50%;

    background: #fff0f5;

    display: flex;

    justify-content: center;

    align-items: center;

    font-size: 48px;

}


h1 {

    color: #bd6682;

    font-size: 25px;

    margin-bottom: 10px;

}


.description {

    color: #999;

    font-size: 14px;

    margin-bottom: 30px;

}


.form-group {

    text-align: left;

    margin-bottom: 20px;

}


.form-group label {

    display: block;

    color: #bd6682;

    font-weight: bold;

    font-size: 14px;

    margin-bottom: 8px;

}


.form-group input {

    width: 100%;

    padding: 14px 18px;

    border:
        1px solid
        #f0d5df;

    border-radius: 18px;

    outline: none;

    font-size: 15px;

    background: #fffafa;

}


.form-group input:focus {

    border-color: #df7895;

    box-shadow:
        0 0 0 4px
        rgba(
            223,
            120,
            149,
            0.10
        );

}


.error {

    background: #fff0f3;

    color: #d96b87;

    padding: 12px;

    border-radius: 15px;

    font-size: 14px;

    margin-bottom: 20px;

}


.login-button {

    width: 100%;

    border: none;

    background:
        linear-gradient(
            135deg,
            #df7895,
            #d66f8d
        );

    color: white;

    padding: 14px;

    border-radius: 25px;

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;

    transition: 0.3s;

}


.login-button:hover {

    transform:
        translateY(-3px);

    box-shadow:
        0 10px 25px
        rgba(
            220,
            120,
            150,
            0.25
        );

}


.footer {

    margin-top: 25px;

    color: #aaa;

    font-size: 12px;

}


</style>


</head>


<body>


<div class="login-card">


    <div class="logo">

        🌸 MomCare

    </div>


    <div class="icon">

        🤱🏻

    </div>


    <h1>

        ยินดีต้อนรับเข้าสู่ MomCare

    </h1>


    <p class="description">

        ระบบติดตามสุขภาพคุณแม่หลังคลอด 💗

    </p>


    <?php if ($error != ""): ?>


        <div class="error">

            ⚠️

            <?php

            echo htmlspecialchars($error);

            ?>

        </div>


    <?php endif; ?>


   <form method="POST">

    <div class="form-group">

        <label>
            👤 ชื่อ-นามสกุล
        </label>

        <input
            type="text"
            name="fullname"
            placeholder="กรอกชื่อ-นามสกุล"
            required
        >

    </div>


    <div class="form-group">

        <label>
            🎂 วันเกิด
        </label>

        <input
            type="text"
            name="birth_date"
            maxlength="8"
            minlength="8"
            placeholder="เช่น 13062548"
            required
        >

    </div>


    <button
        type="submit"
        class="login-button"
    >
        เข้าสู่ระบบ 💗
    </button>

</form>


    <div class="footer">

        🌷 MomCare

        <br>

        ดูแลคุณแม่ในทุกช่วงเวลาหลังคลอด

    </div>


</div>

<script>

function changeLoginForm() {

    const role =
        document.getElementById("role").value;

    const motherField =
        document.getElementById("mother-field");

    const nurseField =
        document.getElementById("nurse-field");


    if (role === "mother") {

        motherField.style.display = "block";
        nurseField.style.display = "none";

    }

    else if (role === "nurse") {

        motherField.style.display = "none";
        nurseField.style.display = "block";

    }

    else {

        motherField.style.display = "none";
        nurseField.style.display = "none";

    }

}

</script>

</body>


</html>