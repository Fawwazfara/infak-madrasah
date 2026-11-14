<template>
  <div class="ui-page flex flex-col gap-6">
    <!-- Header & Action Toolbar -->
    <div class="ui-stagger ui-stagger-1 flex flex-col lg:flex-row lg:items-end justify-between gap-4">
      <div>
        <h2 class="text-2xl sm:text-[26px] font-bold text-[#1C1917] tracking-tight">Monitoring Pembayaran &amp; Siswa</h2>
        <p class="text-sm text-[#57534E] mt-1">Tabel status infak 12 bulan per kelas. Ketuk ikon info di sebelah nama untuk rincian wali.</p>
      </div>

      <div class="flex flex-wrap items-center gap-2.5">
        <router-link to="/admin/siswa/create" class="ui-btn-primary ui-btn-icon-l">
          <UiIcon name="person_add" :size="17" :weight="600" />
          <span>Tambah Siswa</span>
        </router-link>

        <router-link to="/admin/infak/create" class="ui-btn-secondary ui-btn-icon-l">
          <UiIcon name="volunteer_activism" :size="17" :weight="600" />
          <span>Catat Infak</span>
        </router-link>

        <button type="button" @click="exportSiswa" class="ui-btn-secondary ui-btn-icon-l">
          <UiIcon name="download" :size="17" :weight="600" />
          <span>Export Data</span>
        </button>

        <button
          v-if="currentClassObj"
          type="button"
          @click="cetakFormSetoran"
          class="ui-btn-secondary ui-btn-icon-l"
        >
          <UiIcon name="print" :size="17" :weight="600" />
          <span>Cetak Form Setoran</span>
        </button>

        <button
          type="button"
          @click="prepareBlastWA"
          :disabled="isBlasting"
          class="ui-btn-navy ui-btn-icon-l"
        >
          <UiIcon v-if="isBlasting" name="progress_activity" :size="17" :weight="600" class="animate-spin" />
          <UiIcon v-else name="campaign" :size="17" :weight="600" />
          <span v-if="!isBlasting">Blast WA Tunggakan</span>
          <span v-else>Mengirim {{ blastProgress?.sent ?? 0 }}/{{ blastProgress?.total ?? '...' }}</span>
        </button>
      </div>
    </div>

    <!-- Blast WA Progress Banner -->
    <div
      v-if="isBlasting && blastProgress"
      class="ui-panel-padded bg-[#F9EFE7] shadow-[0_0_0_1px_oklch(0.55_0.13_42_/_0.22)]"
    >
      <div class="flex items-center justify-between text-sm font-semibold text-[#7C3A10] mb-2">
        <span>Mengirim antrean WhatsApp bertahap (jeda {{ blastDelay }} dtk, jangan tutup tab)...</span>
        <span class="font-mono tabular-nums">{{ blastProgress.sent }} / {{ blastProgress.total }}</span>
      </div>
      <div class="w-full h-2.5 bg-[#EEDAC9] rounded-full overflow-hidden">
        <div
          class="h-full bg-[#A8551E] rounded-full transition-[width] duration-300 ease-out-expo"
          :style="{ width: blastProgress.total ? Math.round((blastProgress.sent / blastProgress.total) * 100) + '%' : '0%' }"
        ></div>
      </div>
    </div>

    <!-- Class Navigation Bar (Prev / Active / Next + Quick Pills) -->
    <div class="ui-stagger ui-stagger-2 ui-panel p-4 flex flex-col gap-3">
      <div class="flex items-center justify-between gap-3">
        <button
          type="button"
          @click="navigateClass(-1)"
          :disabled="!hasPrevClass"
          aria-label="Kelas sebelumnya"
          class="min-h-[44px] min-w-[44px] rounded-[11px] border border-transparent bg-[#F7F4ED] shadow-[var(--shadow-border)] hover:bg-[#FDFCF9] hover:shadow-[var(--shadow-border-hover)] text-[#1C1917] flex items-center justify-center transition-[background-color,box-shadow,transform] duration-150 ease-out enabled:active:scale-[0.96] disabled:opacity-40 disabled:cursor-not-allowed"
        >
          <UiIcon name="chevron_left" :size="20" :weight="600" />
        </button>

        <div class="text-center min-w-0">
          <span class="block text-xs text-[#78716C]">Kelas Aktif (panah ← →)</span>
          <span class="text-lg font-bold text-[#1C1917]">{{ currentClassName }}</span>
        </div>

        <button
          type="button"
          @click="navigateClass(1)"
          :disabled="!hasNextClass"
          aria-label="Kelas selanjutnya"
          class="min-h-[44px] min-w-[44px] rounded-[11px] border border-transparent bg-[#F7F4ED] shadow-[var(--shadow-border)] hover:bg-[#FDFCF9] hover:shadow-[var(--shadow-border-hover)] text-[#1C1917] flex items-center justify-center transition-[background-color,box-shadow,transform] duration-150 ease-out enabled:active:scale-[0.96] disabled:opacity-40 disabled:cursor-not-allowed"
        >
          <UiIcon name="chevron_right" :size="20" :weight="600" />
        </button>
      </div>

      <div class="flex flex-wrap gap-1.5 p-1 rounded-[13px] bg-[#ECE8DF]">
        <button
          v-for="(cls, idx) in classes"
          :key="cls.id"
          type="button"
          @click="currentClassIndex = idx"
          :class="[
            'px-3 py-1.5 rounded-[9px] text-xs font-semibold transition-[background-color,color,box-shadow,transform] duration-150 ease-out active:scale-[0.96]',
            currentClassIndex === idx
              ? 'bg-[#292524] text-[#FDFCF9] shadow-[var(--shadow-btn-navy)]'
              : 'text-[#57534E] hover:text-[#1C1917] hover:bg-[#FDFCF9]/80'
          ]"
        >
          {{ cls.nama_kelas }}
        </button>
      </div>
    </div>

    <!-- Checklist Matrix Table -->
    <div class="ui-stagger ui-stagger-3 ui-panel overflow-hidden">
      <div v-if="isLoading" class="p-8 space-y-3">
        <div v-for="n in 5" :key="n" class="h-12 bg-[#F7F4ED] rounded-[10px] animate-pulse"></div>
      </div>

      <div v-else-if="filteredStudents.length === 0" class="p-14 text-center">
        <div class="w-12 h-12 mx-auto rounded-[12px] bg-[#ECE8DF] text-[#78716C] flex items-center justify-center mb-3">
          <UiIcon name="group_off" :size="24" :weight="500" />
        </div>
        <p class="text-base font-bold text-[#1C1917]">Belum ada siswa di {{ currentClassName }}</p>
        <p class="text-sm text-[#57534E] mt-1">Tambahkan siswa baru atau pilih kelas lain di atas.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap min-w-max">
          <thead>
            <tr class="bg-[#F7F4ED] border-b border-black/[0.06]">
              <th class="px-3 py-3.5 text-xs font-bold text-[#78716C] border-r border-black/[0.06] text-center w-12">#</th>
              <th class="sticky left-0 z-20 bg-[#F7F4ED] px-4 py-3.5 text-xs font-bold text-[#1C1917] min-w-[210px] border-r border-black/[0.06]">
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
                  <span class="truncate max-w-[180px] sm:max-w-[240px]">{{ student.name }}</span>
                  <button
                    type="button"
                    class="min-h-[34px] min-w-[34px] rounded-[9px] border border-transparent bg-[#F7F4ED] shadow-[var(--shadow-border)] hover:bg-[#FDFCF9] hover:shadow-[var(--shadow-border-hover)] text-[#A8551E] inline-flex items-center justify-center shrink-0 transition-[background-color,box-shadow,transform] duration-150 ease-out active:scale-[0.96]"
                    aria-label="Detail &amp; Edit Siswa"
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

    <!-- Info Siswa Modal -->
    <div
      v-if="modalStudent"
      class="fixed inset-0 z-[100] flex items-center justify-center bg-[#1C1917]/45 backdrop-blur-[2px] p-4 overscroll-contain"
      @click="closeModal"
    >
      <div class="ui-panel-elevated w-full max-w-md overflow-hidden overscroll-contain max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="px-6 py-4 border-b border-black/[0.06] flex justify-between items-center">
          <div>
            <h3 class="text-base font-bold text-[#1C1917]">{{ modalStudent.name }}</h3>
            <p class="text-xs text-[#78716C]">{{ modalStudent.kelas || currentClassName }}</p>
          </div>
          <button
            type="button"
            class="min-h-[38px] min-w-[38px] text-[#78716C] hover:text-[#1C1917] hover:bg-[#ECE8DF] rounded-[9px] flex items-center justify-center transition-[background-color,color,transform] duration-150 ease-out active:scale-[0.96]"
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

          <div class="pt-4 border-t border-black/[0.06] flex flex-col sm:flex-row gap-2.5">
            <button type="button" class="ui-btn-danger ui-btn-icon-l flex-1" @click="confirmDelete(modalStudent)">
              <UiIcon name="delete" :size="16" :weight="600" />
              <span>Hapus Siswa</span>
            </button>
            <router-link :to="`/admin/siswa/${modalStudent.id}/edit`" class="ui-btn-secondary ui-btn-icon-l flex-1">
              <UiIcon name="edit" :size="16" :weight="600" />
              <span>Edit &amp; Infak</span>
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Blast WA Confirmation Modal -->
    <div
      v-if="confirmBlast"
      class="fixed inset-0 z-[110] flex items-center justify-center bg-[#1C1917]/50 backdrop-blur-[2px] p-4 overscroll-contain"
      @click="confirmBlast = false"
    >
      <div class="ui-panel-elevated w-full max-w-md p-6 text-center overscroll-contain" @click.stop>
        <div class="mx-auto w-14 h-14 bg-[#F9EFE7] text-[#A8551E] rounded-full flex items-center justify-center mb-4">
          <UiIcon name="campaign" :size="24" :weight="600" />
        </div>
        <h3 class="text-lg font-bold text-[#1C1917]">Kirim Blast WhatsApp Tunggakan?</h3>
        <p class="text-sm text-[#57534E] mt-2 mb-6 leading-relaxed">
          Pesan penagihan massal akan dikirim bertahap kepada wali santri yang menunggak 2 bulan atau lebih dengan jeda <strong>{{ blastDelay }} detik</strong> per pesan agar aman. Jangan tutup tab selama proses berjalan.
        </p>
        <div class="flex gap-3">
          <button type="button" class="ui-btn-secondary flex-1" @click="confirmBlast = false">Batal</button>
          <button type="button" class="ui-btn-navy flex-1" @click="executeBlastWA">Mulai Kirim</button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="studentToDelete"
      class="fixed inset-0 z-[110] flex items-center justify-center bg-[#1C1917]/50 backdrop-blur-[2px] p-4 overscroll-contain"
      @click="cancelDelete"
    >
      <div class="ui-panel-elevated w-full max-w-sm p-6 text-center overscroll-contain" @click.stop>
        <div class="mx-auto w-14 h-14 bg-[#FDF2F4] shadow-[var(--shadow-btn-danger)] rounded-full flex items-center justify-center mb-4 text-[#9F1239]">
          <UiIcon name="warning" :size="24" :weight="600" />
        </div>
        <h3 class="text-lg font-bold text-[#1C1917]">Hapus Siswa?</h3>
        <p class="text-sm text-[#57534E] mt-2 mb-6 leading-relaxed">
          Data <strong>{{ studentToDelete.name }}</strong> beserta seluruh catatan infaknya akan dihapus permanen.
        </p>
        <div class="flex gap-3">
          <button type="button" class="ui-btn-secondary flex-1" @click="cancelDelete">Batal</button>
          <button type="button" class="ui-btn-danger flex-1" @click="executeDelete">Ya, Hapus</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';
