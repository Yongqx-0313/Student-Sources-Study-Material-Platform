<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../Log In.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "sssmp");
if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

$id = intval($_GET['id']);
$userID = $_SESSION['user']['UserID'];

$sql = "DELETE FROM resources WHERE id=? AND created_by=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $userID);
$stmt->execute();

header("Location: profile.php");
exit();
?>