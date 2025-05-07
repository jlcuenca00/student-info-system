<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Include database connection
require_once "db_connection.php";

// Get user role
$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Get user name based on role
$user_name = "";
switch ($role) {
    case 'student':
        $stmt = $conn->prepare("SELECT first_name, last_name FROM students WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $user_name = $row['first_name'] . ' ' . $row['last_name'];
        }
        $stmt->close();
        break;
        
    case 'teacher':
        $stmt = $conn->prepare("SELECT first_name, last_name FROM teachers WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $user_name = $row['first_name'] . ' ' . $row['last_name'];
        }
        $stmt->close();
        break;
        
    case 'admin':
        $stmt = $conn->prepare("SELECT first_name, last_name FROM admins WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $user_name = $row['first_name'] . ' ' . $row['last_name'];
        }
        $stmt->close();
        break;
}

// Handle sorting
$sort_by = isset($_GET['sort']) ? clean_input($conn, $_GET['sort']) : 'last_name';
$order = isset($_GET['order']) ? clean_input($conn, $_GET['order']) : 'ASC';

// Validate sort field to prevent SQL injection
$allowed_sort_fields = ['id', 'first_name', 'last_name', 'email'];

if (!in_array($sort_by, $allowed_sort_fields)) {
    $sort_by = 'last_name'; // Default if invalid sort field
}

// Toggle order for next sort
$toggle_order = ($order == 'ASC') ? 'DESC' : 'ASC';

// Query to get admins
$query = "SELECT a.id, a.first_name, a.last_name, a.email, u.username 
          FROM admins a
          JOIN users u ON a.user_id = u.id
          ORDER BY $sort_by $order";

$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin List - Student Information System</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* Same styles as student list view */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7f9;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background-color: #2c3e50;
            color: white;
            padding: 15px 0;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .logo h1 {
            margin: 0;
            font-size: 24px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-info p {
            margin-right: 20px;
        }
        
        .logout-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        
        .logout-btn:hover {
            background-color: #c0392b;
        }
        
        .main-content {
            display: flex;
            margin: 20px 0;
        }
        
        nav.sidebar {
            width: 250px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-right: 20px;
        }
        
        .sidebar h3 {
            margin-top: 0;
            color: #2c3e50;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        
        .sidebar li {
            margin-bottom: 10px;
        }
        
        .sidebar a {
            display: block;
            color: #34495e;
            text-decoration: none;
            padding: 10px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .sidebar a:hover {
            background-color: #f0f2f5;
        }
        
        .sidebar a.active {
            background-color: #3498db;
            color: white;
        }
        
        .content-area {
            flex: 1;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        
        .page-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .page-title h2 {
            color: #2c3e50;
            margin: 0;
        }
        
        .add-btn {
            background-color: #2ecc71;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        
        .add-btn:hover {
            background-color: #27ae60;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        table th {
            background-color: #f8f9fa;
            color: #2c3e50;
            font-weight: bold;
        }
        
        table th a {
            color: #2c3e50;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        table th a:hover {
            text-decoration: underline;
        }
        
        table th a::after {
            content: '';
            display: inline-block;
            width: 0;
            height: 0;
            margin-left: 5px;
        }
        
        table th a.asc::after {
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-bottom: 5px solid #2c3e50;
        }
        
        table th a.desc::after {
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 5px solid #2c3e50;
        }
        
        table tbody tr:hover {
            background-color: #f5f5f5;
        }
        
        .action-buttons a {
            margin-right: 10px;
            text-decoration: none;
        }
        
        .view-btn {
            color: #3498db;
        }
        
        .edit-btn {
            color: #f39c12;
        }
        
        .delete-btn {
            color: #e74c3c;
        }
        
        .footer {
            text-align: center;
            padding: 20px 0;
            margin-top: 40px;
            color: #7f8c8d;
            font-size: 14px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                flex-direction: column;
            }
            
            nav.sidebar {
                width: 100%;
                margin-right: 0;
                margin-bottom: 20px;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                <h1>Student Information System</h1>
            </div>
            <div class="user-info">
                <p>Welcome, <?php echo htmlspecialchars($user_name); ?> (<?php echo ucfirst($_SESSION['role']); ?>)</p>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="main-content">
            <nav class="sidebar">
                <h3>Main Menu</h3>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <?php if ($role == 'student' || $role == 'teacher' || $role == 'admin'): ?>
                        <li><a href="students.php">Students</a></li>
                    <?php endif; ?>
                    
                    <?php if ($role == 'teacher' || $role == 'admin'): ?>
                        <li><a href="teachers.php">Teachers</a></li>
                    <?php endif; ?>
                    
                    <?php if ($role == 'admin'): ?>
                        <li><a href="admins.php" class="active">Administrators</a></li>
                    <?php endif; ?>
                    
                    <li><a href="profile.php">My Profile</a></li>
                </ul>
            </nav>
            
            <div class="content-area">
                <div class="page-title">
                    <h2>Administrator List</h2>
                    <?php if ($role == 'admin'): ?>
                        <a href="add_admin.php" class="add-btn">Add New Administrator</a>
                    <?php endif; ?>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th><a href="?sort=id&order=<?php echo $sort_by == 'id' ? $toggle_order : 'ASC'; ?>" class="<?php echo $sort_by == 'id' ? strtolower($order) : ''; ?>">ID</a></th>
                            <th><a href="?sort=first_name&order=<?php echo $sort_by == 'first_name' ? $toggle_order : 'ASC'; ?>" class="<?php echo $sort_by == 'first_name' ? strtolower($order) : ''; ?>">First Name</a></th>
                            <th><a href="?sort=last_name&order=<?php echo $sort_by == 'last_name' ? $toggle_order : 'ASC'; ?>" class="<?php echo $sort_by == 'last_name' ? strtolower($order) : ''; ?>">Last Name</a></th>
                            <th><a href="?sort=email&order=<?php echo $sort_by == 'email' ? $toggle_order : 'ASC'; ?>" class="<?php echo $sort_by == 'email' ? strtolower($order) : ''; ?>">Email</a></th>
                            <th>Username</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td class="action-buttons">
                                <?php if ($role == 'admin'): ?>
                                    <a href="edit_admin.php?id=<?php echo $row['id']; ?>" class="edit-btn">Edit</a>
                                    <a href="delete_admin.php?id=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this administrator?')">Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php
                            }
                        } else {
                        ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">No administrators found.</td>
                        </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="footer">
            <p>© <?php echo date("Y"); ?> Student Information System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>