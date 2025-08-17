<?php
session_start();

// Check login
if (!isset($_SESSION['user'])) {
    header("Location: ../Log In.html");
    exit();
}

$user = $_SESSION['user'];
$userID = $user['UserID'];

// DB connection
$conn = new mysqli("localhost", "root", "", "sssmp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get resource ID
if (!isset($_GET['id'])) {
    die("No resource ID provided.");
}
$resourceID = intval($_GET['id']);

// Fetch resource data
$sql = "SELECT * FROM resources WHERE id = ? AND created_by = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $resourceID, $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Resource not found or you don’t have permission to edit it.");
}
$resource = $result->fetch_assoc();
$stmt->close();

// Handle update form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['course_code'];
    $session = $_POST['session'];
    $type = $_POST['type'];
    $title = $_POST['title'];
    $detail = $_POST['description'];
    $visibility = $_POST['visibility'] === 'private' ? 'private' : 'public';

    // Update SQL (file update optional)
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $allowedExt = ['pdf', 'doc', 'docx'];
        $tmpPath = $_FILES['file']['tmp_name'];
        $fileName = $_FILES['file']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExt, $allowedExt)) {
            die("Invalid file type.");
        }

        $uploadDir = __DIR__ . '/uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $safeFileName = preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($fileName, PATHINFO_FILENAME));
        $newFileName = $safeFileName . '_' . time() . '.' . $fileExt;
        $destination = $uploadDir . '/' . $newFileName;

        if (!move_uploaded_file($tmpPath, $destination)) {
            die("Failed to upload file.");
        }

        $dbFilePath = 'uploads/' . $newFileName;

        $updateSQL = "UPDATE resources 
                      SET code=?, session=?, type=?, title=?, detail=?, pdf_file=?, visibility=? 
                      WHERE id=? AND created_by=?";
        $stmt = $conn->prepare($updateSQL);
        $stmt->bind_param("sssssssii", $code, $session, $type, $title, $detail, $dbFilePath, $visibility, $resourceID, $userID);
    } else {
        $updateSQL = "UPDATE resources 
                      SET code=?, session=?, type=?, title=?, detail=?, visibility=? 
                      WHERE id=? AND created_by=?";
        $stmt = $conn->prepare($updateSQL);
        $stmt->bind_param("ssssssii", $code, $session, $type, $title, $detail, $visibility, $resourceID, $userID);
    }

    if ($stmt->execute()) {
        header("Location: profile.php?updated=1");
        exit();
    } else {
        echo "Update failed: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Study Material</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../src/main.css">
    <link rel="stylesheet" href="css/profile.css">
</head>

<body style="background: linear-gradient(to right, #c6defe, #ffffff);" class="min-h-screen flex flex-col text-gray-900">

    <?php include 'header.php'; ?>

    <main class="flex-1 flex flex-col align-center justify-center mx-auto max-w-6xl px-4 py-6">
        <h1 class="mb-4 text-2xl font-semibold">Edit Study Material</h1>

        <form action="" method="POST" enctype="multipart/form-data" class="rounded-2xl bg-white p-6 shadow-xl w-auto lg:min-w-[994px]">
            <div class="grid gap-6 md:grid-cols-[290px,1fr]">

                <!-- LEFT: File + Visibility -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Replace file (optional)</label>
                    <input type="file" name="file"
                        class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm 
                            file:mr-4 file:rounded-md file:border-0 file:bg-slate-800 file:px-3 file:py-2 
                            file:text-sm file:font-semibold file:text-white hover:file:bg-slate-700"
                        accept=".pdf,.doc,.docx" />

                    <?php if (!empty($resource['pdf_file'])): ?>
                                            <?php $fileName = basename($resource['pdf_file']); ?>
                                            <p class="mt-2 text-xs text-slate-500">
                                                Current file:
                                                <a href="<?php echo htmlspecialchars($resource['pdf_file']); ?>" target="_blank" class="text-blue-600 underline truncate line-clamp-1">
                                                    <?php echo htmlspecialchars($fileName); ?>
                                                </a>
                                            </p>
                                        <?php else: ?>
                                            <p class="mt-2 text-xs text-slate-400">No file uploaded.</p>
                                        <?php endif; ?>


                    <label class="mt-4 block text-sm font-medium text-slate-700">Visibility</label>
                    <div class="mt-2 flex gap-2">
                        <label class="flex-1">
                            <input type="radio" name="visibility" value="public" class="peer sr-only" <?php echo ($resource['visibility'] === 'public') ? 'checked' : ''; ?> />
                            <div
                                class="w-full cursor-pointer rounded-full border px-3 py-2 text-center text-sm font-semibold text-emerald-800 peer-checked:border-emerald-400 peer-checked:bg-emerald-50">
                                Public</div>
                        </label>
                        <label class="flex-1">
                            <input type="radio" name="visibility" value="private" class="peer sr-only" <?php echo ($resource['visibility'] === 'private') ? 'checked' : ''; ?> />
                            <div
                                class="w-full cursor-pointer rounded-full border px-3 py-2 text-center text-sm font-semibold text-rose-800 peer-checked:border-rose-400 peer-checked:bg-rose-50">
                                Private</div>
                        </label>
                    </div>
                </div>

                <!-- RIGHT: Form fields -->
                <div>
                    <label>Subject Code</label>
                    <input type="text" name="course_code" value="<?php echo htmlspecialchars($resource['code']); ?>"
                        required class="mb-4 w-full rounded-xl border px-3 py-2" />

                    <div class="flex gap-3">
                        <div>
                            <label>Session</label>
                            <select name="session" class="border rounded px-3 py-2">
                                <option value=""></option>
                                <?php foreach (["2019/2020", "2020/2021", "2021/2022", "2022/2023", "2023/2024"] as $s): ?>
                                    <option value="<?php echo $s; ?>" <?php echo ($resource['session'] === $s) ? 'selected' : ''; ?>><?php echo $s; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Type</label>
                            <select name="type" class="border rounded px-3 py-2">
                                <option value=""></option>
                                <?php foreach (["Notes", "Past Paper", "Tutorial", "Cheat Sheet"] as $t): ?>
                                    <option value="<?php echo $t; ?>" <?php echo ($resource['type'] === $t) ? 'selected' : ''; ?>><?php echo $t; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <label>Title</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($resource['title']); ?>" required
                        class="mb-4 w-full rounded-xl border px-3 py-2" />

                    <label>Description</label>
                    <textarea name="description"
                        class="min-h-[140px] w-full rounded-xl border px-3 py-2"><?php echo htmlspecialchars($resource['detail']); ?></textarea>

                    <div class="mt-4 flex justify-end gap-3">
                        <a href="profile.php" class="px-4 py-2 rounded-xl bg-gray-200">Cancel</a>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white">Update</button>
                    </div>
                </div>
            </div>
        </form>
    </main>

    <?php include 'footer.php'; ?>
</body>

</html>