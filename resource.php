<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "sssmp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch the specific resource
$stmt = $conn->prepare("SELECT title, detail FROM resources WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($title, $detail);
$stmt->fetch();
$stmt->close();
$conn->close();

if (!$title) {
    die("Resource not found.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MMU Knowledge Hub</title>
    <link rel="stylesheet" href="css/profile.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body style="background: linear-gradient(to right, #c6defe, #ffffff);" class=" text-gray-900">


    <!-- Header -->
    <?php include 'header.php' ?>

    <div class="max-w-3xl mx-auto px-4 py-8 bg-white shadow rounded-lg">
        <!-- Back Button -->
        <a href="Main.php" class="inline-block mb-4 bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">←
            Back</a>

        <!-- Title and Details -->
        <h1 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($title); ?></h1>
        <p class="text-gray-700 text-lg"><?php echo htmlspecialchars($detail); ?></p>
    </div>

    <!-- Header -->
    <?php include 'footer.php' ?>
</body>

</html>