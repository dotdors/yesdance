document.addEventListener("DOMContentLoaded", () => {
  const overlay = document.createElement("div");
  overlay.id = "spotlight-overlay";
  document.body.appendChild(overlay);

  const body = document.body;
  let mouseX = window.innerWidth / 2;
  let mouseY = window.innerHeight / 2;
  let ticking = false;

  const minWidth = 768; // disable on mobile

  function updateSpotlight() {
    document.documentElement.style.setProperty("--circle-x", `${mouseX}px`);
    document.documentElement.style.setProperty("--circle-y", `${mouseY}px`);
    ticking = false;
  }

  function onMouseMove(e) {
    if (window.innerWidth < minWidth) return;
    mouseX = e.clientX;
    mouseY = e.clientY;
    if (!ticking) {
      window.requestAnimationFrame(updateSpotlight);
      ticking = true;
    }
  }

  function enableEffect(mode) {
    body.classList.remove("spotlight-active", "invert-active");
    if (mode) {
      body.classList.add(`${mode}-active`);
      window.addEventListener("mousemove", onMouseMove);
    } else {
      window.removeEventListener("mousemove", onMouseMove);
    }
  }

  // Default: spotlight
  if (window.innerWidth >= minWidth) {
    enableEffect("spotlight");
  }

  // Handle resize
  window.addEventListener("resize", () => {
    if (window.innerWidth < minWidth) {
      enableEffect(null);
    } else if (
      !body.classList.contains("spotlight-active") &&
      !body.classList.contains("invert-active")
    ) {
      enableEffect("spotlight");
    }
  });

  // 🔑 Keyboard toggles
  document.addEventListener("keydown", (e) => {
    if (e.key === "1") enableEffect("spotlight");
    if (e.key === "2") enableEffect("invert");
    if (e.key === "0") enableEffect(null);
  });

  // Expose toggle for external control
  window.toggleCursorEffect = enableEffect;
});
