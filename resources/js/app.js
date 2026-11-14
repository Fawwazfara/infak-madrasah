import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './src/router';
import App from './src/App.vue';
import UiIcon from './src/components/shared/UiIcon.vue';
import { notify } from './src/utils/toast';
import '../css/app.css';
import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;
window.axios.defaults.baseURL = '/api';

window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 401) {
            localStorage.removeItem('user');
            if (router.currentRoute.value.path !== '/login') {
                notify('Sesi Anda telah berakhir. Silakan login kembali.', 'warning');
                router.push('/login');
            }
        }
        return Promise.reject(error);
    }
);

const app = createApp(App);

app.use(createPinia());
app.use(router);
app.component('UiIcon', UiIcon);

app.mount('#app');
