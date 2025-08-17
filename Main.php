<?php
session_start();

/* ---------- DB ---------- */
$conn = new mysqli("localhost", "root", "", "sssmp");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

/* ---------- Filters from URL ---------- */
$q = trim($_GET['q'] ?? '');   // keyword
$code = trim($_GET['code'] ?? '');   // subject code
$type = trim($_GET['type'] ?? '');   // Notes | Past Paper | Tutorial | Cheat Sheet
$sessionFilter = trim($_GET['session'] ?? '');   // Sessions from 2019/2020 until 2023/2024

/* ---------- Pagination (Next always works) ---------- */
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 6;                           // change page size here
$offset = ($page - 1) * $perPage;

/* ---------- Current user (for liked status) ---------- */
$userID = $_SESSION['user']['UserID'] ?? 0;     // make sure your login sets this

/* ---------- Base SELECT with JOIN + liked subquery ---------- */
$sqlBase = "SELECT
              r.`id`, r.`code`, r.`session`, r.`type`,
              r.`title`, r.`detail`, r.`likes`,
              COALESCE(u.`Name`, 'Anonymous') AS author,
              (SELECT 1 FROM resource_likes rl
                 WHERE rl.user_id = ? AND rl.resource_id = r.id
                 LIMIT 1) AS liked,
              (SELECT 1 FROM collected c
                 WHERE c.user_id = ? AND c.resource_id = r.id
                 LIMIT 1) AS collected
            FROM `resources` AS r
            LEFT JOIN `user` AS u ON u.`UserID` = r.`created_by`";


/* ---------- Build WHERE + params (we keep a 2nd set for COUNT) ---------- */
$conds = ["r.`visibility` = 'public'"];
$params = [$userID, $userID];
$types = "ii";   // first param is userID for liked subquery
$countParams = [];
$countTypes = "";    // COUNT(*) doesn't need userID

// keyword search (title/detail/code)
if ($q !== "") {
  $conds[] = "(r.`title` LIKE ? OR r.`detail` LIKE ? OR r.`code` LIKE ?)";
  $like = "%{$q}%";
  array_push($params, $like, $like, $like);
  array_push($countParams, $like, $like, $like);
  $types .= "sss";
  $countTypes .= "sss";
}
// subject code (contains)
if ($code !== "") {
  $conds[] = "r.`code` LIKE ?";
  $v = "%{$code}%";
  $params[] = $v;
  $types .= "s";
  $countParams[] = $v;
  $countTypes .= "s";
}
// type (exact)
if ($type !== "") {
  $conds[] = "r.`type` = ?";
  $params[] = $type;
  $types .= "s";
  $countParams[] = $type;
  $countTypes .= "s";
}
// session (exact)
if ($sessionFilter !== "") {
  $conds[] = "r.`session` = ?";
  $params[] = $sessionFilter;
  $types .= "s";
  $countParams[] = $sessionFilter;
  $countTypes .= "s";
}

$whereSql = " WHERE " . implode(" AND ", $conds);

/* ---------- 1) Count total rows for page numbers ---------- */
$countSql = "SELECT COUNT(*) AS cnt FROM `resources` r" . $whereSql;
$countStmt = $conn->prepare($countSql) or die("COUNT prepare error: " . $conn->error);
if ($countParams) {
  $countStmt->bind_param($countTypes, ...$countParams);
}
$countStmt->execute();
$totalRows = (int) ($countStmt->get_result()->fetch_assoc()['cnt'] ?? 0);
$totalPages = max(1, (int) ceil($totalRows / $perPage));
// NOTE: we do NOT clamp $page down to $totalPages on purpose,
// so users can click Next into empty pages

/* ---------- 2) Fetch current page (may be empty if beyond last page) ---------- */
$sql = $sqlBase . $whereSql . " ORDER BY r.`id` DESC LIMIT {$perPage} OFFSET {$offset}";
$stmt = $conn->prepare($sql) or die("SQL prepare error: " . $conn->error);
if ($params) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

