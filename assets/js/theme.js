// Dark/Light Mode Toggle
(function () {
  const currentTheme = localStorage.getItem("theme") || "light";
  function setTheme(theme) {
    document.documentElement.setAttribute("data-theme", theme);
    localStorage.setItem("theme", theme);

    const toggleBtn = document.getElementById("themeToggle");
    if (toggleBtn) {
      const icon = toggleBtn.querySelector("i");
      if (icon) {
        if (theme === "dark") {
          icon.className = "bi bi-sun-fill";
          toggleBtn.setAttribute("aria-label", "Switch to light mode");
        } else {
          icon.className = "bi bi-moon-fill";
          toggleBtn.setAttribute("aria-label", "Switch to dark mode");
        }
      }
    }
  }

  // Initialize theme
  setTheme(currentTheme);

  // Toggle theme function
  window.toggleTheme = function () {
    const currentTheme = document.documentElement.getAttribute("data-theme");
    const newTheme = currentTheme === "dark" ? "light" : "dark";
    setTheme(newTheme);
  };

  // Add event listener when DOM is ready
  document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.getElementById("themeToggle");
    if (toggleBtn) {
      toggleBtn.addEventListener("click", toggleTheme);
    }
  });
})();

