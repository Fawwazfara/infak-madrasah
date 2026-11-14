<template>
  <div class="ui-page flex flex-col gap-6">
    <!-- Header & Filter -->
    <div class="ui-stagger ui-stagger-1 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <h2 class="text-2xl sm:text-[26px] font-bold text-[#1C1917] tracking-tight">Kelas &amp; Daftar Siswa</h2>
        <p class="text-sm text-[#57534E] mt-1">Status pembayaran infak 12 bulan ajaran siswa di kelas yang Anda ampu.</p>
      </div>

      <div class="w-full sm:w-64" v-if="classes.length > 0">
        <label class="sr-only" for="class-select">Pilih Kelas</label>
        <div class="relative">
          <select id="class-select" v-model="selectedClass" @change="filterStudents" class="ui-select">
            <option value="">Semua Kelas Saya</option>
            <option v-for="c in classes" :key="c.id" :value="c.nama_kelas">{{ c.nama_kelas }}</option>
          </select>
          <UiIcon name="expand_more" :size="17" :weight="600" class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-[#78716C]" />
        </div>
      </div>
    </div>

    <!-- Matrix Table Container -->
    <div class="ui-stagger ui-stagger-2 ui-panel overflow-hidden">
      <div v-if="isLoading" class="p-8 space-y-3">
        <div v-for="n in 5" :key="n" class="h-12 bg-[#F7F4ED] rounded-[10px] animate-pulse"></div>
      </div>

      <div v-else-if="filteredStudents.length === 0" class="p-14 text-center">
        <div class="w-12 h-12 mx-auto rounded-[12px] bg-[#ECE8DF] text-[#78716C] flex items-center justify-center mb-3">
          <UiIcon name="group_off" :size="24" :weight="500" />
        </div>
        <p class="text-base font-bold text-[#1C1917]">Belum ada siswa</p>
        <p class="text-sm text-[#57534E] mt-1">Tidak ada data siswa yang sesuai untuk kelas yang dipilih.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap min-w-max">
          <thead>
            <tr class="bg-[#F7F4ED] border-b border-black/[0.06]">
              <th class="px-3 py-3.5 text-xs font-bold text-[#78716C] border-r border-black/[0.06] text-center w-12">#</th>
              <th class="sticky left-0 z-20 bg-[#F7F4ED] px-4 py-3.5 text-xs font-bold text-[#1C1917] min-w-[200px] border-r border-black/[0.06]">
                Nama Siswa
              </th>
              <th class="px-3 py-3.5 text-xs font-bold text-[#78716C] text-center w-14" v-for="month in months" :key="month">
                {{ month }}
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-black/[0.05]">
            <tr
              v-for="(student, index) in filteredStudents"
              :key="student.id"
              class="hover:bg-[#F7F4ED] transition-colors duration-100 ease-out group"
            >
              <td class="px-3 py-3.5 text-sm font-mono tabular-nums text-[#78716C] border-r border-black/[0.05] text-center">
                {{ index + 1 }}
              </td>
              <td class="sticky left-0 z-10 bg-[#FDFCF9] group-hover:bg-[#F7F4ED] px-4 py-3.5 text-sm font-semibold text-[#1C1917] border-r border-black/[0.05] transition-colors duration-100 ease-out">
                <div class="flex items-center justify-between gap-3">
                  <div class="min-w-0">
                    <p class="truncate max-w-[180px] sm:max-w-[240px]">{{ student.name }}</p>
                    <p class="text-[11px] font-medium text-[#78716C]">{{ student.kelas }}</p>
                  </div>
                  <button
                    type="button"
                    class="min-h-[34px] min-w-[34px] rounded-[9px] border border-transparent bg-[#F7F4ED] shadow-[var(--shadow-border)] hover:bg-[#FDFCF9] hover:shadow-[var(--shadow-border-hover)] text-[#A8551E] inline-flex items-center justify-center shrink-0 transition-[background-color,box-shadow,transform] duration-150 ease-out active:scale-[0.96]"
                    aria-label="Info Siswa &amp; Wali"
                    @click.stop="openModal(student)"
                  >
                    <UiIcon name="info" :size="16" :weight="600" />
                  </button>
                </div>
              </td>
              <td class="px-2.5 py-3.5 text-center" v-for="(status, mIndex) in student.payments" :key="mIndex">
                <span class="inline-flex items-center justify-center">
                  <UiIcon v-if="status" name="check_circle" :size="19" :weight="600" filled class="text-[#A8551E]" />
                  <UiIcon v-else name="cancel" :size="18" :weight="400" class="text-[#D6D0C4]" />
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Info Modal -->
    <div
      v-if="modalStudent"
      class="fixed inset-0 z-[100] flex items-center justify-center bg-[#1C1917]/45 backdrop-blur-[2px] p-4 overscroll-contain"
      @click="closeModal"
    >
      <div class="ui-panel-elevated w-full max-w-md overflow-hidden overscroll-contain" @click.stop>
        <div class="px-6 py-4 border-b border-black/[0.06] flex justify-between items-center">
          <div>
            <h3 class="text-base font-bold text-[#1C1917]">{{ modalStudent.name }}</h3>
            <p class="text-xs text-[#78716C]">{{ modalStudent.kelas }}</p>
          </div>
          <button
            type="button"
            class="min-h-[36px] min-w-[36px] text-[#78716C] hover:text-[#1C1917] hover:bg-[#ECE8DF] rounded-[9px] flex items-center justify-center transition-[background-color,color,transform] duration-150 ease-out active:scale-[0.96]"
            aria-label="Tutup modal"
            @click="closeModal"
          >
            <UiIcon name="close" :size="18" :weight="600" />
          </button>
        </div>

        <div class="p-6 space-y-4 text-sm">
          <div class="flex justify-between gap-3">
            <span class="text-[#78716C]">Blok / Alamat</span>
            <span class="font-semibold text-[#1C1917] text-right">{{ modalStudent.blok || '-' }}</span>
          </div>
          <div class="flex justify-between gap-3">
            <span class="text-[#78716C]">Nama Wali</span>
            <span class="font-semibold text-[#1C1917] text-right">{{ modalStudent.waliName || '-' }}</span>
          </div>
          <div class="flex justify-between gap-3">
            <span class="text-[#78716C]">No WhatsApp</span>
            <a
              v-if="modalStudent.phone"
              class="text-[#A8551E] font-semibold inline-flex items-center gap-1.5 hover:underline"
              :href="'https://wa.me/' + modalStudent.phone.replace(/\D/g, '')"
              target="_blank"
            >
              <span class="font-mono tabular-nums">{{ modalStudent.phone }}</span>
              <UiIcon name="open_in_new" :size="14" :weight="600" />
            </a>
            <span v-else class="text-[#78716C]">-</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { notify } from '@/utils/toast';

const months = ['Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];

const classes = ref([]);
const students = ref([]);
const filteredStudents = ref([]);
const selectedClass = ref('');
const isLoading = ref(true);
const modalStudent = ref(null);

const fetchClasses = async () => {
  try {
    const res = await axios.get('/kelas');
    classes.value = res.data || [];
  } catch (e) {
    console.error('Gagal mengambil kelas', e);
  }
};

const fetchStudents = async () => {
  isLoading.value = true;
  try {
    const res = await axios.get('/siswa');
    students.value = res.data || [];
    filteredStudents.value = res.data || [];
  } catch (e) {
    console.error('Gagal mengambil siswa', e);
    notify('Gagal memuat data siswa.', 'error');
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchClasses();
  fetchStudents();
});

const filterStudents = () => {
  if (selectedClass.value === '') {
    filteredStudents.value = students.value;
  } else {
    filteredStudents.value = students.value.filter(s => s.kelas === selectedClass.value);
  }
};

const openModal = (student) => {
  modalStudent.value = student;
};

const closeModal = () => {
  modalStudent.value = null;
};
</script>
