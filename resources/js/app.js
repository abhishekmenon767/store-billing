import Alpine from 'alpinejs';
import billing from './billing';

Alpine.data('billing', billing);
window.Alpine = Alpine;
Alpine.start();
