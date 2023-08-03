<?php    
    $servername = "";
    $username = "root";
    $password = "";
    $dbname = "";

     // Create connection Local
     $conn = mysqli_connect($servername, $username, $password, $dbname);

     // Check connection
    if (mysqli_connect_errno()) {
        die("Connection failed: " . $conn->connect_error);
    } 

    $del2 = " DELETE FROM airport  WHERE  code = 'EXCLUIR' ";
    if (mysqli_query($conn, $del2)) {
        echo "Record deleted X successfully";
    }else {
        echo "Error deleted record: " . mysqli_error($conn);
    }

    $sql = "SELECT  a.code,  a.id FROM airport a GROUP BY a.code ORDER BY a.id";  
    
    if ($result = mysqli_query($conn,$sql)) {
        
        while($row = mysqli_fetch_row($result)) {
            $id = (int)$row[1];
            $code = $row[0];
            
            printf ("%s (%s)\n",$id, $code);
            $upd1 = " UPDATE sale s INNER JOIN airport a ON a.id = s.airport_from SET s.airport_from = '$id' WHERE a.code = '$code' ";
            
            if (mysqli_query($conn, $upd1)) {
                echo "Record updated ONe successfully";
            } else {
                echo "Error updating record: " . mysqli_error($conn);
            }
            
            $upd2 = " UPDATE sale s INNER JOIN airport a ON a.id = s.airport_to SET s.airport_to = '$id' WHERE a.code = '$code' ";
           
            if (mysqli_query($conn, $upd2)) {
                echo "Record updated Two successfully";
            } else {
                echo "Error updating record: " . mysqli_error($conn);
            }

            $del1 = " DELETE FROM airport  WHERE code = '$code' AND id != '$id' ";

            if (mysqli_query($conn, $del1)) {
                echo "Record deleted  successfully";
            }else {
                echo "Error deleted record: " . mysqli_error($conn);
            }                   
        }
        

        /*$upd3 = " UPDATE airport SET code = 'SCL' WHERE a.code = '' ";
           
        if (mysqli_query($conn, $upd2)) {
            echo "Record updated Two successfully";
        } else {
            echo "Error updating record: " . mysqli_error($conn);
        }*/


    }
    mysqli_close($conn);    
?>