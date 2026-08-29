<template>
  <div class="flex h-screen bg-surface-container-lowest text-on-surface font-body-md overflow-hidden relative">
    
    <!-- Mobile Bottom Navigation (Visible only on small screens) -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 h-16 bg-surface-container-lowest border-t border-outline-variant/30 px-6 flex justify-between items-center z-50 shadow-[0_-4px_20px_rgba(0,0,0,0.05)] rounded-t-2xl pb-safe">
      <!-- Kelas & Siswa (Left) -->
      <router-link to="/guru/siswa" class="flex flex-col items-center gap-1 min-w-[64px]" active-class="text-primary font-bold">
        <span class="material-symbols-outlined text-[24px] text-on-surface-variant transition-colors" :class="$route.path.includes('/guru/siswa') ? 'text-primary fill' : ''">groups</span>
        <span class="text-[11px] text-on-surface-variant transition-colors" :class="$route.path.includes('/guru/siswa') ? 'text-primary' : ''">Siswa</span>
      </router-link>

      <!-- Dashboard (Center) -->
      <router-link to="/guru/dashboard" class="flex flex-col items-center gap-1 min-w-[64px]" active-class="text-primary font-bold">
        <span class="material-symbols-outlined text-[24px] text-on-surface-variant transition-colors" :class="$route.path.includes('/guru/dashboard') ? 'text-primary fill' : ''">dashboard</span>
        <span class="text-[11px] text-on-surface-variant transition-colors" :class="$route.path.includes('/guru/dashboard') ? 'text-primary' : ''">Dashboard</span>
      </router-link>

      <!-- Pesan (Right) -->
      <router-link to="/guru/pesan" class="flex flex-col items-center gap-1 min-w-[64px]" active-class="text-primary font-bold">
        <span class="material-symbols-outlined text-[24px] text-on-surface-variant transition-colors" :class="$route.path.includes('/guru/pesan') ? 'text-primary fill' : ''">mail</span>
        <span class="text-[11px] text-on-surface-variant transition-colors" :class="$route.path.includes('/guru/pesan') ? 'text-primary' : ''">Pesan</span>
      </router-link>
    </nav>

    <!-- Desktop Sidebar (Hidden on mobile) -->
    <aside class="hidden md:flex flex-col w-[280px] bg-surface-container-lowest border-r border-outline-variant/30 flex-shrink-0 relative z-20">
      <div class="p-6 flex items-center gap-4">
        <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center text-white">
          <span class="material-symbols-outlined text-xl">school</span>
        </div>
        <div class="flex flex-col">
          <span class="font-title-md text-title-md font-bold text-primary">Portal Madrasah</span>
          <span class="font-label-sm text-label-sm text-on-surface-variant">Panel Guru</span>
        </div>
      </div>
      
      <nav class="flex-1 px-4 py-6 flex flex-col gap-2 overflow-y-auto">
        <router-link 
          to="/guru/dashboard" 
          active-class="bg-secondary-container text-on-secondary-container font-semibold"
          class="flex items-center gap-4 px-4 py-3 rounded-lg hover:bg-surface-container-high text-on-surface-variant font-label-md transition-colors"
        >
          <span class="material-symbols-outlined">dashboard</span>
          <span>Dashboard</span>
        </router-link>
        <router-link 
          to="/guru/siswa" 
          active-class="bg-secondary-container text-on-secondary-container font-semibold"
          class="flex items-center gap-4 px-4 py-3 rounded-lg hover:bg-surface-container-high text-on-surface-variant font-label-md transition-colors"
        >
          <span class="material-symbols-outlined">groups</span>
          <span>Kelas & Siswa</span>
        </router-link>
        <div class="h-px bg-surface-variant my-2 mx-4"></div>
        <router-link 
          to="/guru/pesan" 
          active-class="bg-secondary-container text-on-secondary-container font-semibold"
          class="flex items-center gap-4 px-4 py-3 rounded-lg hover:bg-surface-container-high text-on-surface-variant font-label-md transition-colors"
        >
          <span class="material-symbols-outlined">mail</span>
          <span>Pesan</span>
        </router-link>
        <div class="flex-grow"></div>
        <button @click="handleLogout" class="flex items-center gap-4 px-4 py-3 rounded-lg hover:bg-error-container text-error font-label-md transition-colors w-full mt-4">
          <span class="material-symbols-outlined">logout</span>
          <span>Logout</span>
        </button>
      </nav>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 h-full relative">
      
      <!-- Mobile Header -->
      <header class="md:hidden flex h-[72px] bg-surface-container-lowest/80 backdrop-blur-md border-b border-outline-variant/30 flex-shrink-0 items-center justify-between px-4 z-10 sticky top-0">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center text-white">
            <span class="material-symbols-outlined text-[18px]">school</span>
          </div>
          <h1 class="font-title-md text-title-md text-on-surface font-semibold capitalize">{{ $route.name ? $route.name.replace('guru.', '') : 'Dashboard' }}</h1>
        </div>
        
        <button @click="handleLogout" class="w-9 h-9 flex items-center justify-center rounded-full hover:bg-error/10 text-error transition-colors">
          <span class="material-symbols-outlined text-[22px]">logout</span>
        </button>
      </header>

      <!-- Desktop Header -->
      <header class="hidden md:flex h-[72px] bg-surface-container-lowest/80 backdrop-blur-md border-b border-outline-variant/30 flex-shrink-0 items-center justify-between px-6 z-10 sticky top-0">
        <div class="flex items-center gap-4">
          <h1 class="font-title-lg text-title-lg text-on-surface font-semibold capitalize">Dashboard Guru</h1>
        </div>
        
        <div class="flex items-center gap-4">
          <button class="w-10 h-10 rounded-full flex items-center justify-center text-on-surface-variant hover:bg-surface-container-high transition-colors relative">
            <span class="material-symbols-outlined">notifications</span>
          </button>
          
          <!-- User Profile -->
          <div class="flex items-center gap-3 pl-4 border-l border-outline-variant/30">
            <div class="w-9 h-9 rounded-full bg-primary text-white flex items-center justify-center font-bold text-sm">
              G
            </div>
            <div class="flex flex-col">
              <span class="font-label-md text-[13px] font-bold text-on-surface">Akun Guru</span>
              <span class="font-label-sm text-[11px] text-on-surface-variant">Wali Kelas</span>
            </div>
          </div>
        </div>
      </header>

      <!-- Main Router View -->
      <main class="flex-1 overflow-y-auto relative pb-20 md:pb-0">
        <router-view></router-view>
      </main>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { subscribeToPushNotifications } from '@/utils/pushHelper';

const router = useRouter();

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
