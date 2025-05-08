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

// Check if student ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: students.php");
    exit;
}

$student_id = clean_input($conn, $_GET['id']);

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

// Get student details
$query = "SELECT s.*, d.name as department_name, c.name as course_name 
          FROM students s
          LEFT JOIN departments d ON s.department_id = d.id
          LEFT JOIN courses c ON s.course_id = c.id
          WHERE s.id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Student not found, redirect to student list
    header("Location: students.php");
    exit;
}

$student = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile - Student Information System</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #1b2631;
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
        
        .student-profile {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }
        
        .student-photo-container {
            width: 200px;
            margin-right: 30px;
            margin-bottom: 20px;
        }
        
        .student-photo {
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .student-info {
            flex: 1;
            min-width: 300px;
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
        
        .info-item p {
            margin: 0;
            color: #2c3e50;
            font-size: 16px;
        }
        
        .status-tag {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
        }
        
        .status-enrolled {
            background-color: #2ecc71;
            color: white;
        }
        
        .status-inactive {
            background-color: #e74c3c;
            color: white;
        }
        
        .gpa-excellent {
            color: #2ecc71;
        }
        
        .gpa-good {
            color: #3498db;
        }
        
        .gpa-average {
            color: #f39c12;
        }
        
        .gpa-poor {
            color: #e74c3c;
        }
        
        .action-buttons {
            margin-top: 20px;
        }
        
        .edit-btn {
            background-color: #f39c12;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            margin-right: 10px;
        }
        
        .edit-btn:hover {
            background-color: #d35400;
        }
        
        .delete-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        
        .delete-btn:hover {
            background-color: #c0392b;
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
            
            .student-photo-container {
                width: 150px;
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
                        <li><a href="students.php" class="active">Students</a></li>
                    <?php endif; ?>
                    
                    <?php if ($role == 'teacher' || $role == 'admin'): ?>
                        <li><a href="teachers.php">Teachers</a></li>
                    <?php endif; ?>
                    
                    <?php if ($role == 'admin'): ?>
                        <li><a href="admins.php">Administrators</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="content-area">
                <div class="page-title">
                    <h2>Student Profile</h2>
                    <a href="students.php" class="back-btn">Back to Student List</a>
                </div>
                
                <div class="student-profile">
                    <div class="student-photo-container">
                        <img src="uploads/<?php echo !empty($student['photo']) ? htmlspecialchars($student['photo']) : 'default.jpg'; ?>" 
                             alt="Student Photo" class="student-photo">
                    </div>
                    
                    <div class="student-info">
                        <h3><?php echo htmlspecialchars($student['first_name']) . ' ' . htmlspecialchars($student['last_name']); ?></h3>
                        <p>
                            <span class="status-tag <?php echo $student['enrollment_status'] == 'Enrolled' ? 'status-enrolled' : 'status-inactive'; ?>">
                                <?php echo htmlspecialchars($student['enrollment_status']); ?>
                            </span>
                        </p>
                        
                        <?php if ($role == 'admin'): ?>
                            <div class="action-buttons">
                                <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="edit-btn">Edit Profile</a>
                                <a href="delete_student.php?id=<?php echo $student['id']; ?>" class="delete-btn" 
                                   onclick="return confirm('Are you sure you want to delete this student?')">Delete Student</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="info-section">
                    <h3>Personal Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Student ID</label>
                            <p><?php echo htmlspecialchars($student['student_id']); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Age</label>
                            <p><?php echo htmlspecialchars($student['age']); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Date of Birth</label>
                            <p><?php echo htmlspecialchars($student['date_of_birth']); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Admission Date</label>
                            <p><?php echo htmlspecialchars($student['admission_date']); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Mobile Number</label>
                            <p><?php echo htmlspecialchars($student['mobile_no']); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Nationality</label>
                            <p><?php echo htmlspecialchars($student['nationality']); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Email</label>
                            <p><?php echo htmlspecialchars($student['email']); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="info-section">
                    <h3>Address</h3>
                    <div class="info-item">
                        <p><?php echo htmlspecialchars($student['address']); ?></p>
                    </div>
                </div>
                
                <div class="info-section">
                    <h3>Academic Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Department</label>
                            <p><?php echo htmlspecialchars($student['department_name']); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Course</label>
                            <p><?php echo htmlspecialchars($student['course_name']); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Year Level</label>
                            <p><?php echo htmlspecialchars($student['year_level']); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Section</label>
                            <p><?php echo htmlspecialchars($student['section']); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Units Taken (This SY)</label>
                            <p><?php echo htmlspecialchars($student['units_taken_current']); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Units Taken (Overall)</label>
                            <p><?php echo htmlspecialchars($student['units_taken_overall']); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Current GPA</label>
                            <p class="<?php 
                                if ($student['gpa'] <= 1.5) echo 'gpa-excellent';
                                else if ($student['gpa'] <= 2.5) echo 'gpa-good';
                                else if ($student['gpa'] <= 3.5) echo 'gpa-average';
                                else echo 'gpa-poor';
                            ?>">
                                <?php echo htmlspecialchars($student['gpa']); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>© <?php echo date("Y"); ?> Student Information System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>