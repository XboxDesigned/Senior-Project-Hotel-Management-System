<?php
require_once('../../../inc/db_connect.php');
$status = session_status();
if ($status == PHP_SESSION_NONE) {
    session_start();
}

$u_user = filter_input(INPUT_POST, 'username');
$u_pass = filter_input(INPUT_POST, 'password');
$u_role = filter_input(INPUT_POST, 'role');
$error = "";

//validate inputs
if($u_user === "" ){$error = $error. "Invalid Username </br>";
}else {
    // Query to check if the username already exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
    $stmt->execute(['username' => $u_user]);
    $userExists = $stmt->fetchColumn();

    if ($userExists) {
        $error .= "Username already taken </br>";
    }
}if (strpos($u_user, ' ') !== false) {
    $error .= "Username cannot contain spaces </br>";
} 
if($u_pass === ""){$error = $error. "Password can't be blank</br>";}
if($u_role === ""){$error = $error. "Role can't be blank</br>";} 
    echo $error;
	if ($error !== ""){
		include("registerForm.php");
		//var_dump($u_role);
	}

else{
	
	$hashpass = password_hash($u_pass, PASSWORD_DEFAULT);
	
	$queryAddUsers  = '
	INSERT INTO users
		(username, password, role)
	VALUES
		(:u_user, :hashpass, :u_role)';
				 
    $statementUA = $db->prepare($queryAddUsers);
    $statementUA->bindValue(':u_user', $u_user);
    $statementUA->bindValue(':hashpass', $hashpass);
    $statementUA->bindValue(':u_role', $u_role);
    $statementUA->execute();
    $statementUA->closeCursor();
	
    // Display the User List page
	 echo "User Added!";
	include('registerForm.php');
}
?>
