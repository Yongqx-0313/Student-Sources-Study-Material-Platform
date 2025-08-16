<?php
session_start(); // Start the session

// Check login
if (!isset($_SESSION['user'])) {
    header("Location: ../Log In.html");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "sssmp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$userID = $_SESSION['user']['UserID']; // Logged-in user ID
$userData = [];

// --- Get user info ---
$sql = "SELECT * FROM user WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $userData = $result->fetch_assoc(); // contains Name, Email, etc.
}
$stmt->close();

// --- Get user's uploaded resources (public + private) ---
$sql2 = "
    SELECT r.id, r.code, r.session, r.type, r.title, r.detail, r.likes, r.visibility, u.Name AS author
    FROM collected c
    JOIN resources r ON c.resource_id = r.id
    JOIN user u ON r.created_by = u.UserID
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
";

$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $userID);
$stmt2->execute();
$userResources = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../src/main.css">
    <link rel="stylesheet" href="css/profile.css">
</head>


<body
    style="background: linear-gradient(to right, #c6defe, #ffffff);"
    class="min-h-screen">
    <!-- Header -->
    <?php include 'header.php' ?>

    <!-- Main Section -->
    <div class="flex flex-col justify-center items-center py-10 profile">

        <div class="grid gap-6 sm:grid-cols-2 md:grid-cols-3 mt-6 justify-center items-center py-10">
            <?php while ($row = $userResources->fetch_assoc()): ?>
                <div class="bg-white p-4 rounded shadow relative">
                    <a href="resource.php?id=<?php echo $row['id']; ?>" class="block">
                        <div class="text-xs text-gray-500 mb-1">
                            <?php echo htmlspecialchars($row['code']); ?> •
                            <?php echo htmlspecialchars($row['session']); ?> •
                            <?php echo htmlspecialchars($row['type']); ?> •
                            <?php echo ucfirst($row['visibility']); ?>
                        </div>
                        <h3 class="font-semibold text-lg">
                            <?php echo htmlspecialchars($row['title']); ?>
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">
                            <?php echo htmlspecialchars(substr($row['detail'], 0, 60)); ?>...
                        </p>
                        <!-- <div class="mt-2 text-xs text-gray-500">❤ <?php echo $row['likes']; ?></div> -->
                    </a>
            
                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-2 mt-3">
                        <a href="edit_resource.php?id=<?php echo $row['id']; ?>"
                            class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 text-sm">
                            Edit
                        </a>
                        <a href="delete_resource.php?id=<?php echo $row['id']; ?>"
                            onclick="return confirm('Are you sure you want to delete this resource?');"
                            class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 text-sm">
                            Delete
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>

        </div>

    </div>

    <!-- Footer -->
    <?php include 'footer.php' ?>
</body>


</html>