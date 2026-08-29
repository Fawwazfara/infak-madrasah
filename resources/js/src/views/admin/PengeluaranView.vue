<template>
  <div class="flex-1 flex flex-col min-w-0 h-full bg-surface-dim md:bg-transparent overflow-y-auto">
    <main class="w-full max-w-6xl mx-auto px-container-margin py-lg pb-24 md:pb-lg flex flex-col lg:flex-row gap-lg">
      
      <!-- Kolom Kiri: Form Tambah Pengeluaran -->
      <div class="w-full lg:w-1/3 flex flex-col gap-md">
        <!-- Optional Mobile Title if wanted, but AdminLayout has TopAppBar -->
        <h2 class="font-headline-lg-mobile md:font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background md:hidden mb-2">Pengeluaran</h2>

        <section class="bg-surface-container-lowest rounded-3xl shadow-sm md:shadow-md p-lg border border-outline-variant/30 flex flex-col gap-lg">
          <h2 class="font-headline-md text-headline-md text-on-surface">Tambah Pengeluaran</h2>
          
          <form class="flex flex-col gap-md" @submit.prevent="submitPengeluaran">
            <!-- Keperluan / Barang -->
            <div class="flex flex-col gap-xs">
              <label class="font-label-md text-label-md text-on-surface-variant" for="keperluan">Keperluan / Barang</label>
              <input v-model="form.keperluan" required class="w-full h-12 px-4 bg-surface rounded-xl border border-outline-variant text-body-md text-on-surface placeholder:text-outline/50 focus:outline-none focus:ring-4 focus:ring-primary/20 focus:border-primary transition-all" id="keperluan" placeholder="Contoh: Pembelian Alat Tulis" type="text">
            </div>
            
            <!-- Nominal (Rp) -->
            <div class="flex flex-col gap-xs">
              <label class="font-label-md text-label-md text-on-surface-variant" for="nominal">Nominal (Rp)</label>
              <input v-model="form.nominal" required class="w-full h-12 px-4 bg-surface rounded-xl border border-outline-variant text-body-md text-on-surface placeholder:text-outline/50 focus:outline-none focus:ring-4 focus:ring-primary/20 focus:border-primary transition-all" id="nominal" placeholder="0" type="number">
            </div>

            <!-- Tanggal -->
            <div class="flex flex-col gap-xs">
              <label class="font-label-md text-label-md text-on-surface-variant" for="tanggal">Tanggal</label>
              <div class="relative">
                <input v-model="form.tanggal" required type="date" class="w-full h-12 pl-4 pr-12 bg-surface rounded-xl border border-outline-variant text-body-md text-on-surface placeholder:text-outline/50 focus:outline-none focus:ring-4 focus:ring-primary/20 focus:border-primary transition-all block" id="tanggal">
              </div>
            </div>
            
            <!-- Submit Button -->
            <div class="mt-2">
              <button 
                type="submit"
                :disabled="isSubmitting"
                :class="[
                  'w-full h-12 font-label-md text-label-md rounded-xl transition-all shadow-md focus:outline-none flex items-center justify-center gap-2',
                  isSubmitting ? 'bg-secondary opacity-80 cursor-not-allowed text-white' : 'bg-primary text-on-primary hover:bg-primary/90 active:scale-95'
                ]"
              >
                <span v-if="!isSubmitting" class="material-symbols-outlined font-normal text-[20px]">add_task</span>
                <span v-if="isSubmitting" class="material-symbols-outlined animate-spin font-normal text-[20px]">sync</span>
                {{ isSubmitting ? 'Menyimpan...' : 'Catat Pengeluaran' }}
              </button>
            </div>
          </form>
        </section>
      </div>

      <!-- Kolom Kanan: Filter & Riwayat -->
      <div class="w-full lg:w-2/3 flex flex-col gap-md">
        
        <!-- Filter Bulan -->
        <div class="flex flex-col gap-sm">
          <label class="font-label-md text-label-md text-on-surface-variant">Filter Bulan</label>
          <div class="flex overflow-x-auto gap-3 pb-2 no-scrollbar">
            <button 
              v-for="f in filterOptions" 
              :key="f"
              type="button"
              @click="activeMonth = f"
              :class="[
                'h-10 px-5 rounded-full font-label-md text-label-md transition-all flex-shrink-0 border',
                activeMonth === f
                  ? 'bg-primary text-white border-primary shadow-sm'
                  : 'bg-surface-container-lowest text-on-surface-variant border-outline-variant/50 hover:bg-surface-container-high'
              ]"
            >
              {{ f }}
            </button>
          </div>
        </div>

        <!-- Riwayat Pengeluaran -->
        <section class="bg-surface-container-lowest rounded-3xl shadow-sm md:shadow-md border border-outline-variant/30 flex flex-col overflow-hidden">
          
          <div class="p-lg pb-md border-b border-surface-container-high flex items-start sm:items-center justify-between gap-4">
            <h2 class="font-headline-md text-[20px] text-on-surface">Riwayat<br class="sm:hidden" /> Pengeluaran</h2>
            <div class="bg-primary-fixed-dim/30 text-on-primary-fixed px-4 py-2 rounded-xl text-right flex flex-col sm:flex-row sm:items-center sm:gap-2 shrink-0">
              <span class="font-label-sm text-[11px] text-on-surface-variant sm:text-on-primary-fixed">Total:</span>
              <span class="font-headline-sm font-semibold text-on-primary-fixed-variant">Rp {{ formatRupiah(totalPengeluaran) }}</span>
            </div>
          </div>

          <div class="flex flex-col divide-y divide-surface-container-high px-4 pb-4">
            <!-- List Item -->
            <div 
              v-for="item in filteredRiwayat" 
              :key="item.id"
              class="flex items-center gap-4 py-4 px-2 hover:bg-surface-container-lowest transition-colors"
            >
              <!-- Date Box -->
              <div class="w-12 h-14 bg-surface-container rounded-xl flex flex-col items-center justify-center shrink-0">
                <span class="font-label-sm text-[12px] font-bold text-on-surface leading-tight">{{ getDay(item.tanggal) }}</span>
                <span class="font-label-sm text-[10px] font-medium text-on-surface-variant leading-tight">{{ getMonthShort(item.tanggal) }}</span>
              </div>
              
              <!-- Info -->
              <div class="flex-1 min-w-0">
                <h3 class="font-label-md text-[15px] font-bold text-on-surface truncate">{{ item.keterangan }}</h3>
                <p class="font-body-sm text-[14px] text-on-surface-variant mt-0.5 font-medium">Rp {{ formatRupiah(item.jumlah) }}</p>
              </div>
              
              <!-- Actions -->
              <div class="flex gap-2 shrink-0">
                <button 
                  @click="editItem(item)"
                  title="Edit"
                  class="w-10 h-10 rounded-xl bg-orange-50 text-orange-500 hover:bg-orange-100 flex items-center justify-center transition-colors"
                >
                  <span class="material-symbols-outlined text-[20px]">edit</span>
                </button>
                <button 
                  @click="confirmDelete(item)"
                  title="Hapus"
                  class="w-10 h-10 rounded-xl bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center transition-colors"
                >
                  <span class="material-symbols-outlined text-[20px]">delete</span>
                </button>
              </div>
            </div>

            <!-- Action Button See All -->
            <div class="pt-4 text-center">
              <router-link to="/admin/riwayat" class="font-label-md text-[14px] font-bold text-primary hover:text-primary-container transition-colors p-2">
                Lihat Semua Riwayat
              </router-link>
            </div>
          </div>
        </section>

      </div>
    </main>

    <!-- Delete Confirmation Modal -->
    <div 
      v-if="itemToDelete" 
      class="fixed inset-0 z-[110] flex items-center justify-center bg-black/60 p-container-margin" 
      @click="cancelDelete"
    >
      <div class="bg-surface rounded-[24px] shadow-2xl w-full max-w-sm overflow-hidden p-6 text-center" @click.stop>
        <div class="mx-auto w-16 h-16 bg-error/10 rounded-full flex items-center justify-center mb-4 text-error">
          <span class="material-symbols-outlined text-[32px]">warning</span>
        </div>
        <h3 class="font-headline-md text-on-surface mb-2">Hapus Pengeluaran?</h3>
        <p class="font-body-md text-on-surface-variant mb-6">Anda yakin ingin menghapus catatan pengeluaran <b>{{ itemToDelete.keterangan }}</b> senilai <b>Rp {{ formatRupiah(itemToDelete.jumlah) }}</b>?</p>
        <div class="flex gap-3">
          <button class="flex-1 h-12 bg-surface-container border border-outline-variant text-on-surface rounded-xl font-label-md transition-colors hover:bg-surface-variant active:scale-95" @click="cancelDelete">
            Batal
          </button>
          <button class="flex-1 h-12 bg-error text-white rounded-xl font-label-md transition-colors hover:bg-error/90 active:scale-95" @click="executeDelete">
            Ya, Hapus
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';

