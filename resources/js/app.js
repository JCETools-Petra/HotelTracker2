import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Chart = Chart;
window.Alpine = Alpine;

Alpine.start();

// !! FUNGSI UNTUK DARK MODE TOGGLE !!
function toggleTheme() {
    const htmlEl = document.documentElement;
    if (htmlEl.classList.contains('dark')) {
        htmlEl.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    } else {
        htmlEl.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    }
}

// Jadikan fungsi toggleTheme global agar bisa dipanggil dari elemen HTML
window.toggleTheme = toggleTheme;
// !! AKHIR FUNGSI DARK MODE TOGGLE !!