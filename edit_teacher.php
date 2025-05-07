<?php
include("db_connection.php");
include("auth.php");

// Only admin can edit teacher details
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);

// Fetch department list
$departments = $conn->query("SELECT id, name FROM departments");

// Get current teacher data
$result = $conn->query("SELECT * FROM teachers WHERE id=$id");
$row = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $conn->prepare("UPDATE teachers SET first_name=?, last_name=?, department_id=?, email=?, mobile_no=? WHERE id=?");
    $stmt->bind_param("ssiss i", 
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['department_id'],
        $_POST['email'],
        $_POST['mobile_no'],
        $id
    );
    $stmt->execute();
    header("Location: profile_teacher.php?id=$id");
    exit;
}

// Get admin user name for header
$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
$user_name = "";
$stmt = $conn->prepare("SELECT first_name, last_name FROM admins WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result2 = $stmt->get_result();
if ($result2->num_rows > 0) {
    $user_row = $result2->fetch_assoc();
    $user_name = $user_row['first_name'] . ' ' . $user_row['last_name'];
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Teacher - Student Information System</title>
    <style>
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
        
        .back-btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .back-btn:hover {
            background-color: #2980b9;
        }
        
        .info-section {
            margin-bottom: 30px;
        }
        
        .info-section h3 {
            color: #2c3e50;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .info-item {
            margin-bottom: 15px;
        }
        
        .info-item label {
            display: block;
            font-weight: bold;
            color: #7f8c8d;
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .info-item input, 
        .info-item select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .action-buttons {
            margin-top: 20px;
        }
        
        .save-btn {
            background-color: #2ecc71;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
        }
        
        .save-btn:hover {
            background-color: #27ae60;
        }
        
        .cancel-btn {
            background-color: #95a5a6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
            margin-left: 10px;
        }
        
        .cancel-btn:hover {
            background-color: #7f8c8d;
        }
        
        .footer {
            text-align: center;
            padding: 20px 0;
            margin-top: 40px;
            color: #7f8c8d;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .main-content {
                flex-direction: column;
            }
            
            nav.sidebar {
                width: 100%;
                margin-right: 0;
                margin-bottom: 20px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
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
                <p>Welcome, <?php echo htmlspecialchars($user_name); ?> (<?php echo ucfirst($role); ?>)</p>
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
                    <li><a href="students.php">Students</a></li>
                    <?php if ($role == 'teacher' || $role == 'admin'): ?>
                        <li><a href="teachers.php" class="active">Teachers</a></li>
                    <?php endif; ?>
                    <?php if ($role == 'admin'): ?>
                        <li><a href="admins.php">Administrators</a></li>
                    <?php endif; ?>
                    <li><a href="profile.php">My Profile</a></li>
                </ul>
            </nav>
            <div class="content-area">
                <div class="page-title">
                    <h2>Edit Teacher Profile</h2>
                    <a href="profile_teacher.php?id=<?php echo $id; ?>" class="back-btn">Cancel</a>
                </div>
                <form method="post">
                    <div class="info-section">
                        <h3>Personal Information</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>First Name</label>
                                <input type="text" name="first_name" value="<?php echo htmlspecialchars($row['first_name']); ?>" required>
                            </div>
                            <div class="info-item">
                                <label>Last Name</label>
                                <input type="text" name="last_name" value="<?php echo htmlspecialchars($row['last_name']); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="info-section">
                        <h3>Professional Information</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Department</label>
                                <select name="department_id" required>
                                    <option value="">Select Department</option>
                                    <?php while($dept = $departments->fetch_assoc()): ?>
                                        <option value="<?= $dept['id'] ?>" <?= $dept['id'] == $row['department_id'] ? 'selected' : '' ?>>
                                            <?= $dept['name'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="info-section">
                        <h3>Contact Information</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Email</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required>
                            </div>
                            <div class="info-item">
                                <label>Mobile Number</label>
                                <input type="text" name="mobile_no" value="<?php echo htmlspecialchars($row['mobile_no']); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="action-buttons">
                        <button type="submit" class="save-btn">Save Changes</button>
                        <a href="profile_teacher.php?id=<?php echo $id; ?>" class="cancel-btn">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
        <div class="footer">
            <p>© <?php echo date("Y"); ?> Student Information System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
