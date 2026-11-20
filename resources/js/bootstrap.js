/**
 * Frontend HTTP Client Bootstrap
 *
 * Configures Axios with X-Requested-With headers so Laravel recognizes
 * AJAX requests and enforces CSRF protection seamlessly.
 *
 * @author Renatus Bernardo
 */
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const appUrlMeta = document.querySelector('meta[name="app-url"]');
if (appUrlMeta && appUrlMeta.getAttribute('content')) {
    window.axios.defaults.baseURL = appUrlMeta.getAttribute('content').replace(/\/+$/, '');
}
