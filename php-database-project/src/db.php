<?php

 $server = "sql100.infinityfree.com";
 $username = "if0_40163491";
 $password = "VrxifniUgw3t";
 $dbname = "if0_40163491_biowell";

 $conn = mysqli_connect($server, $username, $password, $dbname);

 if(! $conn){
    die("Connection failed: " . mysqli_connect_error());
 }
?>