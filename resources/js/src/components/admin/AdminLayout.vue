<template>
  <div class="h-screen overflow-hidden bg-background text-on-surface antialiased flex flex-col md:flex-row">
    
    <!-- Mobile Drawer Backdrop -->
    <transition
      enter-active-class="transition-opacity duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-300"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div 
        v-if="isDrawerOpen" 
        @click="isDrawerOpen = false" 
        class="fixed inset-0 bg-black/50 z-40 md:hidden"
      ></div>
    </transition>

    <!-- Navigation Sidebar/Drawer -->
    <!-- On mobile: Fixed sliding drawer. On desktop: Permanent left sidebar -->
    <aside 
      :class="[
        'fixed inset-y-0 left-0 w-[280px] h-screen bg-surface z-50 shadow-2xl md:shadow-none md:border-r md:border-surface-variant transition-transform duration-300 ease-in-out md:relative md:translate-x-0',
        isDrawerOpen ? 'translate-x-0' : '-translate-x-full'
      ]"
    >
      <div class="p-md border-b border-surface-variant flex items-center justify-between">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center text-white">
            <span class="material-symbols-outlined text-xl">school</span>
          </div>
          <span class="font-headline-md text-primary">Madrasah</span>
        </div>
        <!-- Close Button (Mobile Only) -->
        <button 
          @click="isDrawerOpen = false" 
          class="p-2 rounded-full hover:bg-surface-variant text-on-surface-variant md:hidden"
        >
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>
      
      <nav @click="isDrawerOpen = false" class="p-2 flex flex-col gap-1 overflow-y-auto h-[calc(100vh-73px)]">
        <router-link 
          to="/admin/dashboard" 
          active-class="bg-secondary-container text-on-secondary-container font-semibold"
          class="flex items-center gap-4 px-4 py-3 rounded-lg hover:bg-surface-container-high text-on-surface-variant font-label-md transition-colors"
        >
          <span class="material-symbols-outlined fill">dashboard</span>
          <span>Dashboard</span>
        </router-link>
        <router-link 
          to="/admin/siswa" 
          active-class="bg-secondary-container text-on-secondary-container font-semibold"
          class="flex items-center gap-4 px-4 py-3 rounded-lg hover:bg-surface-container-high text-on-surface-variant font-label-md transition-colors"
        >
          <span class="material-symbols-outlined">group</span>
          <span>Kelas &amp; Siswa</span>
        </router-link>
        <router-link 
          to="/admin/guru" 
          active-class="bg-secondary-container text-on-secondary-container font-semibold"
          class="flex items-center gap-4 px-4 py-3 rounded-lg hover:bg-surface-container-high text-on-surface-variant font-label-md transition-colors"
        >
          <span class="material-symbols-outlined">badge</span>
          <span>Manajemen Guru</span>
        </router-link>
        <div class="h-px bg-surface-variant my-2 mx-4"></div>
        <router-link 
          to="/admin/infak/create" 
          active-class="bg-secondary-container text-on-secondary-container font-semibold"
          class="flex items-center gap-4 px-4 py-3 rounded-lg hover:bg-surface-container-high text-on-surface-variant font-label-md transition-colors"
        >
          <span class="material-symbols-outlined">volunteer_activism</span>
          <span>Input Infak</span>
        </router-link>
        <router-link 
          to="/admin/pengeluaran" 
          active-class="bg-secondary-container text-on-secondary-container font-semibold"
          class="flex items-center gap-4 px-4 py-3 rounded-lg hover:bg-surface-container-high text-on-surface-variant font-label-md transition-colors"
        >
          <span class="material-symbols-outlined">money_off</span>
          <span>Pengeluaran</span>
        </router-link>
        <router-link 
          to="/admin/laporan" 
          active-class="bg-secondary-container text-on-secondary-container font-semibold"
          class="flex items-center gap-4 px-4 py-3 rounded-lg hover:bg-surface-container-high text-on-surface-variant font-label-md transition-colors"
        >
          <span class="material-symbols-outlined">account_balance</span>
          <span>Laporan Keuangan</span>
        </router-link>
        <div class="h-px bg-surface-variant my-2 mx-4"></div>
        <router-link 
          to="/admin/pesan" 
          active-class="bg-secondary-container text-on-secondary-container font-semibold"
          class="flex items-center gap-4 px-4 py-3 rounded-lg hover:bg-surface-container-high text-on-surface-variant font-label-md transition-colors"
        >
          <span class="material-symbols-outlined">mail</span>
          <span>Pesan</span>
        </router-link>
        <router-link 
          to="/admin/riwayat" 
          active-class="bg-secondary-container text-on-secondary-container font-semibold"
          class="flex items-center gap-4 px-4 py-3 rounded-lg hover:bg-surface-container-high text-on-surface-variant font-label-md transition-colors"
        >
          <span class="material-symbols-outlined">history</span>
          <span>Riwayat Aktivitas</span>
        </router-link>
        <div class="flex-grow"></div>
        <button @click="handleLogout" class="flex items-center gap-4 px-4 py-3 rounded-lg hover:bg-error-container text-error font-label-md transition-colors w-full mt-4 mb-4">
          <span class="material-symbols-outlined">logout</span>
          <span>Logout</span>
        </button>
      </nav>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
      
      <!-- TopAppBar -->
      <header class="bg-surface shadow-sm sticky top-0 z-30 transition-all duration-200 shrink-0">
        <div class="flex justify-between items-center w-full px-container-margin h-16 max-w-7xl mx-auto">
          <div class="flex items-center gap-md">
            <!-- Hamburger Menu (Mobile Only) -->
            <button 
              @click="isDrawerOpen = true" 
              class="p-2 -ml-2 rounded-full hover:bg-primary-container/10 text-primary transition-all active:scale-95 focus:outline-none md:hidden"
            >
              <span class="material-symbols-outlined text-2xl">menu</span>
            </button>
            
            <div class="w-10 h-10 rounded-full overflow-hidden shrink-0 border-2 border-primary-container bg-surface-variant flex items-center justify-center">
              <img alt="User Profile Picture" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuD7fec1vwfjlEDZj5zSc2WGTkcPTgKpeEwDHMdhcMfCTWqRA40TISQrMpjdIhKflew27um5YSO3siL4zuFhcviNOQxlF7q2Tj9aS_FSWqwMnpFmcuiZ3eJre6P6-1yqkphGPyehWHTUB7YMjt5OJkqqK8OL6XoIXoN8dzZRtuh8X42KYCAtLt6s34cSud8sKd1rlKO-6xbykrMRL6GcmL2np1z7GgZ3OoPflDi4Ea4nF_pXVWXYT9kGgPsU3AnmHT4hX5mZ9AVUi8ET">
            </div>
            <div>
              <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Madrasah Manager</p>
              <h1 class="font-headline-lg-mobile text-headline-lg-mobile text-primary truncate max-w-[200px] md:max-w-[400px]">Assalamu'alaikum, Admin</h1>
            </div>
          </div>
          
          <div class="flex items-center gap-2">
            <button class="p-2 rounded-full hover:bg-primary-container/10 text-primary transition-all duration-200 active:scale-95 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
              <span class="material-symbols-outlined text-2xl" data-icon="notifications">notifications</span>
            </button>
          </div>
        </div>
      </header>

      <!-- Page Content Router View -->
      <div class="flex-1 overflow-y-auto w-full relative pb-16 md:pb-0">
        <router-view></router-view>
      </div>
      
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { subscribeToPushNotifications } from '@/utils/pushHelper';

const router = useRouter();
const isDrawerOpen = ref(false);

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
