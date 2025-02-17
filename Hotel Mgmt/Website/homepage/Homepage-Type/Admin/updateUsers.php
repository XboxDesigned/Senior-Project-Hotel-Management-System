<?php
require_once('../../Website/inc/db_connect.php');

// Start session if not already started
$status = session_status();
if ($status == PHP_SESSION_NONE) {
    session_start();
}

// Get the user ID from the URL (GET request)
$u_id = filter_input(INPUT_GET, 'update_id', FILTER_VALIDATE_INT);
$user = null; // Initialize user variable

// Fetch user data only if update_id is provided
if ($u_id) {
    $queryUsers = "
    SELECT
        u_id,
        username,
        password,
        role
    FROM
        users
    WHERE u_id = :u_id
    ";
    
    $statementUsers = $db->prepare($queryUsers);
    $statementUsers->bindValue(':u_id', $u_id);
    $statementUsers->execute();
    $user = $statementUsers->fetch();
    $statementUsers->closeCursor();
}

// Process form submission only when "Update User" is clicked
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_user'])) {
    $u_id = filter_input(INPUT_POST, 'uid', FILTER_VALIDATE_INT);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password');
    $role = filter_input(INPUT_POST, 'role');
    $error = "";

    // Validate inputs
    if (!$u_id) {
        $error .= "Invalid User ID <br>";
    }
    if (!$username) {
        $error .= "Invalid Username <br>";
    }
    if (!$password) {
        $error .= "Password can't be blank<br>";
    }
    

    // Display errors if validation fails
    if ($error !== "") {
        echo "<div style='color:red;'>$error</div>";
    } else {
        // Hash the password before updating
        $hashpass = password_hash($password, PASSWORD_DEFAULT);

        // Update user details in the database
        $queryUpdateUsers = 'UPDATE users
                            SET username = :username, password = :hashpass, role = :role
                            WHERE u_id = :u_id';

        $statementUU = $db->prepare($queryUpdateUsers);
        $statementUU->bindValue(':u_id', $u_id);
        $statementUU->bindValue(':username', $username);
        $statementUU->bindValue(':hashpass', $hashpass);
        $statementUU->bindValue(':role', $role);
        $statementUU->execute();
        $statementUU->closeCursor();

        // Redirect back after success
		header("Location: Homepage.php");
		exit();

    }
}
?>

<!-- Only show the update form if user data is available -->
<?php if ($user): ?>
    <h3>Update User ID: <?php echo htmlspecialchars($u_id); ?></h3>
    <form action="" method="post" id="update_users_form">
        <input type="hidden" name="uid" value="<?php echo htmlspecialchars($u_id); ?>">
        <table>
            <tr>
                <th>Username:</th>
                <td><input type="text" name="username" value="<?php echo htmlspecialchars($user["username"] ?? ''); ?>" required></td>
            </tr>
            <tr>
                <th>Password:</th>
                <td><input type="password" name="password" placeholder="Type New Password Here" required></td>
            </tr>
            <tr>
                <th>Role:</th>
                <td>
                    <select name="role" required>
                        <option value="admin" <?php echo ($user["role"] === "Admin") ? "selected" : ""; ?>>Admin</option>
						<option value="front_desk" <?php echo ($user["role"] === "Admin") ? "selected" : ""; ?>>Front Desk</option>
						<option value="maintenance" <?php echo ($user["role"] === "Maintenance") ? "selected" : ""; ?>>Maintenance/Housekeeper</option>
                    </select>
                </td>
            </tr>
        </table>
        <br>
        <input type="submit" name="update_user" value="Update User">
		<br>
		<button type="button" onclick="window.location.href='Homepage.php';">CANCEL</button>
		<br><br>

    </form>
<?php else: ?>
    <p style="color:red;">User not found.</p>
<?php endif; ?>
