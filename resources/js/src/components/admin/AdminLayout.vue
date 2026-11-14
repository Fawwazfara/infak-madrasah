<template>
  <div class="h-screen overflow-hidden bg-[#F5F3EE] text-[#1C1917] flex flex-col md:flex-row">
    <!-- Mobile Drawer Backdrop -->
    <Transition
      enter-active-class="transition-opacity duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-150 ease-out"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isDrawerOpen"
        @click="isDrawerOpen = false"
        class="fixed inset-0 bg-[#1C1917]/45 backdrop-blur-[2px] z-40 md:hidden overscroll-contain"
      ></div>
    </Transition>

    <!-- Navigation Sidebar / Drawer -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 w-[264px] h-screen bg-[#FDFCF9] border-r border-black/[0.06] z-50 md:z-20 flex flex-col shrink-0 transition-transform duration-200 ease-out md:relative md:translate-x-0 overscroll-contain',
        isDrawerOpen ? 'translate-x-0 shadow-[var(--shadow-elevated)]' : '-translate-x-full'
      ]"
    >
      <div class="h-16 px-5 border-b border-black/[0.06] flex items-center justify-between shrink-0">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-9 h-9 bg-[#292524] rounded-[10px] flex items-center justify-center text-[#FDFCF9] shadow-[var(--shadow-btn-navy)] shrink-0">
            <UiIcon name="school" :size="18" :weight="600" />
          </div>
          <div class="min-w-0">
            <p class="font-bold text-sm text-[#1C1917] leading-tight truncate">Assajjad</p>
            <p class="text-[11px] font-medium text-[#78716C] leading-tight truncate">Admin Tata Usaha</p>
          </div>
        </div>
        <button
          @click="isDrawerOpen = false"
          aria-label="Tutup menu"
          class="min-w-[36px] min-h-[36px] rounded-[9px] text-[#78716C] hover:text-[#1C1917] hover:bg-[#ECE8DF] flex items-center justify-center md:hidden transition-[background-color,color,transform] duration-150 ease-out active:scale-[0.96]"
        >
          <UiIcon name="close" :size="18" :weight="600" />
        </button>
      </div>

      <nav @click="isDrawerOpen = false" class="flex-1 px-3 py-4 flex flex-col gap-1 overflow-y-auto overscroll-contain">
        <p class="px-3 pt-1 pb-1.5 text-[11px] font-bold uppercase tracking-wider text-[#78716C]">Utama</p>
        <router-link v-for="item in navPrimary" :key="item.to" :to="item.to" :class="linkClass(item.to)">
          <UiIcon :name="item.icon" :size="18" :weight="isActive(item.to) ? 600 : 500" :filled="isActive(item.to)" />
          <span>{{ item.label }}</span>
        </router-link>

        <p class="px-3 pt-4 pb-1.5 text-[11px] font-bold uppercase tracking-wider text-[#78716C]">Keuangan &amp; Laporan</p>
        <router-link v-for="item in navFinance" :key="item.to" :to="item.to" :class="linkClass(item.to)">
          <UiIcon :name="item.icon" :size="18" :weight="isActive(item.to) ? 600 : 500" :filled="isActive(item.to)" />
          <span>{{ item.label }}</span>
        </router-link>

        <p class="px-3 pt-4 pb-1.5 text-[11px] font-bold uppercase tracking-wider text-[#78716C]">Lainnya</p>
        <router-link v-for="item in navOther" :key="item.to" :to="item.to" :class="linkClass(item.to)">
          <UiIcon :name="item.icon" :size="18" :weight="isActive(item.to) ? 600 : 500" :filled="isActive(item.to)" />
          <span>{{ item.label }}</span>
        </router-link>

        <div class="flex-grow"></div>

        <div class="pt-3 mt-3 border-t border-black/[0.06]">
          <button
            @click="handleLogout"
            class="w-full min-h-[42px] px-3 py-2 rounded-[10px] text-sm font-semibold text-[#9F1239] hover:bg-[#FDF2F4] flex items-center gap-3 transition-[background-color,transform] duration-150 ease-out active:scale-[0.96]"
          >
            <UiIcon name="logout" :size="18" :weight="600" />
            <span>Keluar</span>
          </button>
        </div>
      </nav>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
      <header class="h-16 bg-[#FDFCF9]/90 backdrop-blur-md border-b border-black/[0.06] shrink-0 z-10 sticky top-0">
        <div class="h-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4">
          <div class="flex items-center gap-3 min-w-0">
            <button
              @click="isDrawerOpen = true"
              aria-label="Buka menu navigasi"
              class="min-w-[40px] min-h-[40px] -ml-1.5 rounded-[10px] text-[#44403C] hover:text-[#1C1917] hover:bg-[#ECE8DF] flex items-center justify-center md:hidden transition-[background-color,transform] duration-150 ease-out active:scale-[0.96]"
            >
              <UiIcon name="menu" :size="20" :weight="600" />
            </button>
            <div class="min-w-0">
              <p class="text-[11px] font-semibold uppercase tracking-wider text-[#78716C] hidden sm:block">Portal Administrasi Madrasah</p>
              <h1 class="text-base sm:text-lg font-bold text-[#1C1917] truncate">{{ pageTitle }}</h1>
            </div>
          </div>

          <div class="flex items-center gap-3 shrink-0">
            <div class="hidden sm:flex flex-col text-right">
              <span class="text-xs font-bold text-[#1C1917] truncate max-w-[160px]">{{ user.name || 'Admin Tata Usaha' }}</span>
              <span class="text-[11px] text-[#78716C]">Administrator</span>
            </div>
            <div class="w-9 h-9 rounded-[10px] bg-[#292524] text-[#FDFCF9] flex items-center justify-center font-bold text-xs shadow-[var(--shadow-btn-navy)]">
              {{ initials }}
            </div>
          </div>
        </div>
      </header>

      <main class="flex-1 overflow-y-auto w-full relative">
        <router-view></router-view>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import { subscribeToPushNotifications } from '@/utils/pushHelper';

const router = useRouter();
const route = useRoute();
const isDrawerOpen = ref(false);

const user = computed(() => {
  try {
    return JSON.parse(localStorage.getItem('user') || '{}');
  } catch {
    return {};
  }
});

const initials = computed(() => {
  const name = (user.value.name || 'Admin').trim();
  const parts = name.split(/\s+/);
  return parts.length >= 2
    ? (parts[0][0] + parts[1][0]).toUpperCase()
    : name.substring(0, 2).toUpperCase();
});

const navPrimary = [
  { to: '/admin/dashboard', label: 'Dashboard', icon: 'dashboard' },
  { to: '/admin/siswa', label: 'Kelas & Siswa', icon: 'groups' },
  { to: '/admin/guru', label: 'Manajemen Guru', icon: 'badge' },
];

const navFinance = [
  { to: '/admin/infak/create', label: 'Catat Infak', icon: 'volunteer_activism' },
  { to: '/admin/pengeluaran', label: 'Pengeluaran & Kasbon', icon: 'money_off' },
  { to: '/admin/laporan', label: 'Laporan Keuangan', icon: 'account_balance' },
];

const navOther = [
  { to: '/admin/pesan', label: 'Pesan Guru', icon: 'mail' },
  { to: '/admin/riwayat', label: 'Riwayat Aktivitas', icon: 'history' },
];

const TITLES = {
  'admin.dashboard': 'Dashboard Keuangan',
  'admin.siswa': 'Monitoring Pembayaran & Siswa',
  'admin.siswa.create': 'Tambah Data Siswa',
  'admin.siswa.edit': 'Ubah Data Siswa & Infak',
  'admin.infak.create': 'Catat Penerimaan Infak',
  'admin.guru': 'Manajemen Akun Guru',
  'admin.pengeluaran': 'Pengeluaran & Kasbon Guru',
  'admin.laporan': 'Laporan Keuangan Bulanan',
  'admin.pesan': 'Pesan Masuk Guru',
  'admin.riwayat': 'Riwayat Aktivitas',
};

const pageTitle = computed(() => TITLES[route.name] || 'Admin Madrasah');

const isActive = (path) => route.path === path || route.path.startsWith(path + '/');

const linkClass = (path) => [
  'min-h-[42px] px-3 py-2 rounded-[10px] text-sm font-medium flex items-center gap-3 transition-[background-color,color,box-shadow,transform] duration-150 ease-out active:scale-[0.98]',
  isActive(path)
    ? 'bg-[#292524] text-[#FDFCF9] font-semibold shadow-[var(--shadow-btn-navy)]'
    : 'text-[#44403C] hover:text-[#1C1917] hover:bg-[#ECE8DF]'
];

const handleLogout = async () => {
  try {
    await axios.post('/logout');
  } catch (error) {
    console.error('Logout error', error);
  } finally {
    localStorage.removeItem('user');
    router.push('/login');
  }
};

onMounted(() => {
  subscribeToPushNotifications();
});
</script>
