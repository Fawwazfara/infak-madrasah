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
          <div v-for="group in filteredGroups" :key="group.dateLabel" class="relative z-10 flex flex-col gap-4">
            
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
          
          <!-- Empty State if no search results -->
          <div v-if="filteredGroups.length === 0" class="pl-12 text-center py-8">
            <span class="material-symbols-outlined text-[48px] text-outline-variant mb-2">search_off</span>
            <p class="font-label-md text-on-surface-variant">Tidak ada riwayat yang cocok dengan "{{ searchQuery }}".</p>
          </div>
        </div>

        <!-- Loading / End Indicator -->
        <div v-if="filteredGroups.length > 0" class="flex justify-center mt-8 pl-12">
          <span class="material-symbols-outlined text-outline-variant animate-spin">refresh</span>
        </div>

      </div>

    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

import axios from 'axios';
import { onMounted } from 'vue';

const searchQuery = ref('');

const rawHistory = ref([]);

onMounted(async () => {
  try {
    const res = await axios.get('/log-aktivitas');
    
    // Group logs by Date string
    const groups = {};
    res.data.forEach(log => {
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
    
    rawHistory.value = Object.values(groups);
  } catch (error) {
    console.error("Gagal mengambil riwayat", error);
  }
});

const filteredGroups = computed(() => {
  if (!searchQuery.value.trim()) return rawHistory.value;

  const query = searchQuery.value.toLowerCase();
  
  return rawHistory.value.map(group => {
    // Filter items inside the group
    const filteredItems = group.items.filter(item => 
      item.title.toLowerCase().includes(query) || 
      item.description.toLowerCase().includes(query)
    );
    
    // Return a new group object with filtered items
    return {
      ...group,
      items: filteredItems
    };
  }).filter(group => group.items.length > 0); // Remove empty groups
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
      rawHistory.value = [];
      alert("Log aktivitas telah berhasil dikosongkan.");
    } catch (error) {
      console.error("Gagal menghapus log", error);
      alert("Gagal menghapus log.");
    }
  }
};
</script>
