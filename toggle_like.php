<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['user']['UserID'];
$resourceId = isset($_POST['resource_id']) ? (int)$_POST['resource_id'] : 0;

$conn = new mysqli("localhost", "root", "", "sssmp");
if ($conn->connect_error) {
    echo json_encode(['error' => 'DB connection failed']);
    exit;
}

// Check if already liked
$stmt = $conn->prepare("SELECT id FROM resource_likes WHERE user_id = ? AND resource_id = ?");
$stmt->bind_param("ii", $userId, $resourceId);
$stmt->execute();
$stmt->store_result();

$liked = $stmt->num_rows > 0;
$stmt->close();

if ($liked) {
    $stmt = $conn->prepare("DELETE FROM resource_likes WHERE user_id = ? AND resource_id = ?");
    $stmt->bind_param("ii", $userId, $resourceId);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("UPDATE resources SET likes = GREATEST(likes - 1, 0) WHERE id = ?");
    $stmt->bind_param("i", $resourceId);
    $stmt->execute();
    $stmt->close();

    $newLikeStatus = false;
} else {
    $stmt = $conn->prepare("INSERT INTO resource_likes (user_id, resource_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $userId, $resourceId);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("UPDATE resources SET likes = likes + 1 WHERE id = ?");
    $stmt->bind_param("i", $resourceId);
    $stmt->execute();
    $stmt->close();

    $newLikeStatus = true;
}

// Get updated like count
$stmt = $conn->prepare("SELECT likes FROM resources WHERE id = ?");
$stmt->bind_param("i", $resourceId);
$stmt->execute();
$stmt->bind_result($newCount);
$stmt->fetch();
$stmt->close();

echo json_encode([
    'liked' => $newLikeStatus,
    'count' => $newCount
]);
?>
