<?php
    $servername = "";
    $username = "root";
    $password = "";
    $dbname = "";

     // Create connection Local
     $conn = new mysqli($servername, $username, $password, $dbname);

     // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    // sql to delete a record   
    /*$sql = "DELETE FROM airport WHERE id = '66' ";
    $result = $conn->query($sql);
    if($result === TRUE){
        echo "Record deleted successfully";                    
    }else{
        echo "Error deleted record: " . $conn->error;
    }*/

    $name = '';
    $code = 'MXP';
    $sql1 = " UPDATE airport SET code = '$code', name = 'Aeroporto de Milao' WHERE  id = '92' ";
    $result1 = $conn->query($sql1);
   

   /* $sql2 = "DELETE FROM airport WHERE id = '159' ";
    $result2 = $conn->query($sql2);*/
    

    $code = 'OMA';
    $sql3 = " UPDATE airport SET code = '$code', name = 'Aeroporto de Omaha' WHERE id = '338' ";
    $result3 = $conn->query($sql3);
    

    $name = 'Aeroporto de Manaus';
    $code = 'MAO';
    $sql4 = " UPDATE airport SET code = '$code', name = '$name' WHERE id = '354' ";
    $result4 = $conn->query($sql4);
    
    $code = 'BSB';
    $sql5 = " UPDATE airport SET code = 'BSB', name = 'Aeroporto de Brasilia' WHERE id = '355' ";
    $result5 = $conn->query($sql5);    

    $name = 'Aeroporto de McAllen';
    $code = 'MFE';
    $sql6 = " UPDATE airport SET code = '$code', name = '$name' WHERE id = '376' ";
    $result6 = $conn->query($sql6);

    $name = 'Aeroporto de Porto Alegre';
    $code = 'POA';
    $sql7 = " UPDATE airport SET code = '$code', name = '$name' WHERE id = '449' ";
    $result7 = $conn->query($sql7);    

    $name = 'Aeroporto de Congonhas';
    $code = 'CGH';
    $sql8 = " UPDATE airport SET code = '$code', name = '$name' WHERE id = '450' ";
    $result8 = $conn->query($sql8);    

    $name = 'Aeroporto de Congonhas';
    $code = 'CGH';
    $sql9 = " UPDATE airport SET code = '$code', name = '$name' WHERE id = '628' ";
    $result9 = $conn->query($sql9);    

    $name = 'Aeroporto de Congonhas';
    $code = 'CGH';
    $sql10 = " UPDATE airport SET code = '$code', name = '$name' WHERE id = '629' ";
    $result10 = $conn->query($sql10);    

    $name = 'Aeroporto de Santarem';
    $code = 'STM';
    $sql11 = " UPDATE airport SET code = '$code', name = '$name' WHERE id = '635' ";
    $result11 = $conn->query($sql11);    

    $name = 'Aeroporto de Santiago do Chile';
    $code = 'SCL';
    $sql12 = " UPDATE airport SET code = '$code', name = '$name' WHERE id = '669' "; 
    $result12 = $conn->query($sql12);    

    $name = 'Aeroporto de Manaus';
    $code = 'MAO';
    $sql13 = " UPDATE airport SET code = '$code', name = '$name' WHERE id = '674' ";
    $result13 = $conn->query($sql13);    

    $name = 'Aeroporto de Buenos Aires/Ezeiza';
    $code = 'EZE';
    $sql14 = " UPDATE airport SET code = '$code', name = '$name' WHERE id = '675' ";
    $result14 = $conn->query($sql14);      

    $name = 'Aeroporto de Manaus';
    $code = 'MAO';
    $sql15 = " UPDATE airport SET code = '$code', name = '$name' WHERE id = '676' ";
    $result15 = $conn->query($sql15);

    $name = 'Aeroporto de Buenos Aires/Ezeiza';
    $code = 'EZE';
    $sql16 = " UPDATE airport SET code = '$code', name = '$name' WHERE code = '677' ";
    $result16 = $conn->query($sql16);


    /*Complement */
    $sql17 = " UPDATE airport SET name = 'Aeroporto de Copiapo' WHERE  code = 'CPO' ";
    $result17 = $conn->query($sql17);

    $sql18 = " UPDATE airport SET  name = 'Aeroporto de Leticia' WHERE  code = 'LET' ";
    $result18 = $conn->query($sql18);

    $sql19 = " UPDATE airport SET  code ='SCL', name = 'Aeroporto de Santiago do Chile' WHERE  id = '668' ";
    $result19 = $conn->query($sql19);

    $sql20 = " UPDATE airport SET  name = 'Aeroporto de Pensacola' WHERE  code = 'PNS' ";
    $result20 = $conn->query($sql20);

    $sql21 = " UPDATE airport SET  name = 'Aeroporto de Neuquen' WHERE  code = 'NQN' ";
    $result21 = $conn->query($sql21);

    $sql22 = " UPDATE airport SET code ='STM', name = 'Aeroporto de Santarem' WHERE  id = '687' ";
    $result22 = $conn->query($sql22);

    $sql23 = " UPDATE airport SET  name = 'Aeroporto de Daca' WHERE  code = 'DAC' ";
    $result23 = $conn->query($sql23);

    $sql24 = " UPDATE airport SET  name = 'Aeroporto de Washington' WHERE  code = 'BWI' ";
    $result24 = $conn->query($sql24);

    $sql25 = " UPDATE airport SET  name = 'Aeroporto Internacional Jose Joaquin de Olmedo' WHERE  code = 'GYE' ";
    $result25 = $conn->query($sql25);     

    $conn->close();
       
?>