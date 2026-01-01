document.addEventListener("DOMContentLoaded", () => {
  const btnMore = document.getElementById("btnHeaderMore");
  const panel = document.getElementById("headerMobilePanel");
  const overlay = document.getElementById("headerMobileOverlay");

  if (!btnMore || !panel || !overlay) return;

  let desktopActions = null;     // .header-right-actions real
  let desktopHost = null;        // contenedor .header-actions-content (donde vive en desktop)
  let placeholder = null;        // marcador para devolverlo
  let moved = false;

  function findDesktopNodes() {
    // OJO: header-right-actions existe SOLO cuando la vista tiene header-buttons
    desktopActions = document.querySelector("#header .header-right-actions");
    desktopHost = document.querySelector("#header .header-actions-content");
  }

  function ensurePlaceholder() {
    if (!placeholder) placeholder = document.createComment("header-actions-placeholder");
  }

  function moveActionsToMobile() {
    findDesktopNodes();
    if (!desktopActions || !desktopHost) return;

    if (!moved) {
      ensurePlaceholder();
      // dejamos un marcador donde estaba, para devolverlo exacto
      desktopActions.parentNode.insertBefore(placeholder, desktopActions);
    }

    panel.innerHTML = "";
    panel.appendChild(desktopActions);
    moved = true;
  }

  function restoreActionsToDesktop() {
    if (!moved || !placeholder) return;

    // devolverlo al lugar original
    placeholder.parentNode.insertBefore(panel.firstElementChild, placeholder);
    panel.innerHTML = "";
    moved = false;
  }

  function isMobile() {
    return window.matchMedia("(max-width: 768px)").matches;
  }

  function openPanel() {
    if (isMobile()) moveActionsToMobile();
    panel.classList.add("show");
    overlay.classList.add("show");
  }

  function closePanel() {
    panel.classList.remove("show");
    overlay.classList.remove("show");
  }

  // Toggle ⋮
  btnMore.addEventListener("click", (e) => {
    e.preventDefault();
    e.stopPropagation();

    if (panel.classList.contains("show")) closePanel();
    else openPanel();
  });

  // Click fuera cierra
  overlay.addEventListener("click", closePanel);
  document.addEventListener("click", (e) => {
    if (!panel.classList.contains("show")) return;
    if (panel.contains(e.target) || btnMore.contains(e.target)) return;
    closePanel();
  });

  // No cierres al tocar dentro del panel
  panel.addEventListener("click", (e) => e.stopPropagation());

  // Si cambias tamaño, devuelves a desktop
  window.addEventListener("resize", () => {
    if (!isMobile()) {
      closePanel();
      restoreActionsToDesktop();
    } else {
      // si está abierto en móvil, asegúrate que esté movido
      if (panel.classList.contains("show")) moveActionsToMobile();
    }
  });

  // Estado inicial
  if (!isMobile()) {
    restoreActionsToDesktop();
  }
});
