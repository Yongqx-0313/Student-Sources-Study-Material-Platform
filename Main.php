<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "sssmp");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Fetch resources
$sql = "
  SELECT r.*, u.Name AS author,
         (SELECT 1 FROM resource_likes WHERE user_id = ? AND resource_id = r.id LIMIT 1) AS liked
  FROM resources r
  JOIN user u ON r.created_by = u.UserID
  WHERE r.visibility = 'public'
  ORDER BY r.id DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
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
  <!-- Search & Filters -->
  <div class="max-w-7xl mx-auto px-4 py-6">
    <form class="bg-white rounded-lg shadow p-4 flex flex-wrap gap-3">
      <input type="text" placeholder="Search title or description..."
        class="flex-1 border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200" />
      <input type="text" placeholder="Subject Code"
        class="w-32 border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200" />
      <select class="border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200">
        <option value="">Type</option>
        <option value="notes">Notes</option>
        <option value="past_paper">Past Paper</option>
        <option value="tutorial">Tutorial</option>
        <option value="cheatsheet">Cheat Sheet</option>
      </select>
      <button class="bg-indigo-600 text-white px-4 py-2 rounded">Filter</button>
      <button class="bg-indigo-600 text-white px-4 py-2 rounded"><a href="Upload.php">Upload</a></button>
    </form>
  </div>

  <!-- Resource Grid -->
  <div class="max-w-7xl mx-auto px-4 pb-8">
    <div class="grid gap-6 sm:grid-cols-2 md:grid-cols-3">
      <?php while ($row = $result->fetch_assoc()): ?>
        
          <div class="bg-white border rounded-lg shadow hover:shadow-md transition p-4 flex flex-col cursor-pointer">
            <a href="resource.php?id=<?php echo $row['id']; ?>">
            <div class="text-xs text-gray-500 mb-1">
              <?php echo htmlspecialchars($row['code']); ?> •
              <?php echo htmlspecialchars($row['session']); ?> •
              <?php echo htmlspecialchars($row['type']); ?>
            </div>
            <h2 class="font-semibold text-lg line-clamp-2">
              <?php echo htmlspecialchars($row['title']); ?>
            </h2>
            <p class="text-sm text-gray-600 mt-1 flex-grow">
              <?php echo htmlspecialchars(substr($row['detail'], 0, 60)); ?>...
            </p>
        </a>
        <div class="mt-3 flex justify-between text-xs text-gray-500">
          <span>By <?php echo htmlspecialchars($row['author']); ?></span>
          <button class="like-btn <?= $row['liked'] ? 'text-red-500' : 'text-gray-500' ?>"
        data-id="<?= $row['id'] ?>"
        aria-pressed="<?= $row['liked'] ? 'true' : 'false' ?>">
    ❤ <span class="like-count"><?= $row['likes'] ?></span>
</button>

        </div>
    </div>
  <?php endwhile; ?>
  </div>
  </div>

  <!-- Pagination -->
  <div class="max-w-7xl mx-auto px-4 pb-8">
    <div class="flex justify-center gap-2">
      <a href="#" class="px-3 py-1 border rounded bg-indigo-600 text-white">1</a>
      <a href="#" class="px-3 py-1 border rounded hover:bg-gray-100">2</a>
      <a href="#" class="px-3 py-1 border rounded hover:bg-gray-100">3</a>
    </div>
  </div>
  <!-- Footer -->
  <?php include 'footer.php' ?>
</body>

</html>

<script>
document.addEventListener('click', async function (e) {
    const btn = e.target.closest('.like-btn');
    if (!btn) return;

    const resourceId = btn.dataset.id;

    try {
        const res = await fetch('toggle_like.php', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `resource_id=${resourceId}`
        });

        const data = await res.json();
        if (data && typeof data.liked !== 'undefined') {
            btn.classList.toggle('text-red-500', data.liked);
            btn.classList.toggle('text-gray-500', !data.liked);
            btn.setAttribute('aria-pressed', data.liked);
            btn.querySelector('.like-count').textContent = data.count;
        }
    } catch (err) {
        alert("Failed to update like.");
        console.error(err);
    }
});
</script>
