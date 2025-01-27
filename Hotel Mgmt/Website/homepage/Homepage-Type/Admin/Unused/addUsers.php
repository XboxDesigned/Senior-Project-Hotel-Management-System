<!-- WIP PAGE<?php/*
require_once('Website/inc/db_connect.php');
$status = session_status();
if ($status == PHP_SESSION_NONE) {
    session_start();
}

$u_email = filter_input(INPUT_POST, 'user');
$u_pass = filter_input(INPUT_POST, 'pass');
$u_admin = 'N';
$error = "";
//var_dump($u_email);
//var_dump($u_pass);
//var_dump($u_admin);
//validate inputs
if($u_email === "" ){$error = $error. "Invalid Email </br>";}
if($u_pass === ""){$error = $error. "Password can't be blank</br>";}
    echo $error;
	if ($error !== ""){
		include("addCustomersForm.php");
	}

else{
	
	$hashpass = password_hash($u_pass, PASSWORD_DEFAULT);
	
	$queryAddUsers  = '
	INSERT INTO users
		(userEmail, password, isAdmin)
	VALUES
		(:u_email, :hashpass, :u_admin)';
				 
    $statementReg = $db->prepare($queryAddUsers);
    $statementReg->bindValue(':u_email', $u_email);
    $statementReg->bindValue(':hashpass', $hashpass);
    $statementReg->bindValue(':u_admin', $u_admin);
    $statementReg->execute();
    $statementReg->closeCursor();
}
?>

<!DOCTYPE HTML>
<html>
	<head>
		<title>Users</title>
		<link rel="stylesheet" type="text/css" href="edit.css">
	</head>
	<main>
		<body>
			<h1>Register successful!</h1>
			<button><a class="back" href="login.php">Back</a></button> <br><br>
		</body>
	</main>
</html>
-->