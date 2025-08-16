<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "sssmp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['comment'])) {
    $comment = trim($_POST['comment']);
    if ($comment !== '') {
        $stmt = $conn->prepare("INSERT INTO comments (resource_id, comment) VALUES (?, ?)");
        $stmt->bind_param("is", $id, $comment);
        $stmt->execute();
        $stmt->close();
        // Refresh page to show new comment but stay on same ID
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id);
        exit();
    }
}

// Fetch resource
$stmt = $conn->prepare("SELECT title, detail, pdf_file FROM resources WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($title, $detail, $pdf_file);
$stmt->fetch();
$stmt->close();

if (!$title) {
    die("Resource not found.");
}

// Fetch comments
$comments = [];
$stmt = $conn->prepare("SELECT comment, created_at FROM comments WHERE resource_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $comments[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Knowledge Hub</title>
    <link rel="stylesheet" href="css/profile.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body style="background: linear-gradient(to right, #c6defe, #ffffff);" class=" text-gray-900">

    <!-- Header -->
    <?php include 'header.php' ?>

    <!-- Back Button -->
    <button class=" ml-8 mt-3 bg-indigo-600 text-white px-4 py-2 rounded hover:bg-black/10">
        <a href="Main.php">
            <span>←</span> Back
        </a>
    </button>

    <main class="mx-auto max-w-6xl px-4 py-6">

    
        <!-- Resource Details -->
        <div class="max-w-3xl mx-auto px-4 py-8 mb-6 bg-white shadow rounded-lg">
            <h1 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($title); ?></h1>
                    <p class="text-gray-700 text-lg mb-4"><?php echo htmlspecialchars($detail); ?></p>
                
                    <?php if (!empty($pdf_file)): ?>
                        <div class="mt-4">
                            <h3 class="text-lg font-semibold mb-2">Preview:</h3>
                            <iframe src="<?php echo htmlspecialchars($pdf_file); ?>" class="w-full h-[600px] border rounded-lg"></iframe>
                            <a href="<?php echo htmlspecialchars($pdf_file); ?>" target="_blank"
                                class="inline-block mt-3 bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                                Download PDF
                            </a>
                        </div>
                    <?php endif; ?>
                </div>


        <!-- Comment Section -->
        <div class="max-w-3xl mx-auto px-4 py-8 bg-white shadow rounded-lg">
            <h2 class="text-xl font-semibold mb-4">Comments</h2>

            <!-- Comment Form -->
            <form method="POST" class="flex mb-4">
                <input type="text" name="comment" placeholder="Type your comment..."
                    class="flex-grow border border-gray-300 rounded-l-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                    required>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-r-lg hover:bg-indigo-700">
                    Enter Comment
                </button>
            </form>

            <!-- Display Comments -->
            <?php if (count($comments) > 0): ?>
                <div class="space-y-3">
                    <?php foreach ($comments as $c): ?>
                        <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                            <p class="text-gray-800"><?php echo htmlspecialchars($c['comment']); ?></p>
                            <small class="text-gray-500"><?php echo $c['created_at']; ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-600">No comments yet. Be the first!</p>
            <?php endif; ?>
        </div>

    </main>

    <!-- Footer -->
    <?php include 'footer.php' ?>

</body>

</html>