<?php
    //localhost
    $servername = "";
    $username = "root";
    $password = "";
    $dbname = "mms_gestao";
    //link db
    $servernamex = "";
    $usernamex = "root";
    $passwordx = "";
    $dbnamex = "";

    
    // Create connection Local
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Create connection Link
    $connx = new mysqli($servernamex, $usernamex, $passwordx, $dbnamex);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    // Check connection Link
    if ($connx->connect_error) {
        die("Connection failed: " . $connx->connect_error);
    }
    
    $sql = "SELECT id, name ,code FROM airport";
    $sql2 = "SELECT * FROM airport";
    //producao    
    $result = $connx->query($sql);
    //homologacao
    $result2 = $conn->query($sql2); 

    
    if ($result->num_rows > 0) {

        //producao
        while($row = $result->fetch_assoc()) {  
            //homologacao                              
            while($row2 = $result2->fetch_assoc()){         
                $nameHomol = $row2['name'];
                $codeHomol = $row2['code'];
                //$codeProd = $row['code'];          
                
                //updateProducao
                $upd = " UPDATE airport SET  name = '$nameHomol' WHERE code = '$codeHomol' ";
                $result3 = $connx->query($upd);
               
                if($result3 === TRUE){
                    echo "Record updated successfully";                    
                }else{
                    echo "Error updating record: " . $connx->error;
                }                                        
            }
        }               
    } else {
        echo "0 results";
    }
    $connx->close();
    $conn->close();
?>