import { drainOutbox } from '../../utils/waOutbox';
import { notify } from '@/utils/toast';

const months = ['Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];

const kelasOrder = [
  'TK', 'Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4A', 'Kelas 4B',
  'Kelas 5', 'Kelas 6', 'Kelas 7', 'Kelas 8', 'Kelas 9', 'Kelas Ulya', 'Bambim'
];

const students = ref([]);
const classes = ref([]);
const isLoading = ref(true);
const isBlasting = ref(false);
const confirmBlast = ref(false);
const blastProgress = ref(null);
const blastDelay = ref(60);
const modalStudent = ref(null);
const studentToDelete = ref(null);

const currentClassIndex = ref(0);

const fetchClasses = async () => {
  try {
    const res = await axios.get('/kelas');
    classes.value = res.data || [];
    classes.value.sort((a, b) => {
      const idxA = kelasOrder.indexOf(a.nama_kelas);
      const idxB = kelasOrder.indexOf(b.nama_kelas);
      return (idxA === -1 ? 99 : idxA) - (idxB === -1 ? 99 : idxB);
    });
    const tkIndex = classes.value.findIndex(c => c.nama_kelas === 'TK');
    if (tkIndex !== -1) currentClassIndex.value = tkIndex;
  } catch (e) {
    console.error('Gagal mengambil kelas', e);
  }
};

const fetchStudents = async () => {
  isLoading.value = true;
  try {
    const params = currentClassObj.value ? { kelas_id: currentClassObj.value.id } : {};
    const response = await axios.get('/siswa', { params });
    students.value = response.data || [];
  } catch (error) {
    console.error('Failed to fetch students:', error);
    notify('Gagal memuat daftar siswa.', 'error');
  } finally {
    isLoading.value = false;
  }
};

watch(currentClassIndex, () => {
  fetchStudents();
});

const handleKeydown = (e) => {
  if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'TEXTAREA') return;
  if (e.key === 'ArrowLeft') {
    e.preventDefault();
    navigateClass(-1);
  } else if (e.key === 'ArrowRight') {
    e.preventDefault();
    navigateClass(1);
  }
};

