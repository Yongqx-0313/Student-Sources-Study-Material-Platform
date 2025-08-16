<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: ../Log In.html"); // Redirect to login page if not logged in
    exit();
}

// Access user data from session
$user = $_SESSION['user'];
$adminID = $user['UserID']; // Logged-in UserID

// Database connection
$conn = new mysqli("localhost", "root", "", "sssmp");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: Fetch user's name (if needed)
$sql = "SELECT Name FROM user WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $adminID);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $name = $row['Name'];
} else {
    $name = "Unknown";
}
$stmt->close();

// ---------- Handle Upload ----------
if (
    isset($_POST['course_code'], $_POST['session'], $_POST['type'], $_POST['title'], $_POST['description'], $_POST['visibility']) &&
    isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK
) {
    $code       = $_POST['course_code'];
    $session    = $_POST['session'];
    $type       = $_POST['type'];
    $title      = $_POST['title'];
    $detail     = $_POST['description'];
    $visibility = $_POST['visibility'] === 'private' ? 'private' : 'public';

    // ---------- File Validation ----------
    $allowedExt  = ['pdf', 'doc', 'docx'];
    $allowedMime = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];

    $tmpPath = $_FILES['file']['tmp_name'];
    $fileName = $_FILES['file']['name'];
    $fileSize = $_FILES['file']['size'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $fileMime = mime_content_type($tmpPath);

    if (!in_array($fileExt, $allowedExt) || !in_array($fileMime, $allowedMime)) {
        die("Invalid file type.");
    }

    if ($fileSize > 20 * 1024 * 1024) {
        die("File too large (20MB max).");
    }

    // ---------- Save File ----------
    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $safeFileName = preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($fileName, PATHINFO_FILENAME));
    $newFileName = $safeFileName . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExt;
    $destination = $uploadDir . '/' . $newFileName;

    if (!move_uploaded_file($tmpPath, $destination)) {
        die("Failed to move uploaded file.");
    }

    $dbFilePath = 'uploads/' . $newFileName;

    // ---------- Insert into DB ----------
    $insertSQL = "INSERT INTO resources (code, session, type, title, detail, pdf_file, visibility)
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertSQL);
    $insertStmt->bind_param("sssssss", $code, $session, $type, $title, $detail, $dbFilePath, $visibility);

    if ($insertStmt->execute()) {
        header("Location: Main.php?uploaded=1");
        exit();
    } else {
        echo "Insert failed: " . $insertStmt->error;
    }

    $insertStmt->close();
}

$conn->close();
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
    <button
        class=" ml-8 mt-3 bg-indigo-600 text-white px-4 py-2 rounded hover:bg-black/10">
        <a href="Main.php" class="">
            <span>←</span> Back
        </a></button>

    <main class="mx-auto max-w-6xl px-4 py-6">
        <h1 class="mb-4 text-2xl font-semibold">Upload Study Material</h1>

        <form action="Upload.php" method="POST" enctype="multipart/form-data"
            class="rounded-2xl bg-white p-6 shadow-xl">
            <!-- Grid -->
            <div class="grid gap-6 md:grid-cols-[290px,1fr]">
                <!-- LEFT: file + visibility -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Upload file</label>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <input
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm file:mr-4 file:rounded-md file:border-0 file:bg-slate-800 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-slate-700"
                            type="file" name="file"
                            accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                            required />
                        <p class="mt-2 text-xs text-slate-500">PDF or DOC/DOCX • Max 20 MB</p>

                        <label class="mt-4 block text-sm font-medium text-slate-700">Visibility</label>

                        <!-- Radio chips using peer -->
                        <div class="mt-2 flex gap-2" role="radiogroup" aria-label="Visibility">
                            <!-- Public -->
                            <label class="flex-1">
                                <input type="radio" name="visibility" value="public" class="peer sr-only" checked />
                                <div class="w-full cursor-pointer rounded-full border border-slate-200 bg-white px-3 py-2 text-center text-sm font-semibold text-emerald-800
                            peer-checked:border-emerald-400 peer-checked:bg-emerald-50">
                                    Public
                                </div>
                            </label>
                            <!-- Private -->
                            <label class="flex-1">
                                <input type="radio" name="visibility" value="private" class="peer sr-only" />
                                <div class="w-full cursor-pointer rounded-full border border-slate-200 bg-white px-3 py-2 text-center text-sm font-semibold text-rose-800
                            peer-checked:border-rose-400 peer-checked:bg-rose-50">
                                    Private
                                </div>
                            </label>
                        </div>
                        <p class="mt-2 text-xs text-slate-500">
                            Public = shown on main page • Private = only on your profile
                        </p>
                    </div>
                    <br />
                    <p class="text-lg"><strong>Author: </strong><?php echo ($name); ?></p>
                </div>

                <!-- RIGHT: fields -->
                <div>
                    <!-- Subject Code -->
                    <label for="course_code" class="mb-1 block text-sm font-medium text-slate-700">Subject Code</label>
                    <input
                        id="course_code" name="course_code" type="text" maxlength="40" required
                        placeholder="e.g., TSE3042"
                        class="mb-4 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none ring-0 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20" />

                    <div class="flex">
                        <!-- Session -->
                         <div class="flex flex-col mr-3">
                        <label for="course_code" class="mb-1 block text-sm font-medium text-slate-700">Session</label>
                        <select name="session" class="border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200 mb-3">
                            <option value=""></option>
                            <option value="2019/2020">2019/2020</option>
                            <option value="2020/2021">2020/2021</option>
                            <option value="2021/2022">2021/2022</option>
                            <option value="2022/2023">2022/2023</option>
                            <option value="2023/2024">2023/2024</option>
                        </select>
                         </div>

                        <!-- Type -->
                        <div class="flex flex-col">
                        <label for="course_code" class="mb-1 block text-sm font-medium text-slate-700">Type</label>
                        <select name="type" class="border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200 mb-3">
                            <option value=""></option>
                            <option value="Notes">Notes</option>
                            <option value="Past Paper">Past Paper</option>
                            <option value="Tutorial">Tutorial</option>
                            <option value="Cheat Sheet">Cheat Sheet</option>
                        </select>
                        </div>
                    </div>


                    <!-- Title -->
                    <label for="title" class="mb-1 block text-sm font-medium text-slate-700" required>Title</label>
                    <input
                        id="title" name="title" type="text" maxlength="200" required
                        placeholder="e.g., Midterm Revision Notes"
                        class="mb-4 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20" />

                    <!-- Detail -->
                    <label for="description" class="mb-1 block text-sm font-medium text-slate-700">Detail</label>
                    <textarea
                        id="description" name="description" required
                        placeholder="Briefly describe what this file contains (chapters, topics, solutions included, etc.)"
                        class="min-h-[140px] w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20"></textarea>

                    <!-- Actions -->
                    <div class="mt-4 flex items-center justify-end gap-3">
                        <a href="Main.php"
                            class="inline-flex items-center rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-200">
                            Cancel
                        </a>
                        <button type="submit"
                            class="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/30">
                            Upload Material
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </main>
    <!-- Footer -->
    <?php include 'footer.php' ?>
</body>

</html>