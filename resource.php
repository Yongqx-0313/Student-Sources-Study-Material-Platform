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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
</head>

<body style="background: linear-gradient(to right, #c6defe, #ffffff);" class=" text-gray-900">

    <!-- Header -->
    <?php include 'header.php' ?>

    <!-- Back Button -->
    <button class=" ml-4 mt-3 bg-indigo-600 text-white px-4 py-2 rounded hover:bg-black/10">
        <a href="Main.php">
            <span><i class="fa-solid fa-angle-left"></i></span> Back
        </a>
    </button>

    <main class="mx-auto max-w-6xl px-4 py-6">


        <!-- Resource Details -->
        <div class="max-w-3xl mx-auto px-4 py-8 mb-6 bg-white shadow rounded-lg">
            <h1 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($title); ?></h1>
            <div class="text-gray-700 text-lg mb-4">
                <p id="detail" class="line-clamp-2">
                    <?php echo htmlspecialchars($detail); ?>
                </p>
                <button id="toggleBtn" class="text-blue-500 hover:underline">Show more</button>
            </div>

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
            <!-- AI Summary / Study Plan -->
            <?php if (!empty($pdf_file)): ?>
                <!-- AI Summary / Study Plan -->
                <div class="max-w-3xl mx-auto px-4 py-6 bg-gray-200 shadow rounded-lg mt-4">


                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-semibold">AI Study Notes</h2>
                        <!-- Toggle Button -->
                        <button id="toggleBtn2" class="text-gray-600 hover:text-gray-900">
                            <i id="toggleIcon2" class="fas fa-angle-down"></i>
                        </button>
                    </div>
                    <div id="toggleContent" class="mt-3">
                        <label class="block text-md font-medium text-slate-700"><b>Your Gemini API Key</b></label>
                        <div class="flex">
                            <p>Don’t have a key?</p>
                            <button id="openApiInfo"
                                class="ml-2 text-sm text-blue-600 underline hover:text-blue-800">
                                Click here
                            </button>
                        </div>

                        <input id="geminiKey" type="password"
                            placeholder="Paste your Gemini API key"
                            class="mt-1 w-full rounded border px-3 py-2 text-sm mb-3" />

                        <div class="flex gap-2">
                            <button id="btnSummarize"
                                class="inline-flex items-center rounded bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                                Generate Study Notes
                            </button>
                            <button id="btnDownload" disabled
                                class="inline-flex items-center rounded bg-slate-600 px-4 py-2 text-sm font-semibold text-white/90 hover:bg-slate-700">
                                Download Notes (.txt)
                            </button>
                        </div>

                        <textarea id="aiOutput" rows="14"
                            placeholder="Your AI summary and study plan will appear here…"
                            class="mt-3 w-full rounded border px-3 py-2 text-sm resize-none"></textarea>
                    </div>
                <?php endif; ?>
                </div>

        </div>

        <!-- Modal Background -->
        <div id="apiInfoModal"
            class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
            <!-- Modal Content -->
            <div class="bg-white rounded-lg shadow-lg max-w-md p-8 relative flex flex-col items-center">
                <!-- Close btn -->
                <button id="closeApiInfo"
                    class="absolute top-3 right-3 text-slate-500 hover:text-black text-xl">
                    ✕
                </button>

                <!-- Title -->
                <h2 class="text-lg font-bold flex items-center gap-2 mb-2">
                    <span class="text-blue-600">🔑</span> Get Your API Key
                </h2>

                <p class="mb-3">Get your free Gemini API key:</p>

                <!-- Link Btn -->
                <a href="https://aistudio.google.com/app/apikey" target="_blank"
                    class="inline-flex items-center text-blue-600 hover:underline mb-4">
                    🔗 AI Studio
                </a>

                <!-- Step -->
                <ol class="list-decimal list-inside space-y-1 text-sm text-slate-700">
                    <li>Visit AI Studio</li>
                    <li>Sign in with Google account</li>
                    <li>Click <b>"Get API key"</b></li>
                    <li>Copy and paste below</li>
                </ol>
            </div>
        </div>

        <!-- Comment Section -->
        <div class="max-w-3xl mx-auto px-4 py-8 bg-white shadow rounded-lg">
            <h2 class="text-xl font-semibold mb-4">Comments</h2>

            <!-- Comment Form -->
            <form method="POST" class="flex mb-4">
                <input type="text" name="comment" placeholder="Type your comment..."
                    class="flex-grow border border-gray-300 rounded-l-lg px-2 py-2 text-sm md:text-md md:px-3 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                    required>
                <button type="submit" class="bg-indigo-600 text-white px-2 py-2 rounded-r-lg text-sm md:text-md md:px-3 hover:bg-indigo-700">
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
<script src="js/resource.js"></script>
<script>
    (function() {
        const id = <?= (int)$id ?>; // current resource id from PHP
        const btnSummarize = document.getElementById('btnSummarize');
        const btnDownload = document.getElementById('btnDownload');
        const aiOutput = document.getElementById('aiOutput');
        const keyInput = document.getElementById('geminiKey');

        // Optional: remember key in sessionStorage
        keyInput.value = sessionStorage.getItem('gemini_api_key') || '';
        keyInput.addEventListener('change', () => {
            sessionStorage.setItem('gemini_api_key', keyInput.value.trim());
        });

        btnSummarize.addEventListener('click', async () => {
            const apiKey = (keyInput.value || '').trim();
            if (!apiKey) {
                alert('Please paste your Gemini API key first.');
                return;
            }

            btnSummarize.disabled = true;
            btnSummarize.textContent = 'Generating…';
            aiOutput.value = '';

            try {
                // 1) get plain text from the uploaded file
                const extr = await fetch(`extract_text.php?id=${id}`);
                const ejson = await extr.json();
                if (!ejson.ok) {
                    throw new Error(ejson.error || 'Failed to extract file text');
                }

                // 2) ask Gemini to summarize + plan
                const prompt =
                    `You are a helpful tutor. Create concise STUDY NOTES and a 7-day STUDY PLAN from this content.

Return markdown with these sections:

# Key Summary (bullets)
# Important Concepts (bullets)
# Common Mistakes (bullets)
# Practice Questions (numbered)
# 7-Day Study Plan (table: Day | Topics | Tasks | Time)

CONTENT START
${ejson.text}
CONTENT END`;

                // Pick one:
                const MODEL = 'gemini-1.5-flash'; // fast & cheap
                // const MODEL = 'gemini-1.5-pro';    // higher quality / longer context

                const url = `https://generativelanguage.googleapis.com/v1/models/${MODEL}:generateContent?key=${encodeURIComponent(apiKey)}`;

                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        contents: [{
                            parts: [{
                                text: prompt
                            }]
                        }]
                    })
                });


                if (!res.ok) {
                    const errText = await res.text();
                    throw new Error('Gemini HTTP ' + res.status + ' — ' + errText);
                }

                const data = await res.json();
                const text = data?.candidates?.[0]?.content?.parts?.[0]?.text || '(No content returned)';
                aiOutput.value = text;
                btnDownload.disabled = false;
            } catch (err) {
                console.error(err);
                alert('Failed: ' + err.message);
            } finally {
                btnSummarize.disabled = false;
                btnSummarize.textContent = 'Generate Study Notes';
            }
        });

        btnDownload.addEventListener('click', () => {
            const blob = new Blob([aiOutput.value], {
                type: 'text/plain'
            });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'study_notes.txt';
            a.click();
            URL.revokeObjectURL(url);
        });
    })();
</script>

<script>
    const modal = document.getElementById('apiInfoModal');
    const openBtn = document.getElementById('openApiInfo');
    const closeBtn = document.getElementById('closeApiInfo');

    openBtn.addEventListener('click', () => {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    });
    closeBtn.addEventListener('click', () => {
        modal.classList.add('hidden');
    });
    // 点击背景关闭
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.add('hidden');
        }
    });
</script>