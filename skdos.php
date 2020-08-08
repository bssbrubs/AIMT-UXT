<?php
$host = "localhost"; 
			$user = "root"; 
			$password = ""; 
			$dbname = "dashboard"; 
			$id = '';
			$con = mysqli_connect($host, $user, $password,$dbname);

				$sql = "INSERT INTO testetable (url, sample) VALUES ('sdsd', 'asda')";
                $kolp = mysqli_query($con, $sql)
                
                ?>