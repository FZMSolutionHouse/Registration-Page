<?php

$servername="localhost";
$username="root";
$password="";
$db_name="registration_db";



$conn = new mysqli($servername,$username,$password,$db_name);


if($conn->connect_error){
    die("Failed Connection".$conn->connect_error);
}
echo "Conntection Established Successfully";



$conn->close();



?>
