<?php
session_start();
// DB
$conn = new mysqli("localhost", "root", "", "sssmp");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

// Read filters from the URL
$q     = trim($_GET['q']    ?? '');   // keyword
$code  = trim($_GET['code'] ?? '');   // subject code
$type  = trim($_GET['type'] ?? '');   // Notes | Past Paper | Tutorial | Cheat Sheet

// This should come from your session or login logic
$userID = $_SESSION['userID'] ?? 0;  // fallback to 0 if not logged in

// Base SELECT with liked subquery
$sqlBase = "SELECT
              r.`id`, r.`code`, r.`session`, r.`type`,
              r.`title`, r.`detail`, r.`likes`,
              COALESCE(u.`Name`, 'Anonymous') AS author,
              (SELECT 1 FROM resource_likes WHERE user_id = ? AND resource_id = r.id LIMIT 1) AS liked,
              (SELECT 1 FROM collected WHERE user_id = ? AND resource_id = r.id LIMIT 1) AS collected
            FROM `resources` AS r
            LEFT JOIN `user` AS u ON u.`UserID` = r.`created_by`";


// Conditions and params
$conds   = ["r.`visibility` = 'public'"];
$params  = [$userID, $userID]; // userID must be the first param due to liked subquery
$typestr = "ii";        // i = integer for userID

// Keyword search in title/detail/code
if ($q !== "") {
  $conds[] = "(r.`title` LIKE ? OR r.`detail` LIKE ? OR r.`code` LIKE ?)";
  $like = "%{$q}%";
  $params[] = $like;
  $params[] = $like;
  $params[] = $like;
  $typestr .= "sss";
}

// Filter by subject code
if ($code !== "") {
  $conds[] = "r.`code` LIKE ?";
  $params[] = "%{$code}%";
  $typestr .= "s";
}

// Filter by type
if ($type !== "") {
  $conds[] = "r.`type` = ?";
  $params[] = $type;
  $typestr .= "s";
}

$sql = $sqlBase . " WHERE " . implode(" AND ", $conds) . " ORDER BY r.`id` DESC LIMIT 60";

// Prepare and execute
$stmt = $conn->prepare($sql) or die("SQL prepare error: " . $conn->error);
if ($params) {
  $stmt->bind_param($typestr, ...$params);
}
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
</head>
<script>
  document.addEventListener('click', async function(e) {
    const btn = e.target.closest('.collect-btn');
    if (!btn) return;

    const resourceId = btn.dataset.id;
    console.log("Star clicked: ", resourceId);

    try {
      const res = await fetch('toggle_collect.php', {
        method: 'POST',
        credentials: 'same-origin', // 🔥 THIS IS THE FIX!
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `resource_id=${resourceId}`
      });


      if (!res.ok) throw new Error("HTTP error: " + res.status);

      const data = await res.json();
      console.log("Collect response:", data); // ✅ See if it’s working

      if (data && typeof data.collected !== 'undefined') {
        btn.classList.toggle('text-yellow-400', data.collected);
        btn.classList.toggle('text-gray-400', !data.collected);
        btn.setAttribute('aria-pressed', data.collected);
      } else {
        console.warn("Unexpected data:", data);
      }
    } catch (err) {
      alert("Failed to toggle collect.");
      console.error("Collect error:", err);
    }
  });
</script>


<body style="background: linear-gradient(to right, #c6defe, #ffffff);" class=" text-gray-900">


  <!-- Header -->
  <?php include 'header.php' ?>
  <!-- Search & Filters -->
  <div class="max-w-7xl mx-auto px-4 py-6">
    <form method="get" action="Main.php" class="bg-white rounded-lg shadow p-4 flex flex-wrap gap-3">

      <!-- Keyword -->
      <input name="q" value="<?php echo htmlspecialchars($q); ?>"
        type="text" placeholder="Search title or description..."
        class="flex-1 border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200" />

      <!-- Subject code -->
      <input name="code" value="<?php echo htmlspecialchars($code); ?>"
        type="text" placeholder="Subject Code"
        class="w-40 border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200" />

      <!-- Type -->
      <select name="type" class="border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200">
        <option value="">Type</option>
        <option value="Notes" <?php if ($type === 'Notes')       echo 'selected'; ?>>Notes</option>
        <option value="Past Paper" <?php if ($type === 'Past Paper')  echo 'selected'; ?>>Past Paper</option>
        <option value="Tutorial" <?php if ($type === 'Tutorial')    echo 'selected'; ?>>Tutorial</option>
        <option value="Cheat Sheet" <?php if ($type === 'Cheat Sheet') echo 'selected'; ?>>Cheat Sheet</option>
      </select>

      <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">Filter</button>
      <a href="Main.php" class="px-4 py-2 rounded border">Clear</a>
      <a href="Upload.php" class="bg-indigo-600 text-white px-4 py-2 rounded">Upload</a>
    </form>
  </div>

  <!-- Resource Grid -->
  <div class="max-w-7xl mx-auto px-4 pb-8">
    <div class="grid gap-6 sm:grid-cols-2 md:grid-cols-3">
      <?php while ($row = $result->fetch_assoc()): ?>

        <div class="bg-white border rounded-lg shadow hover:shadow-md transition p-4 flex flex-col cursor-pointer">

          <div class="text-xs text-gray-500 mb-1 flex justify-between">
            <div>
              <?php echo htmlspecialchars($row['code']); ?> •
              <?php echo htmlspecialchars($row['session']); ?> •
              <?php echo htmlspecialchars($row['type']); ?>
            </div>
            <div> <button class="collect-btn <?= $row['collected'] ? 'text-yellow-400' : 'text-gray-400' ?>"
                data-id="<?= $row['id'] ?>"
                aria-pressed="<?= $row['collected'] ? 'true' : 'false' ?>">
                <i class="fa-star fa-regular"></i>
              </button>
            </div>
          </div>
          <a href="resource.php?id=<?php echo $row['id']; ?>">
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
  document.addEventListener('click', async function(e) {
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