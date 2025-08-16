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

        <form action="api/upload_material.php" method="POST" enctype="multipart/form-data"
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
                </div>

                <!-- RIGHT: fields -->
                <div>
                    <!-- Subject Code -->
                    <label for="course_code" class="mb-1 block text-sm font-medium text-slate-700">Subject Code</label>
                    <input
                        id="course_code" name="course_code" type="text" maxlength="40" required
                        placeholder="e.g., TSE3042"
                        class="mb-4 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none ring-0 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20" />

                    <!-- Session -->
                    <label for="course_code" class="mb-1 block text-sm font-medium text-slate-700">Session</label>
                    <select class="border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200 mb-3">
                        <option value="">Session</option>
                        <option value="notes">2019/2020</option>
                        <option value="past_paper">2020/2021</option>
                        <option value="tutorial">2021/2022</option>
                        <option value="cheatsheet">2022/2023</option>
                        <option value="cheatsheet">2023/2024</option>
                    </select>

                    <!-- Type -->
                    <label for="course_code" class="mb-1 block text-sm font-medium text-slate-700">Type</label>
                    <select class="border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200 mb-3">
                        <option value="">-</option>
                        <option value="notes">Notes</option>
                        <option value="past_paper">Past Paper</option>
                        <option value="tutorial">Tutorial</option>
                        <option value="cheatsheet">Cheat Sheet</option>
                    </select>

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