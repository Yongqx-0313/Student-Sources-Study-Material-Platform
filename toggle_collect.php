<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$conn = new mysqli("localhost", "root", "", "sssmp");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB error']);
    exit;
}

$userID = $_SESSION['user']['UserID'];
$resourceID = (int)($_POST['resource_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$resourceID || !in_array($action, ['collect', 'uncollect'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

if ($action === 'collect') {
    $stmt = $conn->prepare("INSERT IGNORE INTO collected (user_id, resource_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $userID, $resourceID);
    $stmt->execute();
    $collected = true;
} else {
    $stmt = $conn->prepare("DELETE FROM collected WHERE user_id = ? AND resource_id = ?");
    $stmt->bind_param("ii", $userID, $resourceID);
    $stmt->execute();
    $collected = false;
}

echo json_encode(['success' => true, 'collected' => $collected]);
?>
