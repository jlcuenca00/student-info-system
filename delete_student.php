<?php
include("db_connection.php");
include("auth.php");
    

$id = $_GET['id'];
$conn->query("DELETE FROM students WHERE id = $id");
header("Location: view_student.php");
exit;
