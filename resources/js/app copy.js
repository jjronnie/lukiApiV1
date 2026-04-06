import './bootstrap';
import './custom.js';
import { mountEditor } from './ckeditor';


import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
window.Chart = Chart;


window.Alpine = Alpine;

Alpine.start();


import { createIcons, icons } from 'lucide'

document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons }),
    mountEditor()
})
