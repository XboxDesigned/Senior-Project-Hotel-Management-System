<?php
require_once('../../Website/inc/db_connect.php');

// Declare variables for user input to retain values after form submission
$u_user = '';
$u_pass = '';
$u_role = '';
$message = ''; // Variable to store messages

// Check if the form is submitted
if (isset($_POST['register'])) {
    // Get the data from the form
    $u_user = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $u_pass = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    $u_role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);

    // Check if username, password, or role are empty
    if (empty($u_user) || empty($u_pass) || empty($u_role)) {
        $message = "Please enter in user information";
    } else {
        // Check if the username already exists
        $queryCheckUser = 'SELECT COUNT(*) FROM users WHERE username = :u_user';
        $stmtCheck = $db->prepare($queryCheckUser);
        $stmtCheck->bindValue(':u_user', $u_user);
        $stmtCheck->execute();
        $userCount = $stmtCheck->fetchColumn();

        if ($userCount > 0) {
            $message = "Username already exists, please choose another!";
        } else {
            // Proceed with the database insertion if no duplicates
            $hashpass = password_hash($u_pass, PASSWORD_DEFAULT);
            $queryAddUsers = '
            INSERT INTO users (username, password, role)
            VALUES (:u_user, :hashpass, :u_role)';

            // Prepare and execute the query
            $stmt = $db->prepare($queryAddUsers);
            $stmt->bindValue(':u_user', $u_user);
            $stmt->bindValue(':hashpass', $hashpass);
            $stmt->bindValue(':u_role', $u_role);

            if ($stmt->execute()) {
                $message = "User added successfully!";
              
                $u_user = $u_pass = $u_role = ''; // Reset the fields
            } else {
                $message = "Error adding user.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="../inc/homepage_main.css">
</head>
<body>

    <div class="centered-content">
        <h1>User Registration</h1>

        <?php if ($message): ?>
            <div class="message <?php echo (strpos($message, 'Error') !== false || strpos($message, 'choose another') !== false) ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
		
		<br>

        <form method="post" action="">
            <label>Username: 
                <input type="text" name="username" value="<?php echo htmlspecialchars($u_user); ?>" required>
            </label><br>

            <label>Password: 
                <input type="password" name="password" value="<?php echo htmlspecialchars($u_pass); ?>" required>
            </label><br>

            <label>Role: 
                <select name="role" required>
                    <option value="admin" <?php echo ($u_role == 'admin') ? 'selected' : ''; ?>>Admin</option>
                    <option value="front_desk" <?php echo ($u_role == 'front_desk') ? 'selected' : ''; ?>>Front Desk</option>
                    <option value="maintenance" <?php echo ($u_role == 'maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                    <option value="housekeeper" <?php echo ($u_role == 'housekeeper') ? 'selected' : ''; ?>>Housekeeper</option>
                </select>
            </label><br>

            <button type="submit" name="register">Submit</button>
        </form>
    </div>

</body>
</html>
