<?php
session_start();

include "db.php";

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "nurse"
) {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fullname = $_POST["fullname"];
    $id_card = $_POST["id_card"];
    $birth_date = $_POST["birth_date"];
    $phone = $_POST["phone"];
    $delivery_date = $_POST["delivery_date"];
    $delivery_type = $_POST["delivery_type"];

    /* =========================
   เพิ่มข้อมูลผู้ใช้
========================= */

$user_sql = "INSERT INTO users
            (fullname, id_card, role)
            VALUES (?, ?, 'mother')";

$user_stmt = $conn->prepare($user_sql);

$user_stmt->bind_param(
    "ss",
    $fullname,
    $id_card
);

$user_stmt->execute();

$user_id = $conn->insert_id;
    
    $sql = "INSERT INTO mothers 
        (user_id, fullname, id_card, birth_date, phone, delivery_date, delivery_type)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

  $stmt->bind_param(
    "issssss",
    $user_id,
    $fullname,
    $id_card,
    $birth_date,
    $phone,
    $delivery_date,
    $delivery_type
);
    if ($stmt->execute()) {

        header("Location: nurse_dashboard.php");
        exit;

    } else {

        $message = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";

    }

}
?>

<!DOCTYPE html>
<html lang="th">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>เพิ่มข้อมูลแม่ | MomCare</title>

<style>

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #fff7fa;
    color: #555;
}

.container {
    width: 90%;
    max-width: 800px;
    margin: 40px auto;
}

.logo {
    font-size: 24px;
    font-weight: bold;
    color: #d982a1;
    margin-bottom: 20px;
}

.back {
    text-decoration: none;
    color: #d982a1;
}

.card {
    background: white;
    padding: 35px;
    margin-top: 25px;
    border-radius: 20px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
}

h1 {
    color: #d982a1;
    margin-bottom: 10px;
}

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
}

input,
select {
    width: 100%;
    padding: 12px;
    box-sizing: border-box;
    border: 1px solid #ddd;
    border-radius: 10px;
    font-size: 16px;
}

button {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 12px;
    background: #e89bb5;
    color: white;
    font-size: 17px;
    font-weight: bold;
    cursor: pointer;
}

button:hover {
    background: #d982a1;
}

.error {
    color: red;
    margin-bottom: 15px;
}

</style>

</head>

<body>

<div class="container">

    <div class="logo">
        🌸 MomCare
    </div>

    <a href="nurse_dashboard.php" class="back">
        ← กลับหน้าคุณแม่ทั้งหมด
    </a>

    <div class="card">

        <h1>👩🏻‍🍼 เพิ่มข้อมูลคุณแม่</h1>

        <p>
            กรุณากรอกข้อมูลคุณแม่หลังคลอด
        </p>

        <?php if ($message != ""): ?>

            <div class="error">
                <?php echo $message; ?>
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
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    🪪 เลขบัตรประชาชน
                </label>

                <input
                    type="text"
                    name="id_card"
                    maxlength="13"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    🎂 วันเดือนปีเกิด
                </label>

                <input
                    type="date"
                    name="birth_date"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    📱 เบอร์โทรศัพท์
                </label>

                <input
                    type="text"
                    name="phone"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    📅 วันที่คลอด
                </label>

                <input
                    type="date"
                    name="delivery_date"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    🏥 วิธีการคลอด
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

                </select>

            </div>


            <button type="submit">
                💾 บันทึกข้อมูลแม่
            </button>


        </form>

    </div>

</div>

</body>

</html>