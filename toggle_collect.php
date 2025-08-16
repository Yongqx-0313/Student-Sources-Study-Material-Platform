<?php
session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(401); // Unauthorized
    exit("Not logged in");
}

$conn = new mysqli("localhost", "root", "", "sssmp");
if ($conn->connect_error) {
    http_response_code(500);
    exit("DB connection failed");
}

$userID = $_SESSION['user']['UserID'] ?? null;

$resourceID = (int)($_POST['resource_id'] ?? 0);

if ($resourceID === 0) {
    http_response_code(400);
    exit("Invalid resource ID");
}

// Check if already collected
$sql = "SELECT * FROM collected WHERE user_id = ? AND resource_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $userID, $resourceID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Already collected → remove it
    $conn->query("DELETE FROM collected WHERE user_id = $userID AND resource_id = $resourceID");
    echo json_encode(["status" => "uncollected"]);
} else {
    // Not collected → insert
    $sql = "INSERT INTO collected (user_id, resource_id, created_at) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userID, $resourceID);
    $stmt->execute();
    echo json_encode(["status" => "collected"]);
}
?>
