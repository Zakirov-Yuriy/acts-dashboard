import { createRouter, createWebHistory } from 'vue-router';
import DashboardView from './views/DashboardView.vue';
import ProjectsView from './views/ProjectsView.vue';

const routes = [
    { path: '/', name: 'dashboard', component: DashboardView, meta: { title: 'Оплаты и акты' } },
    { path: '/projects', name: 'projects', component: ProjectsView, meta: { title: 'Проекты' } },
];

export default createRouter({
    history: createWebHistory(),
    routes,
});
