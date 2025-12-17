import './bootstrap';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

// Register Alpine plugins
Alpine.plugin(collapse);

window.Alpine = Alpine;

Alpine.start();