/* ---------- Helper: keep filters in pagination links ---------- */
function pageUrl($p)
{
  $qs = http_build_query([
    'q' => $_GET['q'] ?? '',
    'code' => $_GET['code'] ?? '',
    'type' => $_GET['type'] ?? '',
    'session' => $_GET['session'] ?? '',
    'page' => max(1, (int) $p),
  ]);
  return "Main.php?$qs";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Knowledge Hub</title>
  <link rel="stylesheet" href="css/profile.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
</head>

<body style="background: linear-gradient(to right, #c6defe, #ffffff);" class="min-h-screen flex flex-col text-gray-900">
  <!-- Header -->
  <?php include 'header.php' ?>
  <main class="flex-1 flex flex-col">
    <!-- Search & Filters -->
    <div class="mx-auto px-4 py-6 w-auto lg:w-[1275px]">
      <form method="get" action="Main.php" class="bg-white rounded-lg shadow p-4 flex flex-wrap gap-3 justify-between">
        <div>
          <!-- Keyword -->
          <input name="q" value="<?php echo htmlspecialchars($q); ?>" type="text"
            placeholder="Search title or description..."
            class="w-[280px] flex-1 border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200 mr-3" />

          <!-- Subject code -->
          <input name="code" value="<?php echo htmlspecialchars($code); ?>" type="text" placeholder="Subject Code"
            class="w-40 border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200 mr-3" />

          <!-- Type -->
          <select name="type" class="border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200 mr-3">
            <option value="">Type</option>
            <option value="Notes" <?php if ($type === 'Notes')
              echo 'selected'; ?>>Notes</option>
            <option value="Past Paper" <?php if ($type === 'Past Paper')
              echo 'selected'; ?>>Past Paper</option>
            <option value="Tutorial" <?php if ($type === 'Tutorial')
              echo 'selected'; ?>>Tutorial</option>
            <option value="Cheat Sheet" <?php if ($type === 'Cheat Sheet')
              echo 'selected'; ?>>Cheat Sheet</option>
          </select>

          <!-- Session -->
          <select name="session"
            class="border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200 mr-3">
            <option value="">Session</option>
            <option value="2019/2020" <?php if ($sessionFilter === '2019/2020')
              echo 'selected'; ?>>2019/2020</option>
            <option value="2020/2021" <?php if ($sessionFilter === '2020/2021')
              echo 'selected'; ?>>2020/2021</option>
            <option value="2021/2022" <?php if ($sessionFilter === '2021/2022')
              echo 'selected'; ?>>2021/2022</option>
            <option value="2022/2023" <?php if ($sessionFilter === '2022/2023')
              echo 'selected'; ?>>2022/2023</option>
            <option value="2023/2024" <?php if ($sessionFilter === '2023/2024')
              echo 'selected'; ?>>2023/2024</option>
          </select>

          <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded mr-3">Filter</button>
          <a href="Main.php" class="px-4 py-2 rounded border">Clear</a>
        </div>
        <div class="flex">
          <a href="Upload.php" class="bg-indigo-600 text-white px-4 py-2 rounded">Upload</a>
        </div>
      </form>
    </div>

    <!-- Resource Grid -->
    <div class="max-w-7xl mx-auto px-4 pb-8">
      <div class="grid gap-6 sm:grid-cols-2 md:grid-cols-3">
        <?php while ($row = $result->fetch_assoc()): ?>

          <div
            class="bg-white border rounded-lg shadow hover:shadow-md transition p-4 flex flex-col justify-between cursor-pointer">

            <div class="text-xs text-gray-500 mb-1 flex justify-between">
              <div>
                <?php echo htmlspecialchars($row['code']); ?> •
                <?php echo htmlspecialchars($row['session']); ?> •
                <?php echo htmlspecialchars($row['type']); ?>
              </div>
              <div>
                <button class="collect-btn <?= $row['collected'] ? 'text-yellow-400' : 'text-gray-400' ?>"
                  data-id="<?= $row['id'] ?>" aria-pressed="<?= $row['collected'] ? 'true' : 'false' ?>">
                  <i class="fa-star <?= $row['collected'] ? 'fa-solid' : 'fa-regular' ?>"></i>
                </button>
              </div>
            </div>
            <a class="hover:text-blue-500" href="resource.php?id=<?php echo $row['id']; ?>">
              <h2 class="font-semibold text-lg line-clamp-2">
                <?php echo htmlspecialchars($row['title']); ?>
              </h2>
            </a>
            <p class="text-sm text-gray-600 mt-1 flex-grow">
              <?php echo htmlspecialchars(substr($row['detail'], 0, 60)); ?>...
            </p>

            <div class="mt-3 flex justify-between text-xs text-gray-500">
              <span>By <?php echo htmlspecialchars($row['author']); ?></span>
              <button class="like-btn <?= $row['liked'] ? 'text-red-500' : 'text-gray-500' ?>" data-id="<?= $row['id'] ?>"
                aria-pressed="<?= $row['liked'] ? 'true' : 'false' ?>">
                ❤ <span class="like-count"><?= $row['likes'] ?></span>
              </button>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>

    <!-- Pagination -->
    <div class="max-w-7xl mx-auto px-4 pb-8 mt-auto">
      <div class="flex justify-center gap-2">
        <!-- Prev: always goes back, minimum page 1 -->
        <a href="<?php echo pageUrl(max(1, $page - 1)); ?>" class="px-3 py-1 border rounded hover:bg-gray-100">Prev</a>

        <!-- Page numbers (window up to 5 around current) -->
        <?php
        $start = max(1, $page - 2);
        $end = min($totalPages, $page + 2);
        if ($end - $start < 4) {
          $start = max(1, min($start, $end - 4));
          $end = min($totalPages, max($end, $start + 4));
        }
        for ($p = $start; $p <= $end; $p++):
          ?>
          <a href="<?php echo pageUrl($p); ?>" aria-current="<?php echo ($p == $page) ? 'page' : 'false'; ?>"
            class="px-3 py-1 border rounded <?php echo ($p == $page) ? 'bg-indigo-600 text-white' : 'hover:bg-gray-100'; ?>">
            <?php echo $p; ?>
          </a>
        <?php endfor; ?>

        <!-- Next: ALWAYS advances, even if the next page is empty -->
        <a href="<?php echo pageUrl($page + 1); ?>" class="px-3 py-1 border rounded hover:bg-gray-100">Next</a>
      </div>

      <p class="mt-3 text-center text-xs text-slate-500">
        Page <?php echo $page; ?> · <?php echo $totalRows; ?> result(s) · <?php echo $totalPages; ?> page(s)
      </p>
    </div>

    +
  </main>
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
<script>
  document.querySelectorAll('.collect-btn').forEach(button => {
    button.addEventListener('click', function () {
      const resourceId = this.dataset.id;
      const isCollected = this.getAttribute('aria-pressed') === 'true';

      fetch('toggle_collect.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `resource_id=${resourceId}&action=${isCollected ? 'uncollect' : 'collect'}`
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            const icon = this.querySelector('i');
            this.setAttribute('aria-pressed', data.collected ? 'true' : 'false');

            // Toggle color
            this.classList.toggle('text-yellow-400', data.collected);
            this.classList.toggle('text-gray-400', !data.collected);

            // Toggle icon style
            icon.classList.toggle('fa-solid', data.collected);
            icon.classList.toggle('fa-regular', !data.collected);
          } else {
            alert("Action failed: " + data.message);
          }
        }).catch(err => {
          console.error("AJAX error:", err);
        });
    });
  });
</script>