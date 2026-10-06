<?php

session_start();

require_once "db.php";


if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] !== "nurse"
) {

    header("Location: login.php");
    exit();

}


/* ดึงแม่ที่ควรติดตาม */

$sql = "

SELECT

m.mother_id,
m.fullname,
m.id_card,
m.birth_date,
m.phone,
m.delivery_date,
m.delivery_type,

ar.score,
ar.result,
ar.created_at

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

ORDER BY ar.created_at DESC

";


$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="th">

<head>

<meta charset="UTF-8">

<title>คุณแม่ที่ต้องติดตาม | MomCare</title>

<head>

<meta charset="UTF-8">

<title>คุณแม่ที่ต้องติดตาม | MomCare</title>


<style>

.back-button {

    display: inline-block;

    margin-bottom: 20px;

    padding: 8px 18px;

    background: #f8d6e3;

    color: #df7895;

    text-decoration: none;

    border-radius: 20px;

    font-size: 15px;

    font-weight: bold;

    transition: 0.3s;

}


.back-button:hover {

    background: #f2bfd3;

    transform: translateY(-2px);

}

.view-button{

    display:inline-block;

    margin-top:20px;

    padding:12px 25px;

    background:#df7895;

    color:white;

    text-decoration:none;

    border-radius:20px;

    font-weight:bold;

    transition:0.3s;

}


.view-button:hover{

    background:#c96180;

    transform:translateY(-3px);

}

    body{
    margin:0;
    font-family:Arial,sans-serif;
    background:#fff5f8;
    color:#555;
}


.container{
    width:90%;
    max-width:1100px;
    margin:40px auto;
}


h1{
    color:#df7895;
}


.mother-card{

    background:white;

    padding:25px;

    border-radius:25px;

    margin-bottom:20px;

    box-shadow:
    0 8px 25px rgba(220,130,160,0.1);

}


.mother-card h3{

    color:#bd6682;

}


.mother-card p{

    color:#777;

}


a{

    color:#df7895;

}

body{
    background:#fff5f8;
    font-family:Arial,sans-serif;
    color:#555;
}

h1{
    color:#df7895;
}

div{
    margin:10px;
}


</style>


</head>

</head>


<body>

<div class="container">


<h1>⚠️ คุณแม่ที่ต้องติดตาม</h1>

<a href="nurse_dashboard.php" class="back-button">
    ← กลับหน้าหลักพยาบาล
</a>

<hr>


<?php if($result->num_rows > 0): ?>


<?php while($mother = $result->fetch_assoc()): ?>


<div class="mother-card">

<h3>
👩🏻‍🍼 
<?php echo $mother["fullname"]; ?>
</h3>


<p>
📞 โทร:
<?php echo $mother["phone"]; ?>
</p>


<p>
คะแนน:
<?php echo $mother["score"]; ?>/50
</p>


<p>
ผล:
<?php echo $mother["result"]; ?>
</p>


<a 
href="nurse_mother_detail.php?id=<?php echo $mother["mother_id"]; ?>"
class="view-button">

    ดูข้อมูลแม่ →

</a>

<hr>

</div>


<?php endwhile; ?>


<?php else: ?>

<p>
ยังไม่มีคุณแม่ที่ต้องติดตาม
</p>

<?php endif; ?>

</div>

</body>

</html>

