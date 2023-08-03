<?php
    //localhost
    $servername = "mmshomologacaodbinstance.c1oy29a2adal.us-east-1.rds.amazonaws.com";
    $username = "root";
    $password = "A!a1123456";
    $dbname = "mms_gestao";

    // Create connection Local
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    /*$sql = "SELECT o.id AS OrderId, f.emission_method AS Emission, f.miles_used AS MilesUsed FROM online_order o INNER JOIN online_flight f ON f.order_id = o.id WHERE f.emission_method = 'Companhia' ";*/
    $sql = "UPDATE online_order o INNER JOIN online_flight f ON f.order_id = o.id SET o.miles_used = f.miles_used WHERE f.emission_method = 'Companhia' ";
    $result = $conn->query($sql); 

    if($result === TRUE){
        echo "Record updated successfully";                    
    }else{
        echo "Error updating record: " . $conn->error;
    } 

    $conn->close();
?>