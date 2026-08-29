<template>
  <div class="flex-1 flex flex-col min-w-0 relative h-full">
    <!-- Page Header & Filters -->
    <div class="p-container-margin md:p-lg shrink-0">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-md mb-md">
        <h2 class="font-headline-lg-mobile md:font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background">Monitoring Pembayaran</h2>
        
        <div class="flex flex-wrap gap-sm w-full md:w-auto">
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
            v-if="selectedClass"
            @click="cetakFormSetoran"
            class="flex-1 md:flex-none bg-primary-container text-on-primary-container px-4 py-2 rounded-xl font-label-md text-label-md flex items-center justify-center gap-2 hover:bg-primary-container/90 transition-colors active:scale-95 shadow-sm whitespace-nowrap"
          >
            <span class="material-symbols-outlined text-[20px]">print</span> Cetak Form Setoran
          </button>
        </div>
        
        <div class="relative w-full md:w-64">
          <label class="sr-only" for="class-select">Pilih Kelas</label>
          <div class="relative">
            <select v-model="selectedClass" class="block w-full appearance-none bg-surface-container-low border border-outline-variant text-on-surface py-3 pl-4 pr-10 rounded-xl font-label-md text-label-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-colors" id="class-select">
              <option value="">Semua Kelas</option>
              <option v-for="c in classes" :key="c.id" :value="c.nama_kelas">{{ c.nama_kelas }}</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-on-surface-variant">
              <span class="material-symbols-outlined text-[20px]">arrow_drop_down</span>
            </div>
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
            <!-- Iterate Students -->
            <tr v-if="isLoading" class="border-none">
              <td colspan="14" class="p-lg text-center font-body-md text-on-surface-variant">Memuat data siswa...</td>
            </tr>
            <tr v-if="!isLoading && filteredStudents.length === 0" class="border-none">
              <td colspan="14" class="p-lg text-center font-body-md text-on-surface-variant">Belum ada data siswa.</td>
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
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const months = ['Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];

const students = ref([]);
const classes = ref([]);
const isLoading = ref(true);
const selectedClass = ref('');

const fetchClasses = async () => {
  try {
    const res = await axios.get('/kelas');
    classes.value = res.data;
  } catch (e) {
    console.error("Gagal mengambil kelas", e);
  }
};

const fetchStudents = async () => {
  try {
    const response = await axios.get('/siswa');
    students.value = response.data;
  } catch (error) {
    console.error("Failed to fetch students:", error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchClasses();
  fetchStudents();
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
  if (!selectedClass.value) return;
  const kelasObj = classes.value.find(c => c.nama_kelas === selectedClass.value);
  if (kelasObj) {
    window.open(`/api/kelas/${kelasObj.id}/form-setoran`, '_blank');
  }
};

const filteredStudents = computed(() => {
  if (!selectedClass.value) return students.value;
  return students.value.filter(s => s.kelas === selectedClass.value);
});

const modalStudent = ref(null);

const openModal = (student) => {
  modalStudent.value = student;
};

const closeModal = () => {
  modalStudent.value = null;
};

const studentToDelete = ref(null);

const confirmDelete = (student) => {
  studentToDelete.value = student;
  closeModal(); // close the info modal
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
