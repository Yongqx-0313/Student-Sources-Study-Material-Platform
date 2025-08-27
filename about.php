<?php
// about.php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>About • Knowledge Hub</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="css/profile.css">
</head>
<body style="background: linear-gradient(to right, #c6defe, #ffffff);" class="text-slate-900">

  <?php include 'header.php'; ?>

  <!-- Hero -->
  <section class="relative overflow-hidden">
    <div class="mx-auto max-w-6xl px-4 pt-10 pb-6">
      <div class="rounded-3xl bg-white/70 backdrop-blur shadow-xl p-8 md:p-12">
        <h1 class="text-3xl md:text-4xl font-bold tracking-tight">About Knowledge Hub</h1>
        <p class="mt-3 text-slate-600 leading-relaxed">
          Knowledge Hub is a student-sourced repository of study materials built by MMU students for students.
          We created it during a hackathon to make sharing notes, past papers, and tutorials simple, fast, and fair.
        </p>
      </div>
    </div>
  </section>

  <!-- Why / Goals -->
  <section class="mx-auto max-w-6xl px-4 py-6 grid md:grid-cols-2 gap-6">
    <div class="rounded-2xl bg-white shadow p-6">
      <h2 class="text-xl font-semibold">Why we built this</h2>
      <ul class="mt-3 space-y-2 text-slate-700">
        <li>• Past papers and official notes alone aren’t enough for deep understanding.</li>
        <li>• Great resources from seniors and peers are scattered across chats and drives.</li>
        <li>• New students struggle to quickly find reliable, course-specific materials.</li>
      </ul>
    </div>
    <div class="rounded-2xl bg-white shadow p-6">
      <h2 class="text-xl font-semibold">Our goals</h2>
      <ul class="mt-3 space-y-2 text-slate-700">
        <li><span class="font-medium">Upload & share:</span> notes, past papers, tutorials, and tips from students.</li>
        <li><span class="font-medium">Discover fast:</span> powerful search + filters by keyword, subject code, type, and session.</li>
        <li><span class="font-medium">Access quality:</span> previews, ratings, and profiles to surface the best material.</li>
      </ul>
    </div>
  </section>

  <!-- Features -->
  <section class="mx-auto max-w-6xl px-4 py-6">
    <h2 class="text-2xl font-semibold mb-4">Key features</h2>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <?php
      // Feature list (icon, title, desc)
      $features = [
        ['📤','File Upload & Sharing','Upload notes, past papers, and tutorials (PDF/DOCX).'],
        ['🏷️','Categorization & Tagging','Tag by subject code, type, and session for easy discovery.'],
        ['🔎','Search & Filter','Find materials by keywords, code, type, or session.'],
        ['⭐','Peer Rating & Comments','Quality bubbles up; ask follow-up questions politely.'],
        ['👤','User Profiles','Show top sharers and your own uploads/privates.'],
        ['👁️','Preview & Download','Glance at the file before you download.'],
        ['🔐','Public / Private','Choose visibility per upload to control who can see it.'],
      ];
      foreach ($features as $f): ?>
        <div class="rounded-2xl bg-white shadow p-5">
          <div class="text-2xl"><?php echo $f[0]; ?></div>
          <h3 class="mt-2 font-semibold"><?php echo $f[1]; ?></h3>
          <p class="text-slate-600 mt-1 text-sm"><?php echo $f[2]; ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Team -->
  <section class="mx-auto max-w-6xl px-4 py-8">
    <h2 class="text-2xl font-semibold mb-4">Our team</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <?php
      // Replace names/roles/emails as needed
      $team = [
        ['Qi Xiang','Frontend & Backend','morrisqxqx@gmail.com'],
        ['TianYou','Frontend & UI','pangtianyou1289@gmail.com'],
        ['Bernard','Database & UI','ryansim727@gmail.com'],
        ['Alvin','Slide & Backend','limalvin1121@gmail.com'],
      ];
      foreach ($team as $t): ?>
        <div class="rounded-2xl bg-white shadow p-5 text-center">
          <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 text-xl font-bold">
            <?php echo strtoupper($t[0][0]); ?>
          </div>
          <div class="font-semibold"><?php echo htmlspecialchars($t[0]); ?></div>
          <div class="text-slate-600 text-sm"><?php echo htmlspecialchars($t[1]); ?></div>
          <a class="text-indigo-600 text-sm inline-block mt-1 underline" href="mailto:<?php echo htmlspecialchars($t[2]); ?>">
            <?php echo htmlspecialchars($t[2]); ?>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Callout -->
  <section class="mx-auto max-w-6xl px-4 pb-12">
    <div class="rounded-2xl bg-indigo-600 text-white p-6 md:p-8 shadow">
      <h3 class="text-xl font-semibold">What’s next?</h3>
      <p class="mt-2 opacity-90">
        We plan to add inline previews, report/flagging for low-quality content, and better
        analytics to surface the most helpful materials each week.
      </p>
      <a href="Main.php" class="mt-4 inline-block rounded-lg bg-white/10 px-4 py-2 font-medium hover:bg-white/20">
        Back to Home
      </a>
    </div>
  </section>

  <?php include 'footer.php' ?>
</body>
</html>
