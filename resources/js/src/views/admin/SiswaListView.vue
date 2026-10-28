<template>
  <div class="flex-1 flex flex-col min-w-0 relative h-full">
    <!-- Page Header & Filters -->
    <div class="p-container-margin md:p-lg shrink-0">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-md mb-md">
        <h2 class="font-headline-lg-mobile md:font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background">Monitoring Pembayaran</h2>
        
        <div class="flex flex-wrap gap-3 w-full md:w-auto">
          <router-link 
            to="/admin/siswa/create" 
            class="flex-1 md:flex-none bg-primary text-white px-4 py-2 rounded-xl font-label-md text-label-md flex items-center justify-center gap-2 hover:bg-primary/90 transition-colors active:scale-95 whitespace-nowrap"
          >
            <span class="material-symbols-outlined text-[20px]">person_add</span> Tambah Siswa
          </router-link>
          
          <router-link 
            to="/admin/infak/create" 
            class="flex-1 md:flex-none border border-primary text-primary px-4 py-2 rounded-xl font-label-md text-label-md flex items-center justify-center gap-2 hover:bg-primary/5 transition-colors active:scale-95 whitespace-nowrap"
          >
            <span class="material-symbols-outlined text-[20px]">volunteer_activism</span> Catat Infak
          </router-link>

          <button 
            @click="exportSiswa"
            class="flex-1 md:flex-none bg-secondary text-on-secondary px-4 py-2 rounded-xl font-label-md text-label-md flex items-center justify-center gap-2 hover:bg-secondary/90 transition-colors active:scale-95 whitespace-nowrap"
          >
            <span class="material-symbols-outlined text-[20px]">download</span> Export Data
          </button>

          <button 
            v-if="currentClassObj"
            @click="cetakFormSetoran"
            class="flex-1 md:flex-none bg-primary-container text-on-primary-container px-4 py-2 rounded-xl font-label-md text-label-md flex items-center justify-center gap-2 hover:bg-primary-container/90 transition-colors active:scale-95 shadow-sm whitespace-nowrap"
          >
            <span class="material-symbols-outlined text-[20px]">print</span> Cetak Form Setoran
          </button>

          <button 
            @click="blastWA"
            :disabled="isBlasting"
            class="flex-1 md:flex-none bg-[#25D366] text-white px-4 py-2 rounded-xl font-label-md text-label-md flex items-center justify-center gap-2 hover:bg-[#128C7E] transition-colors active:scale-95 whitespace-nowrap disabled:opacity-70 disabled:cursor-not-allowed"
          >
            <span v-if="!isBlasting" class="material-symbols-outlined text-[20px]">campaign</span>
            <span v-else class="material-symbols-outlined animate-spin text-[20px]">sync</span>
            <template v-if="!isBlasting">Blast WA Tunggakan</template>
            <template v-else>Mengirim {{ blastProgress?.sent ?? 0 }}/{{ blastProgress?.total ?? '...' }} (jeda {{ blastDelay }} dtk)</template>
          </button>
        </div>
        
        <!-- Kelas Navigation: Buttons instead of dropdown -->
        <div class="w-full md:w-auto mt-4 md:mt-0">
          <div class="flex items-center justify-center md:justify-start gap-3">
            <!-- Prev Button -->
            <button 
              @click="navigateClass(-1)"
              :disabled="!hasPrevClass"
              class="flex items-center justify-center w-12 h-12 rounded-xl border border-outline-variant bg-surface-container-low text-on-surface hover:bg-surface-variant disabled:opacity-40 disabled:cursor-not-allowed transition-colors active:scale-95"
              aria-label="Kelas sebelumnya"
            >
              <span class="material-symbols-outlined text-[24px]">chevron_left</span>
            </button>

            <!-- Current Class Display -->
            <div class="flex-1 md:flex-none min-w-[200px] max-w-[300px]">
              <div class="bg-surface-container-low rounded-xl border border-outline-variant/50 px-4 py-3 flex items-center justify-center md:justify-start gap-3">
                <span class="material-symbols-outlined text-primary text-[24px]">class</span>
                <div class="text-left min-w-0">
                  <p class="font-label-sm text-on-surface-variant uppercase tracking-wider">Kelas Saat Ini</p>
                  <p class="font-headline-md text-headline-md text-primary truncate">{{ currentClassName }}</p>
                </div>
              </div>
            </div>

            <!-- Next Button -->
            <button 
              @click="navigateClass(1)"
              :disabled="!hasNextClass"
              class="flex items-center justify-center w-12 h-12 rounded-xl border border-outline-variant bg-surface-container-low text-on-surface hover:bg-surface-variant disabled:opacity-40 disabled:cursor-not-allowed transition-colors active:scale-95"
              aria-label="Kelas selanjutnya"
            >
              <span class="material-symbols-outlined text-[24px]">chevron_right</span>
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Data Table Container -->
    <div class="flex-1 overflow-auto bg-surface-container-lowest mx-container-margin md:mx-lg mb-container-margin md:mb-lg rounded-xl shadow-sm border border-surface-container-high relative">
      <div class="overflow-x-auto no-scrollbar pb-md">
        <table class="w-full text-left border-collapse whitespace-nowrap min-w-max">
          <thead>
            <tr class="bg-surface-container border-b border-outline-variant">
              <th class="bg-surface-container px-2 py-4 font-label-md text-label-md text-on-surface w-[40px] border-r border-b border-outline-variant text-center">#</th>
              <th class="sticky left-0 top-0 z-30 bg-surface-container p-md font-label-md text-label-md text-on-surface w-[120px] max-w-[120px] md:w-[200px] md:max-w-[200px] border-r border-b border-outline-variant shadow-[4px_0_10px_-5px_rgba(0,0,0,0.1)]">Nama</th>
              <th class="sticky top-0 z-20 bg-surface-container p-md font-label-md text-label-md text-on-surface-variant text-center w-16 border-b border-outline-variant" v-for="month in months" :key="month">{{ month }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant">
            <!-- Skeleton Loading Rows -->
            <tr v-if="isLoading" v-for="i in 5" :key="i" class="border-none">
              <td class="bg-surface-container-lowest px-2 py-4 border-r border-outline-variant text-center w-[40px]">
                <div class="h-4 w-8 bg-surface-container-high animate-pulse rounded"></div>
              </td>
              <td class="sticky left-0 z-20 bg-surface-container-lowest p-md border-r border-outline-variant w-[120px] max-w-[120px] md:w-[200px] md:max-w-[200px]">
                <div class="h-5 w-40 bg-surface-container-high animate-pulse rounded"></div>
              </td>
              <td class="p-sm text-center" v-for="m in months" :key="m">
                <div class="h-5 w-5 mx-auto bg-surface-container-high animate-pulse rounded-full"></div>
              </td>
            </tr>

            <tr v-if="!isLoading && filteredStudents.length === 0" class="border-none">
              <td colspan="14" class="p-lg text-center font-body-md text-on-surface-variant">Belum ada data siswa di kelas ini.</td>
            </tr>
            <tr 
              v-for="(student, index) in filteredStudents" 
              :key="student.id"
              class="hover:bg-surface-container-low transition-colors group"
            >
              <td class="bg-surface-container-lowest group-hover:bg-surface-container-low px-2 py-4 font-body-md text-body-md text-on-background border-r border-outline-variant text-center transition-colors w-[40px]">{{ index + 1 }}</td>
              <td class="sticky left-0 z-20 bg-surface-container-lowest group-hover:bg-surface-container-low p-md font-body-md text-body-md text-on-background border-r border-outline-variant font-medium w-[120px] max-w-[120px] md:w-[200px] md:max-w-[200px] shadow-[4px_0_10px_-5px_rgba(0,0,0,0.1)] transition-colors">
                <div class="flex items-center justify-between gap-1 overflow-hidden">
                  <span class="truncate w-full">{{ student.name }}</span>
                  <button 
                    class="shrink-0 text-primary hover:bg-primary/10 p-1 rounded-full transition-colors inline-flex items-center" 
                    @click.stop="openModal(student)"
                  >
                    <span class="material-symbols-outlined text-[18px]">info</span>
                  </button>
                </div>
              </td>
              <!-- Months Status -->
              <td class="p-sm text-center" v-for="(status, mIndex) in student.payments" :key="mIndex">
                <span v-if="status" class="material-symbols-outlined text-[#16A34A] text-[20px] fill">check_circle</span>
                <span v-else class="material-symbols-outlined text-outline-variant text-[20px]">cancel</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    

    <!-- Info Modal -->
    <div 
      v-if="modalStudent" 
      class="fixed inset-0 z-[100] flex items-center justify-center bg-black/40 p-container-margin" 
      @click="closeModal"
    >
      <div class="bg-surface rounded-xl shadow-lg w-full max-w-md overflow-hidden" @click.stop>
        <div class="p-lg border-b border-outline-variant flex justify-between items-center">
          <h3 class="font-headline-md text-primary">Info Siswa: {{ modalStudent.name }}</h3>
          <button class="text-on-surface-variant hover:bg-surface-variant p-1 rounded-full" @click="closeModal">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <div class="p-lg space-y-md">
          <div class="flex justify-between">
            <span class="text-on-surface-variant">Blok:</span>
            <span class="font-medium">{{ modalStudent.blok }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-on-surface-variant">Nama Wali:</span>
            <span class="font-medium">{{ modalStudent.waliName }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-on-surface-variant">No Telepon:</span>
            <a class="text-primary font-medium flex items-center gap-1" :href="'https://wa.me/' + modalStudent.phone.replace(/\D/g,'')" target="_blank">
              {{ modalStudent.phone }} <span class="material-symbols-outlined text-[16px]">open_in_new</span>
            </a>
          </div>
          <div class="mt-4 pt-4 border-t border-outline-variant flex flex-col sm:flex-row gap-2">
            <button class="flex-1 h-12 border-2 border-error text-error rounded-lg font-label-md text-label-md flex items-center justify-center gap-2 hover:bg-error/5 transition-colors active:scale-95" @click="confirmDelete(modalStudent)">
              <span class="material-symbols-outlined text-[18px]">delete</span> Hapus Siswa
            </button>
            <router-link :to="`/admin/siswa/${modalStudent.id}/edit`" class="flex-1 h-12 border-2 border-primary text-primary rounded-lg font-label-md text-label-md flex items-center justify-center gap-2 hover:bg-primary/5 transition-colors active:scale-95">
              <span class="material-symbols-outlined text-[18px]">edit</span> Edit Data
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div 
      v-if="studentToDelete" 
      class="fixed inset-0 z-[110] flex items-center justify-center bg-black/60 p-container-margin" 
      @click="cancelDelete"
    >
      <div class="bg-surface rounded-[24px] shadow-2xl w-full max-w-sm overflow-hidden p-6 text-center" @click.stop>
        <div class="mx-auto w-16 h-16 bg-error/10 rounded-full flex items-center justify-center mb-4 text-error">
          <span class="material-symbols-outlined text-[32px]">warning</span>
        </div>
        <h3 class="font-headline-md text-on-surface mb-2">Hapus Siswa?</h3>
        <p class="font-body-md text-on-surface-variant mb-6">Anda yakin ingin menghapus data <b>{{ studentToDelete.name }}</b>? Data infaknya juga akan terhapus.</p>
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
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import axios from 'axios';
import { drainOutbox } from '../../utils/waOutbox';

const months = ['Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];

// Urutan kelas tetap (sesuai DashboardController)
const kelasOrder = [
  'TK', 'Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4A', 'Kelas 4B',
  'Kelas 5', 'Kelas 6', 'Kelas 7', 'Kelas 8', 'Kelas 9', 'Kelas Ulya', 'Bambim'
];

const students = ref([]);
const classes = ref([]);
const isLoading = ref(true);
const isBlasting = ref(false);
const blastProgress = ref(null);
const blastDelay = ref(60);
const modalStudent = ref(null);
const studentToDelete = ref(null);

// Current class index in kelasOrder
const currentClassIndex = ref(0);

const fetchClasses = async () => {
  try {
    const res = await axios.get('/kelas');
    classes.value = res.data;
    
    // Sort classes according to kelasOrder
    classes.value.sort((a, b) => {
      const idxA = kelasOrder.indexOf(a.nama_kelas);
      const idxB = kelasOrder.indexOf(b.nama_kelas);
      return (idxA === -1 ? 99 : idxA) - (idxB === -1 ? 99 : idxB);
    });
    
    // Find TK index and set as default
    const tkIndex = classes.value.findIndex(c => c.nama_kelas === 'TK');
    if (tkIndex !== -1) {
      currentClassIndex.value = tkIndex;
    }
  } catch (e) {
    console.error("Gagal mengambil kelas", e);
  }
};

const fetchStudents = async () => {
  isLoading.value = true;
  try {
    // Pass kelas_id if currentClassObj is ready for server-side filtering
    const params = currentClassObj.value ? { kelas_id: currentClassObj.value.id } : {};
    const response = await axios.get('/siswa', { params });
    students.value = response.data;
  } catch (error) {
    console.error("Failed to fetch students:", error);
  } finally {
    isLoading.value = false;
  }
};

watch(currentClassIndex, () => {
  fetchStudents();
});

onMounted(() => {
  fetchClasses();
  fetchStudents();
  
  // Keyboard navigation
  window.addEventListener('keydown', handleKeydown);
});

const handleKeydown = (e) => {
  // Only navigate if not typing in input
  if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'TEXTAREA') return;
  
  if (e.key === 'ArrowLeft') {
    e.preventDefault();
    navigateClass(-1);
  } else if (e.key === 'ArrowRight') {
    e.preventDefault();
    navigateClass(1);
  }
};

const currentClassObj = computed(() => classes.value[currentClassIndex.value] || null);

const currentClassName = computed(() => currentClassObj.value?.nama_kelas || 'Belum ada kelas');

const hasPrevClass = computed(() => currentClassIndex.value > 0);

const hasNextClass = computed(() => currentClassIndex.value < classes.value.length - 1);

const navigateClass = (direction) => {
  const newIndex = currentClassIndex.value + direction;
  if (newIndex >= 0 && newIndex < classes.value.length) {
    currentClassIndex.value = newIndex;
  }
};

const filteredStudents = computed(() => {
  if (!currentClassObj.value) return [];
  return students.value.filter(s => s.kelas === currentClassObj.value.nama_kelas);
});

const exportSiswa = async () => {
  try {
    const response = await axios.get('/siswa/export', { responseType: 'blob' });
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `backup_siswa_${new Date().getTime()}.csv`);
    document.body.appendChild(link);
    link.click();
    link.remove();
  } catch (error) {
    console.error("Gagal export", error);
    alert("Gagal mengunduh file backup.");
  }
};

const cetakFormSetoran = () => {
  if (!currentClassObj.value) return;
  window.open(`/api/kelas/${currentClassObj.value.id}/form-setoran`, '_blank');
};

const blastWA = async () => {
  if (isBlasting.value) return;

  try {
    const st = await axios.get('/wa/status');
    blastDelay.value = st.data.delay || 60;
  } catch (e) {
    blastDelay.value = 60;
  }

  if (!confirm(`Kirim pesan WhatsApp penagihan massal ke wali santri yang menunggak 2 bulan atau lebih?\n\nPesan DIKIRIM BERTAHAP dengan jeda ${blastDelay.value} detik per pesan (agar nomor tidak keblokir). Jangan tutup tab ini sampai proses selesai.`)) return;

  isBlasting.value = true;
  blastProgress.value = null;
  try {
    const res = await axios.post('/infak/blast-wa');
    const queued = res.data.queued ?? 0;
    const remaining = res.data.remaining ?? 0;

    if (queued === 0 && remaining === 0) {
      alert(res.data.message || 'Tidak ada pesan baru untuk dikirim.');
      return;
    }

    // Kirim antrean bertahap (delay antar pesan dijaga server)
    await drainOutbox((p) => { blastProgress.value = p; });

    const sent = blastProgress.value?.sent ?? 0;
    alert(`Blast WA selesai. ${sent} pesan WhatsApp berhasil dikirim ke wali santri.`);
  } catch (error) {
    console.error("Gagal blast WA", error);
    alert(error.response?.data?.message || "Terjadi kesalahan saat mengirim Blast WA.");
  } finally {
    isBlasting.value = false;
    blastProgress.value = null;
  }
};

const openModal = (student) => {
  modalStudent.value = student;
};

const closeModal = () => {
  modalStudent.value = null;
};

const confirmDelete = (student) => {
  studentToDelete.value = student;
  closeModal();
};

const cancelDelete = () => {
  studentToDelete.value = null;
};

const executeDelete = async () => {
  if (studentToDelete.value) {
    try {
      await axios.delete(`/siswa/${studentToDelete.value.id}`);
      students.value = students.value.filter(s => s.id !== studentToDelete.value.id);
    } catch (error) {
      console.error("Failed to delete student:", error);
      alert("Gagal menghapus siswa");
    } finally {
      studentToDelete.value = null;
    }
  }
};

// Cleanup keyboard listener
import { onUnmounted } from 'vue';
onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown);
});
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