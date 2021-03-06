<?php

header('Content-Type: application/json');

$conn = mysqli_connect("localhost","root","","saveme");

$task = $_GET["task"];

if($task == "login"){

	$username = $_GET["username"];
	$password = $_GET["password"];
	
	$status = array(
		"message"	=>	"",
		"hash"		=>	""
	);
	
	if(!empty($username) && !empty($password)){
		
		//filter values
		$username = mysqli_real_escape_string($conn,$username);
		$password = md5($password);
		
		//match with DB
		$query = mysqli_query($conn,"SELECT* FROM users WHERE username='$username'");
		$rows = mysqli_num_rows($query);
		
		if($rows == 1){
			
			$data = mysqli_fetch_array($query);
			
			if($data["password"] == $password){
				
				if($data["activated"] == 1){
					
					//Update security hash
					$hash = md5(time().$username.$password);
					
					mysqli_query($conn,"UPDATE users SET hash='$hash' WHERE username='$username';");
					
					$status["message"] = "success";
					$status["hash"] = $hash;
					
					
				} else {
					$status["message"] = "Account not activated!";	
				}
				
			} else {
				$status["message"] = "Incorrect Password!";	
			}
			
		} else {
			$status["message"] = "User doesn't exist!";	
		}
		
	} else {
		$status["message"] = "Please enter your username and password!";	
	}
	
} else if($task == "register"){
	
	$status = array(
		"message"	=>	""
	);
	
	$fname = $_GET["fname"];
	$email = $_GET["username"];
	$password = $_GET["password"];
	$nic = $_GET["nic"];
	
	if(!empty($fname) && !empty($email) && !empty($password) && !empty($nic)){
		
		$fname = mysqli_real_escape_string($conn,$fname);
		$email = mysqli_real_escape_string($conn,$email);
		$password = md5($password);
		$nic = mysqli_real_escape_string($conn,$nic);
		$savehash = md5($email."saveme".time());
		
		//match with DB
		$query = mysqli_query($conn,"SELECT* FROM users WHERE username='$email'");
		$rows = mysqli_num_rows($query);
		
		if($rows == 0){
			
			//registration possible
			mysqli_query($conn,"INSERT INTO `users` (`id`, `fullname`, `username`, `password`, `nic`, `savehash`) VALUES (NULL, '$fname', '$email', '$password', '$nic', '$savehash');");
			$status["message"] = "success";
			
		} else {
			$status["message"] = "User account already exists!";	
		}
		
	} else {
		$status["message"] = "All fields are required!";
	}
} else if($task == "report"){
	$status = array(
		"message"	=>	""
	);
	
	$nic = "";
	$savecard = "";
	$name = "";
	$location = $_GET["location"];
	
	$reporter = $_GET["reporter"];
	$hash = $_GET["hash"];
	
	if($_GET["nic"]){
		$nic = $_GET["nic"];
	}
	if($_GET["name"]){
		$name = $_GET["name"];
	}
	
	if(!empty($reporter) && !empty($hash) && !empty($location)){
		
		//check reporter and hash
		$q = mysqli_query($conn,"SELECT* FROM users WHERE username='$reporter' AND hash='$hash';");
		$rows = mysqli_num_rows($q);
		
		if($rows == 1){
			if($_GET["savecard"]){
				
				//Check for existing savecard user ID
				$savecheck = mysqli_query($conn,"SELECT* FROM users WHERE savehash='$savecard'");
				$saverows = mysqli_num_rows($savecheck);
				if($rows == 0){
					$status["message"] = "Invalid SaveMe Card number!";
				} else {
					$savecard = $_GET["savecard"];	
				}
			}
			
			mysqli_query($conn,"INSERT INTO `reports` (`id`, `reporter`, `saveme`, `nic`, `name`, `location`, `proceed`) VALUES (NULL, '$reporter', '$savecard', '$nic', '$name', '$location', '0');");
			$status["message"] = "success";
			
		} else {
			$status["message"] = "Invalid security token! Please relogin.";	
		}
		
	} else {
		$status["message"] = "Incomplete request!";
	}
	
} else {
	$status["message"] = "Invalid request";
}

echo(json_encode($status));

?>