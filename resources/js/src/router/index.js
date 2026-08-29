import { createRouter, createWebHistory } from 'vue-router';
import LoginView from '../views/auth/LoginView.vue';
import AdminLayout from '../components/admin/AdminLayout.vue';
import DashboardView from '../views/admin/DashboardView.vue';
import SiswaListView from '../views/admin/SiswaListView.vue';
import SiswaFormView from '../views/admin/SiswaFormView.vue';
import InfakFormView from '../views/admin/InfakFormView.vue';
import GuruListView from '../views/admin/GuruListView.vue';
import PengeluaranView from '../views/admin/PengeluaranView.vue';
import LaporanView from '../views/admin/LaporanView.vue';
import PesanView from '../views/admin/PesanView.vue';
import RiwayatView from '../views/admin/RiwayatView.vue';

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: LoginView
    },
    {
      path: '/',
      redirect: '/login'
    },
    {
      path: '/admin',
      component: AdminLayout,
      children: [
        {
          path: '',
          redirect: '/admin/dashboard'
        },
        {
          path: 'dashboard',
          name: 'admin.dashboard',
          component: DashboardView
        },
        {
          path: 'siswa',
          name: 'admin.siswa',
          component: SiswaListView
        },
        {
          path: 'siswa/create',
          name: 'admin.siswa.create',
          component: SiswaFormView
        },
        {
          path: 'siswa/:id/edit',
          name: 'admin.siswa.edit',
          component: SiswaFormView
        },
        {
          path: 'infak/create',
          name: 'admin.infak.create',
          component: InfakFormView
        },
        {
          path: 'guru',
          name: 'admin.guru',
          component: GuruListView
        },
        {
          path: 'pengeluaran',
          name: 'admin.pengeluaran',
          component: PengeluaranView
        },
        {
          path: 'laporan',
          name: 'admin.laporan',
          component: LaporanView
        },
        {
          path: 'pesan',
          name: 'admin.pesan',
          component: PesanView
        },
        {
          path: 'riwayat',
          name: 'admin.riwayat',
          component: RiwayatView
        }
      ]
    },
    {
      path: '/guru',
      component: () => import('../components/guru/GuruLayout.vue'),
      children: [
        {
          path: '',
          redirect: '/guru/dashboard'
        },
        {
          path: 'dashboard',
          name: 'guru.dashboard',
          component: () => import('../views/guru/DashboardView.vue')
        },
        {
          path: 'siswa',
          name: 'guru.siswa',
          component: () => import('../views/guru/SiswaListView.vue')
        },
        {
          path: 'pesan',
          name: 'guru.pesan',
          component: () => import('../views/guru/PesanView.vue')
        }
      ]
    }
  ]
});

router.beforeEach((to, from, next) => {
  const publicPages = ['/login'];
  const authRequired = !publicPages.includes(to.path);
  const loggedIn = localStorage.getItem('user');

  if (authRequired && !loggedIn) {
    return next('/login');
  }

  next();
});

export default router;
