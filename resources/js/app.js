const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sidebar-overlay');
const openBtn = document.getElementById('sidebar-open');
const closeBtn = document.getElementById('sidebar-close');

function openSidebar() {
    sidebar?.classList.remove('-translate-x-full');
    overlay?.classList.remove('hidden');
}

function closeSidebar() {
    sidebar?.classList.add('-translate-x-full');
    overlay?.classList.add('hidden');
}

openBtn?.addEventListener('click', openSidebar);
closeBtn?.addEventListener('click', closeSidebar);
overlay?.addEventListener('click', closeSidebar);

// Close sidebar on Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeSidebar();
});

// Confirm dialog helper used by delete/cancel forms
document.querySelectorAll('[data-confirm]').forEach((el) => {
    el.addEventListener('submit', (e) => {
        if (!confirm(el.dataset.confirm)) {
            e.preventDefault();
        }
    });
});