const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const filterOptions = ['Semua', ...months];
const activeMonth = ref(months[new Date().getMonth()]);
const isSubmitting = ref(false);

const form = reactive({
  keperluan: '',
  nominal: '',
  tanggal: ''
});

const riwayat = ref([]);

const fetchPengeluaran = async () => {
  try {
    const res = await axios.get('/pengeluaran');
    riwayat.value = res.data;
  } catch (error) {
    console.error("Gagal mengambil data pengeluaran", error);
  }
};

onMounted(() => {
  const today = new Date();
  form.tanggal = today.toISOString().split('T')[0];
  fetchPengeluaran();
});

const filteredRiwayat = computed(() => {
  if (activeMonth.value === 'Semua') return riwayat.value;
  return riwayat.value.filter(item => {
    const itemMonthIndex = new Date(item.tanggal).getMonth();
    return months[itemMonthIndex] === activeMonth.value;
  });
});

const totalPengeluaran = computed(() => {
  return filteredRiwayat.value.reduce((acc, curr) => acc + parseInt(curr.jumlah), 0);
});

const getDay = (dateStr) => {
  if (!dateStr) return '';
  return dateStr.split('-')[2] || dateStr.split('T')[0].split('-')[2];
};

const getMonthShort = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  const m = date.toLocaleString('id-ID', { month: 'short' });
  return m.replace('.', ''); // id-ID short month sometimes has dot
};

