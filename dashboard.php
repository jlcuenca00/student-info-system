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

// Get user details based on role
$user_id = $_SESSION['user_id'];
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

// Get user-specific statistics
$user_specific_stats = [];

if ($role == 'student') {
    // Get student-specific stats
    $stmt = $conn->prepare("SELECT 
                              department_id, course_id, year_level, 
                              enrollment_status, units_taken_current, units_taken_overall, gpa
                           FROM students WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user_specific_stats = $result->fetch_assoc();
        
        // Get department name
        $dept_stmt = $conn->prepare("SELECT name FROM departments WHERE id = ?");
        $dept_stmt->bind_param("i", $user_specific_stats['department_id']);
        $dept_stmt->execute();
        $dept_result = $dept_stmt->get_result();
        if ($dept_result->num_rows > 0) {
            $dept_row = $dept_result->fetch_assoc();
            $user_specific_stats['department_name'] = $dept_row['name'];
        }
        $dept_stmt->close();
        
        // Get course name
        $course_stmt = $conn->prepare("SELECT name FROM courses WHERE id = ?");
        $course_stmt->bind_param("i", $user_specific_stats['course_id']);
        $course_stmt->execute();
        $course_result = $course_stmt->get_result();
        if ($course_result->num_rows > 0) {
            $course_row = $course_result->fetch_assoc();
            $user_specific_stats['course_name'] = $course_row['name'];
        }
        $course_stmt->close();
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Student Information System</title>
    <link rel="stylesheet" href="css/styles.css">
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
        
        .welcome-section {
            margin-bottom: 30px;
        }
        
        .welcome-section h2 {
            color: #2c3e50;
            margin-top: 0;
        }
        
        .stats-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .stat-card {
            background-color: #f8f9fa;
            border-left: 4px solid #3498db;
            border-radius: 4px;
            padding: 20px;
            flex: 1;
            min-width: 200px;
        }
        
        .stat-card h3 {
            margin-top: 0;
            color: #7f8c8d;
            font-size: 16px;
        }
        
        .stat-card p {
            margin-bottom: 0;
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .user-stats {
            margin-top: 30px;
        }
        
        .user-stats h3 {
            color: #2c3e50;
            margin-top: 0;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .student-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .student-stat-card {
            background-color: #f8f9fa;
            border-radius: 4px;
            padding: 15px;
        }
        
        .student-stat-card h4 {
            margin-top: 0;
            color: #7f8c8d;
            font-size: 15px;
        }
        
        .student-stat-card p {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 0;
        }
        
        .activity-feed {
            margin-top: 30px;
        }
        
        .activity-feed h3 {
            color: #2c3e50;
            margin-top: 0;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .activity-item {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            background-color: #3498db;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .activity-details {
            flex: 1;
        }
        
        .activity-details h4 {
            margin: 0 0 5px 0;
            color: #2c3e50;
            font-size: 16px;
        }
        
        .activity-details p {
            margin: 0;
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .activity-date {
            color: #95a5a6;
            font-size: 12px;
            white-space: nowrap;
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
            
            .stats-cards {
                flex-direction: column;
            }
            
            .student-stats-grid {
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
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <?php if ($role == 'student' || $role == 'teacher' || $role == 'admin'): ?>
                        <li><a href="students.php">Students</a></li>
                    <?php endif; ?>
                    
                    <?php if ($role == 'teacher' || $role == 'admin'): ?>
                        <li><a href="teachers.php">Teachers</a></li>
                    <?php endif; ?>
                    
                    <?php if ($role == 'admin'): ?>
                        <li><a href="admins.php">Administrators</a></li>
                    <?php endif; ?>
                    <li><a href="profile.php">My Profile</a></li>
                </ul>
            </nav>
            
            <div class="content-area">
                <div class="welcome-section">
                    <h2>Dashboard</h2>
                    <p>Welcome to the Student Information System. Use the sidebar to navigate through different sections.</p>
                </div>
                
                <div class="stats-cards">
                    <?php if ($role == 'student' || $role == 'teacher' || $role == 'admin'): ?>
                        <div class="stat-card">
                            <h3>Total Students</h3>
                            <?php
                            $result = $conn->query("SELECT COUNT(*) as count FROM students");
                            $row = $result->fetch_assoc();
                            echo "<p>" . $row['count'] . "</p>";
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($role == 'teacher' || $role == 'admin'): ?>
                        <div class="stat-card">
                            <h3>Total Teachers</h3>
                            <?php
                            $result = $conn->query("SELECT COUNT(*) as count FROM teachers");
                            $row = $result->fetch_assoc();
                            echo "<p>" . $row['count'] . "</p>";
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($role == 'admin'): ?>
                        <div class="stat-card">
                            <h3>Total Administrators</h3>
                            <?php
                            $result = $conn->query("SELECT COUNT(*) as count FROM admins");
                            $row = $result->fetch_assoc();
                            echo "<p>" . $row['count'] . "</p>";
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="stat-card">
                        <h3>Total Departments</h3>
                        <?php
                        $result = $conn->query("SELECT COUNT(*) as count FROM departments");
                        $row = $result->fetch_assoc();
                        echo "<p>" . $row['count'] . "</p>";
                        ?>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Total Courses</h3>
                        <?php
                        $result = $conn->query("SELECT COUNT(*) as count FROM courses");
                        $row = $result->fetch_assoc();
                        echo "<p>" . $row['count'] . "</p>";
                        ?>
                    </div>
                </div>

                <?php if ($role == 'student' && !empty($user_specific_stats)): ?>
                <div class="user-stats">
                    <h3>Your Academic Information</h3>
                    <div class="student-stats-grid">
                        <div class="student-stat-card">
                            <h4>Department</h4>
                            <p><?php echo htmlspecialchars($user_specific_stats['department_name'] ?? 'Not assigned'); ?></p>
                        </div>
                        <div class="student-stat-card">
                            <h4>Course</h4>
                            <p><?php echo htmlspecialchars($user_specific_stats['course_name'] ?? 'Not assigned'); ?></p>
                        </div>
                        <div class="student-stat-card">
                            <h4>Year Level</h4>
                            <p><?php echo htmlspecialchars($user_specific_stats['year_level'] ?? 'Not assigned'); ?></p>
                        </div>
                        <div class="student-stat-card">
                            <h4>Enrollment Status</h4>
                            <p><?php echo htmlspecialchars($user_specific_stats['enrollment_status'] ?? 'Not available'); ?></p>
                        </div>
                        <div class="student-stat-card">
                            <h4>Units Taken (Current)</h4>
                            <p><?php echo htmlspecialchars($user_specific_stats['units_taken_current'] ?? '0'); ?></p>
                        </div>
                        <div class="student-stat-card">
                            <h4>Units Taken (Total)</h4>
                            <p><?php echo htmlspecialchars($user_specific_stats['units_taken_total'] ?? '0'); ?></p>
                        </div>
                        <div class="student-stat-card">
                            <h4>Current GPA</h4>
                            <p><?php echo htmlspecialchars($user_specific_stats['gpa'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="activity-feed">
                    <h3>Recent System Activities</h3>
                    <?php
                    // This would normally fetch from a system_logs table
                    // For now, we'll just display placeholder activities
                    $activities = [
                        ['icon' => '📊', 'title' => 'System Update', 'description' => 'The system was updated with new features.', 'date' => '2 hours ago'],
                        ['icon' => '📆', 'title' => 'Academic Calendar', 'description' => 'The academic calendar for the upcoming semester has been published.', 'date' => '1 day ago'],
                        ['icon' => '📝', 'title' => 'Registration Period', 'description' => 'Registration for the next semester will begin next week.', 'date' => '3 days ago']
                    ];
                    
                    foreach ($activities as $activity):
                    ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <?php echo $activity['icon']; ?>
                        </div>
                        <div class="activity-details">
                            <h4><?php echo htmlspecialchars($activity['title']); ?></h4>
                            <p><?php echo htmlspecialchars($activity['description']); ?></p>
                        </div>
                        <div class="activity-date">
                            <?php echo htmlspecialchars($activity['date']); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>© <?php echo date("Y"); ?> Student Information System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>