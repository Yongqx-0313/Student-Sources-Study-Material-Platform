document.addEventListener("DOMContentLoaded", () => {
  const detail = document.getElementById("detail");
  const toggleBtn = document.getElementById("toggleBtn");

  if (!detail || !toggleBtn) return;

  // --- Check if text exceeds 2 lines ---
  const lineHeight = parseFloat(getComputedStyle(detail).lineHeight); // height of 1 line
  const maxHeight = lineHeight * 2; // 2 lines height
  const fullHeight = detail.scrollHeight; // actual content height

  if (fullHeight <= maxHeight) {
    // Content fits within 2 lines → hide button
    toggleBtn.style.display = "none";
  }

  // --- Toggle expand/collapse ---
  toggleBtn.addEventListener("click", () => {
    if (detail.classList.contains("line-clamp-2")) {
      detail.classList.remove("line-clamp-2");
      toggleBtn.textContent = "Show less";
    } else {
      detail.classList.add("line-clamp-2");
      toggleBtn.textContent = "Show more";
    }
  });

  const toggleBtn2 = document.getElementById("toggleBtn2");
    const toggleIcon2 = document.getElementById("toggleIcon2");
    const toggleContent = document.getElementById("toggleContent");

    toggleBtn2.addEventListener("click", () => {
      // Toggle visibility
      toggleContent.classList.toggle("hidden");

      // Change icon
      if (toggleContent.classList.contains("hidden")) {
        toggleIcon2.classList.remove("fa-angle-up");
        toggleIcon2.classList.add("fa-angle-down");
      } else {
        toggleIcon2.classList.remove("fa-angle-down");
        toggleIcon2.classList.add("fa-angle-up");
      }
    });
});



