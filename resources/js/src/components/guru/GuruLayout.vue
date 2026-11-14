<template>
  <div class="flex h-screen bg-[#F5F3EE] text-[#1C1917] overflow-hidden relative">
    <!-- Desktop Sidebar -->
    <aside class="hidden md:flex flex-col w-[264px] bg-[#FDFCF9] border-r border-black/[0.06] shrink-0 relative z-20">
      <div class="h-16 px-5 border-b border-black/[0.06] flex items-center gap-3">
        <div class="w-9 h-9 bg-[#A8551E] rounded-[10px] flex items-center justify-center text-[#FDFCF9] shadow-[var(--shadow-btn-primary)] shrink-0">
          <UiIcon name="school" :size="18" :weight="600" />
        </div>
        <div class="min-w-0">
          <p class="font-bold text-sm text-[#1C1917] leading-tight truncate">Assajjad</p>
          <p class="text-[11px] font-medium text-[#78716C] leading-tight truncate">Portal Wali Kelas</p>
        </div>
      </div>

      <nav class="flex-1 px-3 py-4 flex flex-col gap-1 overflow-y-auto">
        <p class="px-3 pt-1 pb-1.5 text-[11px] font-bold uppercase tracking-wider text-[#78716C]">Menu Guru</p>
        <router-link v-for="item in navItems" :key="item.to" :to="item.to" :class="linkClass(item.to)">
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
    <div class="flex-1 flex flex-col min-w-0 h-full relative">
      <header class="h-16 bg-[#FDFCF9]/90 backdrop-blur-md border-b border-black/[0.06] shrink-0 flex items-center justify-between px-4 sm:px-6 lg:px-8 z-10 sticky top-0">
        <div class="flex items-center gap-3 min-w-0">
          <div class="md:hidden w-8 h-8 bg-[#A8551E] rounded-[9px] flex items-center justify-center text-[#FDFCF9] shrink-0">
            <UiIcon name="school" :size="16" :weight="600" />
          </div>
          <div class="min-w-0">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#78716C] hidden sm:block">Portal Wali Kelas &amp; Guru</p>
            <h1 class="text-base sm:text-lg font-bold text-[#1C1917] truncate">{{ pageTitle }}</h1>
          </div>
        </div>

        <div class="flex items-center gap-3 shrink-0">
          <div class="hidden sm:flex flex-col text-right">
            <span class="text-xs font-bold text-[#1C1917] truncate max-w-[160px]">{{ user.name || 'Akun Guru' }}</span>
            <span class="text-[11px] text-[#78716C]">Wali Kelas</span>
          </div>
          <div class="w-9 h-9 rounded-[10px] bg-[#A8551E] text-[#FDFCF9] flex items-center justify-center font-bold text-xs shadow-[var(--shadow-btn-primary)]">
            {{ initials }}
          </div>
          <button
            @click="handleLogout"
            aria-label="Keluar"
            class="md:hidden min-w-[38px] min-h-[38px] flex items-center justify-center rounded-[10px] text-[#9F1239] hover:bg-[#FDF2F4] transition-[background-color,transform] duration-150 ease-out active:scale-[0.96]"
          >
            <UiIcon name="logout" :size="18" :weight="600" />
          </button>
        </div>
      </header>

      <main class="flex-1 overflow-y-auto relative pb-20 md:pb-0">
        <router-view></router-view>
      </main>
    </div>

    <!-- Mobile Bottom Navigation -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 h-16 bg-[#FDFCF9]/95 backdrop-blur-md border-t border-black/[0.08] px-2 flex justify-around items-center z-40 shadow-[0_-4px_20px_oklch(0.22_0.015_65_/_0.06)]">
      <router-link
        v-for="item in navItems"
        :key="item.to"
        :to="item.to"
        class="flex flex-col items-center justify-center gap-1 min-w-[64px] py-1.5 rounded-[10px] transition-[color,transform] duration-150 ease-out active:scale-[0.96]"
        :class="isActive(item.to) ? 'text-[#A8551E] font-bold' : 'text-[#78716C] font-medium'"
      >
        <UiIcon :name="item.icon" :size="20" :weight="isActive(item.to) ? 600 : 500" :filled="isActive(item.to)" />
        <span class="text-[11px] leading-none">{{ item.shortLabel }}</span>
      </router-link>
    </nav>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import { subscribeToPushNotifications } from '@/utils/pushHelper';

const router = useRouter();
const route = useRoute();

const user = computed(() => {
  try {
    return JSON.parse(localStorage.getItem('user') || '{}');
  } catch {
    return {};
  }
});

const initials = computed(() => {
  const name = (user.value.name || 'Guru').trim();
  const parts = name.split(/\s+/);
  return parts.length >= 2
    ? (parts[0][0] + parts[1][0]).toUpperCase()
    : name.substring(0, 2).toUpperCase();
});

const navItems = [
  { to: '/guru/dashboard', label: 'Dashboard', shortLabel: 'Beranda', icon: 'dashboard' },
  { to: '/guru/siswa', label: 'Kelas & Siswa', shortLabel: 'Siswa', icon: 'groups' },
  { to: '/guru/laporan', label: 'Laporan Keuangan', shortLabel: 'Laporan', icon: 'account_balance' },
  { to: '/guru/pesan', label: 'Pesan Admin', shortLabel: 'Pesan', icon: 'mail' },
];

const TITLES = {
  'guru.dashboard': 'Dashboard Guru',
  'guru.siswa': 'Kelas & Daftar Siswa',
  'guru.laporan': 'Laporan Keuangan Madrasah',
  'guru.pesan': 'Pesan ke Admin TU',
};

const pageTitle = computed(() => TITLES[route.name] || 'Portal Guru');

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
