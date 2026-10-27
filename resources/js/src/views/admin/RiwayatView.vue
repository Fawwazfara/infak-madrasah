<template>
  <div class="flex-1 flex flex-col min-w-0 h-full bg-surface-dim md:bg-transparent overflow-y-auto">
    <main class="w-full max-w-3xl mx-auto px-container-margin py-lg pb-24 md:pb-lg flex flex-col gap-lg">
      
      <!-- Header Area -->
      <div class="flex items-center justify-between">
        <h2 class="font-headline-lg-mobile md:font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background">Riwayat Aktivitas</h2>
        <button 
          @click="clearLog"
          class="font-label-md text-label-md text-error hover:bg-error/10 px-3 py-1.5 rounded-lg transition-colors active:scale-95"
        >
          Hapus Log
        </button>
      </div>

      <!-- Search Bar -->
      <div class="relative">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
        <input 
          v-model="searchQuery"
          @keyup.enter="fetchLogs"
          type="text" 
          placeholder="Cari transaksi atau aktivitas..." 
          class="w-full h-12 pl-10 pr-4 bg-surface-container-lowest rounded-xl border border-outline-variant/50 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 text-body-md transition-shadow shadow-sm"
        >
      </div>

      <!-- Timeline Container -->
      <div class="relative mt-2">
        <!-- Vertical Line -->
        <div class="absolute left-[19px] top-4 bottom-8 w-px bg-outline-variant/40 z-0"></div>

        <!-- Groups -->
        <div class="flex flex-col gap-6">
          <div v-for="group in groupedLogs" :key="group.dateLabel" class="relative z-10 flex flex-col gap-4">
            
            <!-- Date Label -->
            <div class="pl-12">
              <span class="font-label-sm text-[12px] font-bold text-on-surface-variant uppercase tracking-wider bg-surface-dim md:bg-background pr-2">
                {{ group.dateLabel }}
              </span>
            </div>

            <!-- Items -->
            <div v-for="item in group.items" :key="item.id" class="relative flex items-start pl-12">
              
              <!-- Timeline Icon -->
              <div 
                :class="[
                  'absolute left-0 w-10 h-10 rounded-full flex items-center justify-center text-white border-4 border-surface-dim md:border-background z-10',
                  getIconBgColor(item.type)
                ]"
              >
                <span class="material-symbols-outlined text-[16px]">{{ getIconName(item.type) }}</span>
              </div>
              
              <!-- Card Content -->
              <div class="w-full bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/30 p-4 hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-1">
                  <h3 class="font-label-md text-[15px] font-bold text-on-surface pr-4">{{ item.title }}</h3>
                  <span class="font-label-sm text-[12px] text-on-surface-variant shrink-0">{{ item.time }}</span>
                </div>
                
                <p class="font-body-sm text-[13px] text-on-surface-variant mb-3">{{ item.description }}</p>
                
                <!-- Nominal Badge (if exists) -->
                <div v-if="item.amount !== null" class="inline-block">
                  <span 
                    :class="[
                      'font-label-sm text-[13px] font-bold px-3 py-1 rounded-md',
                      item.type === 'income' ? 'bg-[#E8F5E9] text-[#1B5E20]' : 'bg-[#FFEBEE] text-[#B71C1C]'
                    ]"
                  >
                    {{ item.type === 'income' ? '+ ' : '- ' }}Rp {{ formatRupiah(item.amount) }}
                  </span>
                </div>
              </div>

            </div>
          </div>
          
          <!-- Empty State -->
          <div v-if="!isLoading && groupedLogs.length === 0" class="pl-12 text-center py-8">
            <span class="material-symbols-outlined text-[48px] text-outline-variant mb-2">search_off</span>
            <p class="font-label-md text-on-surface-variant">
              {{ searchQuery ? `Tidak ada riwayat yang cocok dengan "${searchQuery}".` : 'Belum ada riwayat aktivitas.' }}
            </p>
          </div>

          <!-- Skeleton Loading State -->
          <div v-if="isLoading" class="pl-12 flex flex-col gap-4">
            <div v-for="i in 3" :key="i" class="relative flex items-start pl-12">
              <div class="absolute left-0 w-10 h-10 rounded-full bg-surface-container-high animate-pulse border-4 border-surface-dim"></div>
              <div class="w-full bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/30 p-4">
                <div class="h-5 w-48 bg-surface-container-high animate-pulse rounded mb-3"></div>
                <div class="h-4 w-full bg-surface-container-high animate-pulse rounded"></div>
                <div class="h-4 w-3/4 bg-surface-container-high animate-pulse rounded mt-2"></div>
              </div>
            </div>
          </div>

          <!-- Load More Button (Server-side Pagination) -->
          <div v-if="!isLoading && hasMorePages" class="flex justify-center mt-8 pl-12">
            <button 
              @click="loadMore"
              :disabled="isLoadingMore"
              class="px-6 py-2 border border-outline-variant rounded-xl text-on-surface-variant hover:bg-surface-container-low transition-colors"
            >
              <span v-if="isLoadingMore" class="material-symbols-outlined animate-spin text-[20px]">sync</span>
              <span v-else class="material-symbols-outlined text-[20px]">keyboard_arrow_down</span>
              {{ isLoadingMore ? 'Memuat...' : 'Muat Lebih Banyak' }}
            </button>
          </div>

        </div>

      </div>

    </main>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import { onMounted } from 'vue';

