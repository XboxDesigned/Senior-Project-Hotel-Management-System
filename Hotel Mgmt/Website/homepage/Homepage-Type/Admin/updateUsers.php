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
        //vardump($username);
        // Redirect back after success
        header("Location: homepage.php");
        exit();
    }
}
?>

<style>
/* Ensure the form does not get covered by the menu */
.form-container {
    width: 50%; /* Adjust as needed */
    max-width: 500px; /* Prevents form from being too wide */
    margin: 50px auto 50px 230px; /* Pushes the form away from the menu */
    padding: 20px;
    background-color: #f8f8f8;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

/* Center the form on smaller screens */
@media (max-width: 900px) {
    .form-container {
        margin-left: auto;
        margin-right: auto;
        width: 80%;
    }
}

h3 {
    text-align: center;
}

table {
    width: 100%;
}

td {
    padding: 10px;
}

input, select {
    width: 95%; 
    padding: 10px;
    font-size: 18px; 
    border: 1px solid #ccc;
    border-radius: 5px;
}

input[name="username"], input[name="password"] {
   width: 98%;
}

/* Style only buttons inside the update form */
.form-container input[type="submit"], 
.form-container button {
    width: 100%;
    padding: 10px;
    font-size: 16px;
    margin-top: 10px;
    cursor: pointer;
    border: none;
    background-color: #007bff;
    color: white;
    border-radius: 5px;
}

.form-container button {
    background-color: #dc3545;
}

.form-container input[type="submit"]:hover {
    background-color: #0056b3;
}

.form-container button:hover {
    background-color: #c82333;
}

</style>

<!-- Only show the update form if user data is available -->
<?php if ($user): ?>
    <div class="form-container">
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
                            <option value="front_desk" <?php echo ($user["role"] === "Front Desk") ? "selected" : ""; ?>>Front Desk</option>
                            <option value="maintenance" <?php echo ($user["role"] === "Maintenance") ? "selected" : ""; ?>>Maintenance</option>
                            <option value="housekeeper" <?php echo ($user["role"] === "Housekeeper") ? "selected" : ""; ?>>Housekeeper</option>
                        </select>
                    </td>
                </tr>
            </table>
            <br>
            <input type="submit" name="update_user" value="Update User">
            
        </form>
        <button type="button" onclick="window.location.href='Homepage.php';">CANCEL</button>
    </div>
<?php else: ?>
    <p style="color:red;">User not found.</p>
<?php endif; ?>