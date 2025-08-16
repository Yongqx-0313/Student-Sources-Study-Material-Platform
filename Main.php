<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "sssmp");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Fetch resources
$sql = "
  SELECT r.id, r.code, r.session, r.type, r.title, r.detail, r.likes, u.Name AS author
  FROM resources r
  JOIN user u ON r.created_by = u.UserID
  WHERE r.visibility = 'public'
  ORDER BY r.id DESC
";

$result = $conn->query($sql);
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
        <a href="resource.php?id=<?php echo $row['id']; ?>" class="block">
          <div class="bg-white border rounded-lg shadow hover:shadow-md transition p-4 flex flex-col cursor-pointer">
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
            <div class="mt-3 flex justify-between text-xs text-gray-500">
              <span>By <?php echo htmlspecialchars($row['author']); ?></span>
              <span>❤ <?php echo $row['likes']; ?></span>
            </div>
          </div>
        </a>
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