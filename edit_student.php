<?php
include("db_connection.php");
include("auth.php");

$id = intval($_GET['id']);

// Fetch department and course lists
$departments = $conn->query("SELECT id, name FROM departments");
$courses = $conn->query("SELECT id, name FROM courses");

// Get current student data
$result = $conn->query("SELECT * FROM students WHERE id=$id");
$row = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle file upload
    $photoName = $row['photo']; // default to existing
    if (!empty($_FILES['photo']['name'])) {
        $uploadDir = "uploads/";
        $photoName = basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $photoName);
    }

    $stmt = $conn->prepare("UPDATE students SET first_name=?, last_name=?, student_id=?, photo=?, age=?, date_of_birth=?, admission_date=?, mobile_no=?, nationality=?, email=?, address=?, department_id=?, course_id=?, year_level=?, section=?, enrollment_status=?, units_taken_current=?, units_taken_overall=?, gpa=? WHERE id=?");

    $stmt->bind_param("ssssssssssssssssiidi",
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['student_id'],
        $photoName,
        $_POST['age'],
        $_POST['date_of_birth'],
        $_POST['admission_date'],
        $_POST['mobile_no'],
        $_POST['nationality'],
        $_POST['email'],
        $_POST['address'],
        $_POST['department_id'],
        $_POST['course_id'],
        $_POST['year_level'],
        $_POST['section'],
        $_POST['enrollment_status'],
        $_POST['units_taken_current'],
        $_POST['units_taken_overall'],
        $_POST['gpa'],
        $id
    );
    $stmt->execute();
    header("Location: profile.php?id=$id");
    exit;
}

// Get user name and role for header
//session_start();
$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
$user_name = "";
$stmt = $conn->prepare("SELECT first_name, last_name FROM admins WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user_row = $result->fetch_assoc();
    $user_name = $user_row['first_name'] . ' ' . $user_row['last_name'];
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student - Student Information System</title>
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
        
        .info-item input, 
        .info-item select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .info-item textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            min-height: 80px;
        }
        
        .status-tag {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
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
                    <li><a href="students.php" class="active">Students</a></li>
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
                <div class="page-title">
                    <h2>Edit Student Profile</h2>
                    <a href="view_student.php?id=<?php echo $id; ?>" class="back-btn">Cancel</a>
                </div>
                
                <form method="post" enctype="multipart/form-data">
                    <div class="student-profile">
                        <div class="student-photo-container">
                            <img src="uploads/<?php echo !empty($row['photo']) ? htmlspecialchars($row['photo']) : 'default.jpg'; ?>" 
                                 alt="Student Photo" class="student-photo">
                            <input type="file" name="photo" style="margin-top: 10px;">
                        </div>
                        
                        <div class="student-info">
                            <div class="info-item">
                                <label>First Name</label>
                                <input type="text" name="first_name" value="<?php echo htmlspecialchars($row['first_name']); ?>" required>
                            </div>
                            <div class="info-item">
                                <label>Last Name</label>
                                <input type="text" name="last_name" value="<?php echo htmlspecialchars($row['last_name']); ?>" required>
                            </div>
                            <div class="info-item">
                                <label>Student ID</label>
                                <input type="text" name="student_id" value="<?php echo htmlspecialchars($row['student_id']); ?>" required>
                            </div>
                            <div class="info-item">
                                <label>Enrollment Status</label>
                                <select name="enrollment_status" required>
                                    <?php
                                    $statuses = ['Enrolled', 'Not Enrolled', 'Leave of Absence', 'Graduated', 'Dropped'];
                                    foreach ($statuses as $status) {
                                        $selected = ($row['enrollment_status'] == $status) ? 'selected' : '';
                                        echo "<option value=\"$status\" $selected>$status</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-section">
                        <h3>Personal Information</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Age</label>
                                <input type="number" name="age" value="<?php echo htmlspecialchars($row['age']); ?>" required>
                            </div>
                            <div class="info-item">
                                <label>Date of Birth</label>
                                <input type="date" name="date_of_birth" value="<?php echo htmlspecialchars($row['date_of_birth']); ?>" required>
                            </div>
                            <div class="info-item">
                                <label>Admission Date</label>
                                <input type="date" name="admission_date" value="<?php echo htmlspecialchars($row['admission_date']); ?>" required>
                            </div>
                            <div class="info-item">
                                <label>Mobile Number</label>
                                <input type="text" name="mobile_no" value="<?php echo htmlspecialchars($row['mobile_no']); ?>" required>
                            </div>
                            <div class="info-item">
                                <label>Nationality</label>
                                <input type="text" name="nationality" value="<?php echo htmlspecialchars($row['nationality']); ?>" required>
                            </div>
                            <div class="info-item">
                                <label>Email</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-section">
                        <h3>Address</h3>
                        <div class="info-item">
                            <textarea name="address" required><?php echo htmlspecialchars($row['address']); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="info-section">
                        <h3>Academic Information</h3>
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
                            <div class="info-item">
                                <label>Course</label>
                                <select name="course_id" required>
                                    <option value="">Select Course</option>
                                    <?php while($course = $courses->fetch_assoc()): ?>
                                        <option value="<?= $course['id'] ?>" <?= $course['id'] == $row['course_id'] ? 'selected' : '' ?>>
                                            <?= $course['name'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="info-item">
                                <label>Year Level</label>
                                <input type="number" name="year_level" value="<?php echo htmlspecialchars($row['year_level']); ?>" required>
                            </div>
                            <div class="info-item">
                                <label>Section</label>
                                <input type="text" name="section" value="<?php echo htmlspecialchars($row['section']); ?>" required>
                            </div>
                            <div class="info-item">
                                <label>Units Taken (This SY)</label>
                                <input type="number" name="units_taken_current" value="<?php echo htmlspecialchars($row['units_taken_current']); ?>" required>
                            </div>
                            <div class="info-item">
                                <label>Units Taken (Overall)</label>
                                <input type="number" name="units_taken_overall" value="<?php echo htmlspecialchars($row['units_taken_overall']); ?>" required>
                            </div>
                            <div class="info-item">
                                <label>Current GPA</label>
                                <input type="number" step="0.01" name="gpa" value="<?php echo htmlspecialchars($row['gpa']); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <button type="submit" class="save-btn">Save Changes</button>
                        <a href="profile.php?id=<?php echo $id; ?>" class="cancel-btn">Cancel</a>
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