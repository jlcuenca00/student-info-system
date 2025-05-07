<?php
include("db_connection.php");
include("auth.php");

// Only admin can delete a teacher
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);

// Get user_id for teacher to delete associated user account
$stmt = $conn->prepare("SELECT user_id FROM teachers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $userToDelete = $row['user_id'];
}
$stmt->close();

// Delete teacher record
$stmt = $conn->prepare("DELETE FROM teachers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

// Delete corresponding user account
if (!empty($userToDelete)) {
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $userToDelete);
    $stmt->execute();
    $stmt->close();
}

header("Location: teachers.php");
exit;
?>