onMounted(async () => {
  await fetchClasses();
  await fetchStudents();
  window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown);
});

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
    notify('File backup siswa berhasil diunduh.', 'success');
  } catch (error) {
    console.error('Gagal export', error);
    notify('Gagal mengunduh file backup.', 'error');
  }
};

const cetakFormSetoran = () => {
  if (!currentClassObj.value) return;
  window.open(`/api/kelas/${currentClassObj.value.id}/form-setoran`, '_blank');
};

const prepareBlastWA = async () => {
  if (isBlasting.value) return;
  try {
    const st = await axios.get('/wa/status');
    blastDelay.value = st.data.delay || 60;
  } catch {
    blastDelay.value = 60;
  }
  confirmBlast.value = true;
};

const executeBlastWA = async () => {
  confirmBlast.value = false;
  isBlasting.value = true;
  blastProgress.value = null;
  try {
    const res = await axios.post('/infak/blast-wa');
    const queued = res.data.queued ?? 0;
    const remaining = res.data.remaining ?? 0;

    if (queued === 0 && remaining === 0) {
      notify(res.data.message || 'Tidak ada pesan tunggakan baru untuk dikirim.', 'info');
      return;
    }

    await drainOutbox((p) => { blastProgress.value = p; });
    const sent = blastProgress.value?.sent ?? 0;
    notify(`Blast WA selesai. ${sent} pesan berhasil dikirim ke wali santri.`, 'success');
  } catch (error) {
    console.error('Gagal blast WA', error);
    notify(error.response?.data?.message || 'Terjadi kesalahan saat mengirim Blast WA.', 'error');
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
  if (!studentToDelete.value) return;
  try {
    await axios.delete(`/siswa/${studentToDelete.value.id}`);
    students.value = students.value.filter(s => s.id !== studentToDelete.value.id);
    notify('Data siswa berhasil dihapus.', 'success');
  } catch (error) {
    console.error('Failed to delete student:', error);
    notify('Gagal menghapus siswa.', 'error');
  } finally {
    studentToDelete.value = null;
  }
};
</script>
