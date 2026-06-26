import axios from 'axios';

const http = axios.create({
    baseURL: '/api',
    headers: { Accept: 'application/json' },
});

// Тонкая обёртка над API. Один слой доступа к данным — компоненты не знают про axios.
export default {
    summary: (params) => http.get('/dashboard/summary', { params }).then((r) => r.data.data),
    payments: (params) => http.get('/payments', { params }).then((r) => r.data),
    projects: (params) => http.get('/projects', { params }).then((r) => r.data.data),
    references: () => http.get('/references').then((r) => r.data),
    updateAct: (id, payload) => http.patch(`/acts/${id}`, payload).then((r) => r.data.data),
};
