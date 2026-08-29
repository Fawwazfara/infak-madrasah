<template>
  <div class="flex-1 flex flex-col min-w-0 h-full bg-surface-dim md:bg-transparent overflow-y-auto">
    <main class="w-full max-w-6xl mx-auto px-container-margin py-lg pb-24 md:pb-lg flex flex-col gap-lg">
      
      <!-- Optional Mobile Title if wanted, but AdminLayout has TopAppBar -->
      <h2 class="font-headline-lg-mobile md:font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background md:hidden mb-2">Laporan Keuangan</h2>

      <!-- Header / Actions -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <!-- Filter Bulan -->
        <div class="flex flex-col gap-sm w-full md:w-auto max-w-full overflow-hidden">
          <div class="flex overflow-x-auto gap-3 pb-2 no-scrollbar">
          <button 
            v-for="month in months" 
            :key="month"
            type="button"
            @click="activeMonth = month"
            :class="[
              'h-10 px-5 rounded-full font-label-md text-label-md transition-all flex-shrink-0 border',
              activeMonth === month
                ? 'bg-primary text-white border-primary shadow-sm'
                : 'bg-surface-container-lowest text-on-surface-variant border-outline-variant/50 hover:bg-surface-container-high'
            ]"
          >
            {{ month }}
          </button>
          </div>
        </div>
        
        <!-- Cetak PDF Button -->
        <button 
          @click="cetakPdf"
          :disabled="isDownloading"
          class="w-full md:w-auto shrink-0 bg-primary text-white px-6 py-3 rounded-xl font-label-md text-label-md flex items-center justify-center gap-2 hover:bg-primary/90 transition-colors active:scale-95 shadow-sm disabled:opacity-70 disabled:cursor-not-allowed"
        >
          <span v-if="!isDownloading" class="material-symbols-outlined text-[20px]">print</span> 
          <span v-else class="material-symbols-outlined animate-spin text-[20px]">sync</span>
          {{ isDownloading ? 'Memproses...' : 'Cetak PDF' }}
        </button>
      </div>

      <!-- Ringkasan Keuangan (Cards) -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-md">
        <!-- Card Pemasukan -->
        <div class="bg-primary/5 border border-primary/20 rounded-3xl p-lg flex flex-col gap-2">
          <span class="font-label-sm text-[12px] font-bold text-primary uppercase tracking-wider">Total Pemasukan</span>
          <span class="font-headline-lg text-[28px] font-bold text-primary">Rp {{ formatRupiah(totalPemasukan) }}</span>
        </div>
        
        <!-- Card Pengeluaran -->
        <div class="bg-error/5 border border-error/20 rounded-3xl p-lg flex flex-col gap-2">
          <span class="font-label-sm text-[12px] font-bold text-error uppercase tracking-wider">Total Pengeluaran</span>
          <span class="font-headline-lg text-[28px] font-bold text-error">Rp {{ formatRupiah(totalPengeluaran) }}</span>
        </div>
        
        <!-- Card Saldo Akhir -->
        <div class="bg-surface-container-lowest border border-outline-variant/30 rounded-3xl p-lg flex flex-col gap-2 shadow-sm">
          <span class="font-label-sm text-[12px] font-bold text-on-surface-variant uppercase tracking-wider">Saldo Akhir</span>
          <span :class="['font-headline-lg text-[28px] font-bold', saldoAkhir >= 0 ? 'text-primary' : 'text-error']">
            Rp {{ formatRupiah(saldoAkhir) }}
          </span>
        </div>
      </div>

      <!-- Mobile Toggle -->
      <div class="md:hidden flex bg-surface-container rounded-full p-1 border border-outline-variant/50 mt-4">
        <button 
          @click="mobileView = 'pemasukan'"
          :class="[
            'flex-1 h-12 rounded-full font-label-md transition-colors',
            mobileView === 'pemasukan' ? 'bg-surface-container-lowest text-on-surface shadow-sm font-bold' : 'text-on-surface-variant'
          ]"
        >
          Pemasukan
        </button>
        <button 
          @click="mobileView = 'pengeluaran'"
          :class="[
            'flex-1 h-12 rounded-full font-label-md transition-colors',
            mobileView === 'pengeluaran' ? 'bg-surface-container-lowest text-on-surface shadow-sm font-bold' : 'text-on-surface-variant'
          ]"
        >
          Pengeluaran
        </button>
      </div>

      <!-- Daftar Laporan (2 Columns Desktop, Stack/Toggle Mobile) -->
      <div class="flex flex-col md:flex-row gap-lg md:gap-0 relative mt-4 md:mt-6">
        
        <!-- Garis Pemisah (Hanya Desktop) -->
        <div class="hidden md:block absolute left-1/2 top-0 bottom-0 w-px bg-outline-variant/30 transform -translate-x-1/2 z-0"></div>

        <!-- Kolom Pemasukan -->
        <div 
          v-show="mobileView === 'pemasukan' || isDesktop" 
          class="w-full md:w-1/2 md:pr-lg lg:pr-xl z-10 flex flex-col gap-md"
        >
          <h3 class="hidden md:block font-headline-sm text-on-surface mb-2">Pemasukan per Kelas</h3>
          <div class="flex flex-col gap-3">
            <div 
              v-for="item in pemasukanList" 
              :key="item.kelas"
              class="bg-surface-container-lowest border border-outline-variant/30 rounded-2xl p-4 flex items-center gap-4 shadow-sm"
            >
              <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined">account_balance_wallet</span>
              </div>
              <span class="font-label-md text-[15px] font-semibold text-on-surface flex-1">{{ item.kelas }}</span>
              <span class="font-label-md text-[16px] font-bold text-primary shrink-0">Rp {{ formatRupiah(item.nominal) }}</span>
            </div>
          </div>
        </div>

        <!-- Kolom Pengeluaran -->
        <div 
          v-show="mobileView === 'pengeluaran' || isDesktop" 
          class="w-full md:w-1/2 md:pl-lg lg:pl-xl z-10 flex flex-col gap-md"
        >
          <h3 class="hidden md:block font-headline-sm text-on-surface mb-2">Riwayat Pengeluaran</h3>
          <div class="flex flex-col gap-3">
            <div 
              v-for="item in pengeluaranList" 
              :key="item.id"
              class="bg-surface-container-lowest border border-outline-variant/30 rounded-2xl p-4 flex items-center gap-4 shadow-sm"
            >
              <!-- Date Box -->
              <div class="w-12 h-14 bg-surface-container rounded-xl flex flex-col items-center justify-center shrink-0">
                <span class="font-label-sm text-[12px] font-bold text-on-surface leading-tight">{{ getDay(item.tanggal) }}</span>
                <span class="font-label-sm text-[10px] font-medium text-on-surface-variant leading-tight">{{ getMonthShort(item.tanggal) }}</span>
              </div>
              
              <div class="flex-1 min-w-0">
                <h4 class="font-label-md text-[15px] font-semibold text-on-surface truncate">{{ item.keterangan }}</h4>
              </div>
              
              <span class="font-label-md text-[16px] font-bold text-error shrink-0">Rp {{ formatRupiah(item.jumlah) }}</span>
            </div>
          </div>
        </div>

      </div>

    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';

