document.addEventListener('DOMContentLoaded', () => {
    const btnMore = document.querySelector('.btn-header-more');
    const panel = document.getElementById('headerMobilePanel');
    const overlay = document.getElementById('headerMobileOverlay');
    const source = document.getElementById('headerActionsContent');

    if (!btnMore || !panel || !overlay || !source) return;

    // Clonar contenido SOLO UNA VEZ
    panel.innerHTML = source.innerHTML;

    btnMore.addEventListener('click', () => {
        panel.classList.toggle('show');
        overlay.classList.toggle('show');
    });

    overlay.addEventListener('click', () => {
        panel.classList.remove('show');
        overlay.classList.remove('show');
    });
});