const searchQuery = ref('');
const isLoading = ref(false);
const isLoadingMore = ref(false);
const allLogs = ref([]);
const currentPage = ref(1);
const hasMorePages = ref(true);

const fetchLogs = async (page = 1, append = false) => {
  if (append) {
    isLoadingMore.value = true;
  } else {
    isLoading.value = true;
  }
  
  try {
    const params = {
      per_page: 30,
      page: page
    };
    
    if (searchQuery.value.trim()) {
      params.search = searchQuery.value.trim();
    }
    
    const res = await axios.get('/log-aktivitas', { params });
    const newLogs = res.data.data || res.data; // Handle both paginated and non-paginated responses
    
    if (append) {
      allLogs.value = [...allLogs.value, ...newLogs];
    } else {
      allLogs.value = newLogs;
    }
    
    // Check if there are more pages (for paginated response)
    hasMorePages.value = res.data.current_page < res.data.last_page;
    currentPage.value = page;
  } catch (error) {
    console.error("Gagal mengambil riwayat", error);
    hasMorePages.value = false;
  } finally {
    isLoading.value = false;
    isLoadingMore.value = false;
  }
};

const loadMore = () => {
  if (!isLoadingMore.value && hasMorePages.value) {
    fetchLogs(currentPage.value + 1, true);
  }
};

// Re-fetch when search query changes (with debounce)
let searchTimeout = null;
watch(searchQuery, (newVal) => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchLogs(1, false);
  }, 300);
});

onMounted(() => {
  fetchLogs(1, false);
});

const groupedLogs = computed(() => {
  if (!allLogs.value.length) return [];
  
  const groups = {};
  allLogs.value.forEach(log => {
    const date = new Date(log.created_at);
    const dateStr = date.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    
    const timeStr = date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });

    if (!groups[dateStr]) {
      groups[dateStr] = {
        dateLabel: dateStr,
        items: []
      };
    }
    
    groups[dateStr].items.push({
      id: log.id,
      type: log.type,
      title: log.title,
      description: log.description,
      time: timeStr,
      amount: log.amount
    });
  });
  
  return Object.values(groups);
});

const getIconName = (type) => {
  switch (type) {
    case 'income': return 'account_balance_wallet';
    case 'expense': return 'build'; // Wrench/tools icon
    case 'system': return 'person_add';
    default: return 'history';
  }
};

const getIconBgColor = (type) => {
  switch (type) {
    case 'income': return 'bg-[#1B5E20]'; // Dark green
    case 'expense': return 'bg-[#B71C1C]'; // Dark red
    case 'system': return 'bg-[#616161]'; // Dark grey
    default: return 'bg-primary';
  }
};

const formatRupiah = (angka) => {
  return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};

const clearLog = async () => {
  if (confirm("PERINGATAN: Apakah Anda yakin ingin menghapus semua data log aktivitas? Tindakan ini tidak dapat dibatalkan dan akan dicatat dalam audit sistem.")) {
    try {
      await axios.post('/log-aktivitas/clear');
      allLogs.value = [];
      alert("Log aktivitas telah berhasil dikosongkan.");
    } catch (error) {
      console.error("Gagal menghapus log", error);
      alert("Gagal menghapus log.");
    }
  }
};
</script>
