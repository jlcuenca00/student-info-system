<?php
include("db_connection.php");
include("auth.php");

// Only admin can delete another admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: admins.php");
    exit;
}

$id = intval($_GET['id']);

// Get user_id for admin to delete corresponding user account
$stmt = $conn->prepare("SELECT user_id FROM admins WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $userToDelete = $row['user_id'];
}
$stmt->close();

// Delete admin record
$stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
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

header("Location: admins.php");
exit;
?>