const getCurrentMonthIndo = () => {
  const date = new Date();
  const options = { month: 'long' };
  let month = new Intl.DateTimeFormat('id-ID', options).format(date);
  // Pastikan huruf pertama kapital (contoh: 'Juli', bukan 'juli')
  return month.charAt(0).toUpperCase() + month.slice(1);
};

const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const activeMonth = ref(getCurrentMonthIndo());

// Logika Responsif Toggle
const mobileView = ref('pemasukan'); // 'pemasukan' | 'pengeluaran'
const windowWidth = ref(window.innerWidth);

const updateWidth = () => {
  windowWidth.value = window.innerWidth;
};

// Data
const pemasukanList = ref([]);
const pengeluaranList = ref([]);
const totalPemasukan = ref(0);
const totalPengeluaran = ref(0);
const isLoading = ref(false);

const saldoAkhir = computed(() => {
  return totalPemasukan.value - totalPengeluaran.value;
});

const fetchLaporan = async () => {
  isLoading.value = true;
  try {
    const res = await axios.get(`/laporan?bulan=${activeMonth.value}`);
    pemasukanList.value = res.data.pemasukan_per_kelas;
    pengeluaranList.value = res.data.pengeluaran_list;
    totalPemasukan.value = res.data.total_pemasukan;
    totalPengeluaran.value = res.data.total_pengeluaran;
  } catch (err) {
    console.error("Gagal mengambil laporan", err);
  } finally {
    isLoading.value = false;
  }
};

watch(activeMonth, () => {
  fetchLaporan();
});

const isDownloading = ref(false);
const cetakPdf = async () => {
  if (isDownloading.value) return;
  isDownloading.value = true;
  try {
    const response = await axios.get(`/laporan/cetak-pdf?bulan=${activeMonth.value}`, { responseType: 'blob' });
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `laporan_keuangan_${activeMonth.value.toLowerCase()}_${new Date().getFullYear()}.pdf`);
    document.body.appendChild(link);
    link.click();
    link.remove();
  } catch (error) {
    console.error("Gagal export PDF", error);
    alert("Gagal mengunduh file laporan.");
  } finally {
    isDownloading.value = false;
  }
};

const isDesktop = computed(() => windowWidth.value >= 768);

onMounted(() => {
  window.addEventListener('resize', updateWidth);
  fetchLaporan();
});

onUnmounted(() => {
  window.removeEventListener('resize', updateWidth);
});

// Format Utilities
const getDay = (dateStr) => {
  return dateStr.split('-')[2];
};

const getMonthShort = (dateStr) => {
  const date = new Date(dateStr);
  const m = date.toLocaleString('id-ID', { month: 'short' });
  return m.replace('.', '');
};

const formatRupiah = (angka) => {
  return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
