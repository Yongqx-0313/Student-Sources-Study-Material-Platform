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

$userID = $_SESSION['user']['UserID'] ?? 0; // Logged-in user ID
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
    ORDER BY r.id DESC
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


<body style="background: linear-gradient(to right, #c6defe, #ffffff);" class="min-h-screen flex flex-col">
    <!-- Header -->
    <?php include 'header.php' ?>

    <!-- Main Section -->
    <div class="flex flex-col flex-1 justify-center items-center py-10 profile">

        <div class="grid gap-6 sm:grid-cols-2 md:grid-cols-3 mt-6 justify-center items-center py-10">
            <?php while ($row = $userResources->fetch_assoc()): ?>
                <div class="bg-white p-4 rounded shadow relative card">
                    <a href="resource.php?id=<?= $row['id']; ?>" class="block h-full">
                        <div class="text-xs text-gray-500 mb-1 pr-6"> <!-- add padding-right to avoid overlap -->
                            <?= htmlspecialchars($row['code']); ?> •
                            <?= htmlspecialchars($row['session']); ?> •
                            <?= htmlspecialchars($row['type']); ?> •
                            <?= ucfirst($row['visibility']); ?>
                        </div>

                        <h3 class="font-semibold text-lg">
                            <?= htmlspecialchars($row['title']); ?>
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">
                            <?= htmlspecialchars(substr($row['detail'], 0, 60)); ?>...
                        </p>
                    </a>

                    <!-- Star button -->
                    <button class="collect-btn absolute top-3 right-3 text-yellow-400 hover:scale-110 transition"
                        data-id="<?= $row['id']; ?>" aria-pressed="true">
                        <i class="fa-solid fa-star text-sm"></i> <!-- fixed size -->
                    </button>
                    <div class="mt-3 flex justify-between text-xs text-gray-500">
                        <span>By <?php echo htmlspecialchars($row['author']); ?></span>
                    </div>
                </div>

            <?php endwhile; ?>

        </div>

    </div>

    <!-- Footer -->
    <?php include 'footer.php' ?>

    <script>
        document.querySelectorAll('.collect-btn').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                const resourceId = this.dataset.id;
                const isCollected = this.getAttribute('aria-pressed') === 'true';

                fetch('toggle_collect.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `resource_id=${encodeURIComponent(resourceId)}&action=${isCollected ? 'uncollect' : 'collect'}`
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const icon = this.querySelector('i');
                            this.setAttribute('aria-pressed', data.collected ? 'true' : 'false');

                            this.classList.toggle('text-yellow-400', data.collected);
                            this.classList.toggle('text-gray-400', !data.collected);
                            icon.classList.toggle('fa-solid', data.collected);
                            icon.classList.toggle('fa-regular', !data.collected);

                            if (!data.collected) {
                                this.closest('.card').remove(); // remove card if uncollected
                            }
                        } else {
                            alert("Action failed: " + data.message);
                        }
                    })
                    .catch(err => console.error("AJAX error:", err));
            });
        });

    </script>

</body>


</html>