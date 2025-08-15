<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
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
    </form>
  </div>

  <!-- Resource Grid -->
  <div class="max-w-7xl mx-auto px-4 pb-8">
    <div class="grid gap-6 sm:grid-cols-2 md:grid-cols-3">
      
      <!-- Card -->
      <div class="bg-white border rounded-lg shadow hover:shadow-md transition p-4 flex flex-col">
        <div class="text-xs text-gray-500 mb-1">TSE3042 • 2023/2024 • Notes</div>
        <h2 class="font-semibold text-lg line-clamp-2">Midterm Revision Notes</h2>
        <p class="text-sm text-gray-600 mt-1 flex-grow">Concise revision notes for Software Engineering midterm exam...</p>
        <div class="mt-3 flex justify-between text-xs text-gray-500">
          <span>By John Doe</span>
          <span>❤️ 12</span>
        </div>
      </div>

      <!-- Repeat cards for demo -->
      <div class="bg-white border rounded-lg shadow hover:shadow-md transition p-4 flex flex-col">
        <div class="text-xs text-gray-500 mb-1">ICT205 • 2022/2023 • Past Paper</div>
        <h2 class="font-semibold text-lg line-clamp-2">Final Exam Paper 2023</h2>
        <p class="text-sm text-gray-600 mt-1 flex-grow">Past year final exam with solutions for reference...</p>
        <div class="mt-3 flex justify-between text-xs text-gray-500">
          <span>By Jane Lee</span>
          <span>❤️ 25</span>
        </div>
      </div>

      <div class="bg-white border rounded-lg shadow hover:shadow-md transition p-4 flex flex-col">
        <div class="text-xs text-gray-500 mb-1">MAT101 • 2023/2024 • Tutorial</div>
        <h2 class="font-semibold text-lg line-clamp-2">Tutorial Set 5 with Answers</h2>
        <p class="text-sm text-gray-600 mt-1 flex-grow">Fully worked solutions for calculus tutorial questions...</p>
        <div class="mt-3 flex justify-between text-xs text-gray-500">
          <span>By Alan Turing</span>
          <span>❤️ 8</span>
        </div>
      </div>

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
 <!-- Header -->
  <?php include 'footer.php' ?>
</body>
</html>
