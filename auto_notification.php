<?php

// แจ้งเตือนอัตโนมัติหลังคลอด

$sql = "
SELECT mother_id, fullname, delivery_date
FROM mothers
";

$result = $conn->query($sql);


while($row = $result->fetch_assoc()){


    $delivery_date = new DateTime($row["delivery_date"]);
    $today = new DateTime(date("Y-m-d"));


    // ถ้ายังไม่ถึงวันคลอด ข้าม
    if($delivery_date > $today){
        continue;
    }


    $days = $delivery_date->diff($today)->days;



    $title = "";
    $message = "";



    switch($days){

        case 7:

            $title = "ครบ 7 วันหลังคลอด 💗";

            $message = 
            "ถึงเวลาประเมินสุขภาพหลังคลอด 7 วันแล้วค่ะ กรุณาทำแบบประเมิน";

            break;


        case 14:

            $title = "ครบ 14 วันหลังคลอด 💗";

            $message =
            "ถึงเวลาประเมินสุขภาพหลังคลอด 14 วันแล้วค่ะ กรุณาติดตามสุขภาพ";

            break;


        case 90:

            $title = "ครบ 3 เดือนหลังคลอด 💗";

            $message =
            "ถึงเวลาติดตามสุขภาพคุณแม่หลังคลอด 3 เดือนแล้วค่ะ";

            break;


        case 180:

            $title = "ครบ 6 เดือนหลังคลอด 💗";

            $message =
            "ถึงเวลาติดตามสุขภาพคุณแม่หลังคลอด 6 เดือนแล้วค่ะ";

            break;

    }



    if($title != ""){


        // เช็กแจ้งเตือนซ้ำ

        $check = "
        SELECT notification_id
        FROM notifications
        WHERE mother_id = ?
        AND title = ?
        ";


        $stmt = $conn->prepare($check);


        $stmt->bind_param(
            "is",
            $row["mother_id"],
            $title
        );


        $stmt->execute();


        $exist = $stmt->get_result();



        if($exist->num_rows == 0){


            $insert = "
            INSERT INTO notifications
            (
                mother_id,
                title,
                message,
                created_at
            )
            VALUES
            (
                ?,
                ?,
                ?,
                NOW()
            )
            ";


            $stmt2 = $conn->prepare($insert);


            $stmt2->bind_param(
                "iss",
                $row["mother_id"],
                $title,
                $message
            );


            $stmt2->execute();


        }


    }


}


?>