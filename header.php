<header class="sticky top-0 z-50 bg-gradient-to-r from-blue-600 via-blue-400 to-white text-black shadow">
  <div class="container mx-auto px-4 py-2 flex items-center justify-between">
    <!-- Left Side Logo + Title -->
    <div class="flex items-center">
      <img src="img/logo.png" alt="Logo" class="w-9 h-9 mr-2">
      <a href="#" class="text-xl font-bold text-white">Knowledge Hub</a>
    </div>

    

    <!-- Right Side Control Button -->
    <div class="flex items-center space-x-4">
      <!-- Nav Bar-->
    <nav class="hidden md:flex space-x-6">
      <a href="Main.php" class="hover:text-blue-700">Home</a>
      <a href="about.php" class="hover:text-blue-700">About</a>
    </nav>
      <!-- Avatar -->
      <a href="Profile.php" class="w-9 h-9">
        <img src="img/studentprofile.png" alt="profile-picture"
          class="rounded-full border-2 border-white shadow w-full h-full object-cover">
      </a>
      <!-- Collection -->
      <a href="collection.php" class="hidden md:inline hover:text-gray-700">
        <i class="fa-solid fa-star text-2xl"></i>
      </a>
      <!-- Log Out (Desktop Only) -->
      <a href="Log In.html" class="hidden md:inline">
        <button class="bg-white text-gray-800 px-4 py-2 rounded shadow hover:bg-blue-300">
          <i class="fa-solid fa-arrow-right-from-bracket mr-2"></i>Log Out
        </button>
      </a>
      <!-- Hamburger Menu (Mobile Only) -->
      <button id="menuBtn" class="md:hidden text-2xl focus:outline-none">
        ☰
      </button>
    </div>
  </div>

  <!-- Mobile Dropdown Menu -->
  <div id="mobileMenu" class="hidden md:hidden flex justify-between bg-gradient-to-r from-blue-600 via-blue-400 to-white text-black shadow px-4 py-2">
    <div><a href="Main.php" class="py-2 hover:text-blue-600">Home</a></div>
    <div>
      <a href="about.php" class="py-2 hover:text-blue-600">About</a>
    </div>
    <div><a href="collection.php" class="py-2 hover:text-blue-600"><i class="fa-solid fa-star text-md"></i></a></div>
    <div><a href="Log In.html" class="py-2 hover:text-blue-600"><button class="bg-white text-gray-800 px-2 py-1 rounded shadow hover:bg-blue-300">
          <i class="fa-solid fa-arrow-right-from-bracket mr-2"></i>Log Out
        </button></a></div>
  </div>
</header>

<script>
  const btn = document.getElementById("menuBtn");
  const menu = document.getElementById("mobileMenu");
  btn.addEventListener("click", () => {
    menu.classList.toggle("hidden");
  });
</script>
