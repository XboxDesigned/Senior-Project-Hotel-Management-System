<?php
require_once('../../Website/inc/db_connect.php');

$status = session_status();
if ($status == PHP_SESSION_NONE) {
    session_start();
}

// Process form submission for updating user
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['select']) && isset($_POST['submit_val'])) {
    $u_id = filter_input(INPUT_POST, 'uid', FILTER_VALIDATE_INT);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password');
    $role = filter_input(INPUT_POST, 'role');
    $error = "";

    // Validate inputs
    if (!$u_id) $error .= "Invalid User ID <br>";
    if (!$username) $error .= "Invalid Username <br>";
    if (!$password) $error .= "Password can't be blank<br>";

    if ($error !== "") {
        $_SESSION['error'] = $error;
    } else {
        $hashpass = password_hash($password, PASSWORD_DEFAULT);

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
        
        $_SESSION['message'] = "User updated successfully!";
    }

}

// Fetch all users for the table
$queryUsers = "SELECT u_id, username, password, role FROM users";
$statementUsers = $db->prepare($queryUsers);
$statementUsers->execute();
$items = $statementUsers->fetchAll();
$statementUsers->closeCursor();
?>

<!DOCTYPE HTML>
<html>
    <head>
        <title>Users</title>
        <style>
            .hidden { display: none; }

        </style>
    </head>
    
    <main>
        <body>
            <div class="query-buttons-container">
                <br><br>
                <button onclick="showAllUsers()" id="query-buttons">All Users</button>
                <br><br>
                <button onclick="showAdmins()" id="query-buttons">Admin</button>
                <br>
                <button onclick="showFrontDesk()" id="query-buttons">Front Desk</button>
                <br>
                <button onclick="showMaintenance()" id="query-buttons">Maintenance</button>
                <br>
                <button onclick="showHousekeeper()" id="query-buttons">Housekeeper</button>
            </div>

            <div class="table-container">
				<table border="1" id="rooms-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item) : ?>
                        <tr data-role="<?php echo htmlspecialchars($item['role']); ?>">
                            <td><?php echo htmlspecialchars($item['u_id']); ?></td>
                            <td><?php echo htmlspecialchars($item['username']); ?></td>
                            <td><?php echo htmlspecialchars($item['role']); ?></td>
                            <td><button class="update-btn" onclick="openModal(<?php echo $item['u_id']; ?>, '<?php echo htmlspecialchars($item['username']); ?>', '<?php echo htmlspecialchars($item['role']); ?>')">Update User</button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

    
            <div id="updateModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <h3>Update User</h3>
                    
					<form action="" method="post" id="update_users_form">
    <input type="hidden" name="uid" id="modalUserId">
    
    <div class="form-group">
        <label for="modalUsername">Username:</label>
        <input type="text" name="username" id="modalUsername" required>
    </div>
    
    <div class="form-group">
        <label for="modalPassword">Password:</label>
        <input type="password" name="password" id="modalPassword" placeholder="Type New Password Here" required>
    </div>
    
    <div class="form-group">
        <label for="modalRole">Role:</label>
        <select name="role" id="modalRole" required>
            <option value="admin">Admin</option>
            <option value="front_desk">Front Desk</option>
            <option value="maintenance">Maintenance</option>
            <option value="housekeeper">Housekeeper</option>
        </select>
    </div>
    
    <div class="form-actions">
        <button class="update-btn" type="submit" name="select">Update User</button>
        <input type="hidden" name="submit_val"> 
        <button class="update-btn" type="button" onclick="closeModal()">Cancel</button>
    </div>
</form>
					
					
                </div>
            </div>

            <script>
                // Filter functions
                function showAllUsers() {
                    const rows = document.querySelectorAll('#rooms-table tbody tr');
                    rows.forEach(row => row.classList.remove('hidden'));
                }
                
                function showAdmins() {
                    filterUsersByRole('admin');
                }
                
                function showFrontDesk() {
                    filterUsersByRole('front_desk');
                }
                
                function showMaintenance() {
                    filterUsersByRole('maintenance');
                }
                
                function showHousekeeper() {
                    filterUsersByRole('housekeeper');
                }
                
                function filterUsersByRole(role) {
                    const rows = document.querySelectorAll('#rooms-table tbody tr');
                    rows.forEach(row => {
                        if (row.getAttribute('data-role').toLowerCase() === role.toLowerCase()) {
                            row.classList.remove('hidden');
                        } else {
                            row.classList.add('hidden');
                        }
                    });
                }

                // Modal functions
                function openModal(userId, username, role) {
                    document.getElementById('modalUserId').value = userId;
                    document.getElementById('modalUsername').value = username;
                    document.getElementById('modalRole').value = role.toLowerCase();
                    document.getElementById('updateModal').style.display = 'block';
                }

                function closeModal() {
                    document.getElementById('updateModal').style.display = 'none';
                }

                // Close modal when clicking outside of it
                window.onclick = function(event) {
                    const modal = document.getElementById('updateModal');
                    if (event.target == modal) {
                        closeModal();
                    }
                }
            </script>
        </body>
    </main>
</html>