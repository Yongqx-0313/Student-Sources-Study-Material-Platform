<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['userID'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['userID'];
$resourceId = isset($_POST['resource_id']) ? (int)$_POST['resource_id'] : 0;

$conn = new mysqli("localhost", "root", "", "sssmp");
if ($conn->connect_error) {
    echo json_encode(['error' => 'DB error']);
    exit;
}

// Check if already collected
$stmt = $conn->prepare("SELECT id FROM collected WHERE user_id = ? AND resource_id = ?");
$stmt->bind_param("ii", $userId, $resourceId);
$stmt->execute();
$stmt->store_result();

$alreadyCollected = $stmt->num_rows > 0;
$stmt->close();

if ($alreadyCollected) {
    $stmt = $conn->prepare("DELETE FROM collected WHERE user_id = ? AND resource_id = ?");
    $stmt->bind_param("ii", $userId, $resourceId);
    $stmt->execute();
    $stmt->close();
    $collected = false;
} else {
    $stmt = $conn->prepare("INSERT INTO collected (user_id, resource_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $userId, $resourceId);
    $stmt->execute();
    $stmt->close();
    $collected = true;
}

echo json_encode(['collected' => $collected]);
?>
