<?php
// index.php - User selection page
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "registration_db";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all users
    $sql = "SELECT id, applicant_name, email, city FROM info_user ORDER BY id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GIL Registration Forms</title>
  <link rel="stylesheet" href="assests/css/seeerecord.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>GIL Registration System</h1>
            <p>View and Print Registration Forms</p>
        </div>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($users); ?></div>
                <div class="stat-label">Total Registered Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo date('Y'); ?></div>
                <div class="stat-label">Current Year</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo date('d/m'); ?></div>
                <div class="stat-label">Today's Date</div>
            </div>
        </div>

        <?php if (!empty($users)): ?>
            <input type="text" class="search-box" id="searchBox" placeholder="Search by name, email, or city..." onkeyup="filterUsers()">
            
            <table class="users-table" id="usersTable">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Applicant Name</th>
                        <th>Email</th>
                        <th>City</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['applicant_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['city']); ?></td>
                            <td>
                                <a href="printform_filled.php?user_id=<?php echo $user['id']; ?>" 
                                   class="btn btn-view" target="_blank">
                                   View Form
                                </a>
                                <a href="get_user_data.php?id=<?php echo $user['id']; ?>" 
                                   class="btn" target="_blank">
                                   JSON Data
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
                    <div class="no-users">
                <p>No users found in the database.</p>
                <p>Please add some users first.</p>
            </div>
        <?php endif; ?>
    </div>

   
</body>
<script src="assests/javascript/seerecord.js"></script>
</html>