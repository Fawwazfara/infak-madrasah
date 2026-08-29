import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './src/router';
import App from './src/App.vue';
import '../css/app.css';
import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;
window.axios.defaults.baseURL = 'https://assajjad.web.id/api';

window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 401) {
            if (router.currentRoute.value.path !== '/login') {
                alert('Sesi Anda telah berakhir atau tidak valid. Silakan login kembali.');
                router.push('/login');
            }
        }
        return Promise.reject(error);
    }
);

const app = createApp(App);

app.use(createPinia());
app.use(router);

app.mount('#app');