const formatRupiah = (angka) => {
  return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};

const submitPengeluaran = async () => {
  isSubmitting.value = true;
  try {
    const res = await axios.post('/pengeluaran', {
      keperluan: form.keperluan,
      nominal: form.nominal,
      tanggal: form.tanggal
    });
    
    // Refresh list
    fetchPengeluaran();
    
    form.keperluan = '';
    form.nominal = '';
    const today = new Date();
    form.tanggal = today.toISOString().split('T')[0];
  } catch (error) {
    console.error("Gagal menambah pengeluaran", error);
    alert('Gagal mencatat pengeluaran.');
  } finally {
    isSubmitting.value = false;
  }
};

const editItem = (item) => {
  alert(`Fungsi Edit untuk: ${item.keterangan} (Belum diimplementasi)`);
};

const itemToDelete = ref(null);

const confirmDelete = (item) => {
  itemToDelete.value = item;
};

const cancelDelete = () => {
  itemToDelete.value = null;
};

const executeDelete = async () => {
  if (itemToDelete.value) {
    try {
      await axios.delete(`/pengeluaran/${itemToDelete.value.id}`);
      fetchPengeluaran();
    } catch (error) {
      console.error("Gagal menghapus pengeluaran", error);
      alert('Gagal menghapus pengeluaran.');
    }
    itemToDelete.value = null;
  }
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
