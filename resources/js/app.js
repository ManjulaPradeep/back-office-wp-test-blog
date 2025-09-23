import './bootstrap';

import { createApp } from 'vue';
import vuetify from './vuetify';
import Dashboard from './components/Dashboard.vue';

const el = document.getElementById('app');
if (el) {
    const app = createApp(Dashboard, {
        user: JSON.parse(el.dataset.user) 
    });
    app.use(vuetify);
    app.mount(el);
}
