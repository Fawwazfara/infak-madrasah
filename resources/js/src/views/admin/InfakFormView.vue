<template>
  <div class="ui-page flex flex-col gap-6">
    <!-- Header & Concentric Mode Switch -->
    <div class="ui-stagger ui-stagger-1 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <h2 class="text-2xl sm:text-[26px] font-bold text-[#1C1917] tracking-tight">Catat Infak Siswa</h2>
        <p class="text-sm text-[#57534E] mt-1">Input penerimaan infak tunai untuk satu siswa maupun massal satu kelas.</p>
      </div>

      <div class="ui-segmented self-start sm:self-auto">
        <button
          type="button"
          @click="setInputMode('satuan')"
          :class="['ui-segmented-item text-xs sm:text-sm', inputMode === 'satuan' ? 'ui-segmented-item-active' : 'ui-segmented-item-idle']"
        >
          Mode Satuan
        </button>
        <button
          type="button"
          @click="setInputMode('massal')"
          :class="['ui-segmented-item text-xs sm:text-sm', inputMode === 'massal' ? 'ui-segmented-item-active' : 'ui-segmented-item-idle']"
        >
          Mode Massal
        </button>
      </div>
    </div>

    <div
      v-if="isSuccess"
      role="status"
      class="rounded-[14px] border border-transparent bg-[#F9EFE7] shadow-[0_0_0_1px_oklch(0.55_0.13_42_/_0.22)] p-4 flex items-center gap-3 text-[#7C3A10]"
    >
      <UiIcon name="check_circle" :size="20" :weight="600" filled class="text-[#A8551E]" />
      <p class="text-sm font-semibold">Transaksi infak berhasil dicatat dan ditambahkan ke antrean konfirmasi WhatsApp.</p>
    </div>

    <div class="ui-stagger ui-stagger-2 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
      <!-- Form Steps -->
      <div class="lg:col-span-8 flex flex-col gap-6">
        <section class="ui-panel-padded space-y-5">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-[#44403C]" for="infakKelas">Pilih Kelas</label>
              <div class="relative">
                <select id="infakKelas" v-model="form.kelas_id" class="ui-select">
                  <option value="" disabled>-- Pilih Kelas --</option>
                  <option v-for="k in kelasi" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                </select>
                <UiIcon name="expand_more" :size="17" :weight="600" class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-[#78716C]" />
              </div>
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-[#44403C]" for="infakTanggal">Tanggal Terima</label>
              <input id="infakTanggal" v-model="form.tanggal" type="date" class="ui-input">
            </div>
          </div>

          <!-- Month Grid -->
          <div class="pt-4 border-t border-black/[0.06]">
            <label class="block text-sm font-bold text-[#1C1917] mb-3">
              {{ inputMode === 'satuan' ? 'Pilih Bulan Dibayar' : 'Pilih Bulan Tagihan Massal' }}
            </label>
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2.5">
              <button
                v-for="m in monthsList"
                :key="m"
                type="button"
                :disabled="inputMode === 'satuan' && paidMonths.includes(m)"
                @click="inputMode === 'satuan' ? toggleMonth(m) : toggleMonthMassal(m)"
                :class="[
                  'min-h-[46px] px-3 rounded-[10px] border border-transparent text-xs font-bold transition-[background-color,color,box-shadow,transform] duration-150 ease-out',
                  inputMode === 'satuan' && paidMonths.includes(m)
                    ? 'bg-[#F9EFE7] shadow-[inset_0_0_0_1px_oklch(0.55_0.13_42_/_0.22)] text-[#A8551E] opacity-70 cursor-not-allowed'
                    : form.selectedMonths.includes(m)
                      ? 'bg-[#292524] text-[#FDFCF9] shadow-[var(--shadow-btn-navy)] enabled:active:scale-[0.96]'
                      : 'bg-[#F7F4ED] text-[#1C1917] shadow-[var(--shadow-border)] hover:bg-[#FDFCF9] hover:shadow-[var(--shadow-border-hover)] enabled:active:scale-[0.96]'
                ]"
              >
                {{ m }}
              </button>
            </div>
          </div>

          <!-- Student Selection -->
          <div class="pt-4 border-t border-black/[0.06]" v-if="form.kelas_id">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
              <label class="text-sm font-bold text-[#1C1917]" for="siswaSearch">
                {{ inputMode === 'satuan' ? 'Pilih Siswa' : 'Pilih Siswa Belum Lunas' }}
              </label>
              <div v-if="inputMode === 'massal' && filteredStudents.length > 0" class="flex gap-2">
                <button type="button" @click="selectAllSiswa" class="text-xs font-semibold text-[#A8551E] hover:underline">Pilih Semua</button>
                <span class="text-[#D6D0C4]">|</span>
                <button type="button" @click="deselectAllSiswa" class="text-xs font-semibold text-[#78716C] hover:underline">Kosongkan</button>
              </div>
            </div>

            <div class="relative mb-3">
              <UiIcon name="search" :size="17" :weight="500" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#78716C]" />
              <input id="siswaSearch" v-model="searchQuery" type="text" placeholder="Cari nama siswa..." class="ui-input pl-10">
            </div>

            <div v-if="isFetchingStudents" class="p-6 text-center text-sm text-[#78716C]">Memuat daftar siswa...</div>
            <div
              v-else-if="filteredStudents.length === 0"
              class="p-6 text-center rounded-[12px] bg-[#F7F4ED] shadow-[inset_0_1px_2px_0_oklch(0.22_0.015_65_/_0.05),0_0_0_1px_oklch(0.22_0.015_65_/_0.07)] text-sm text-[#78716C]"
            >
              {{ inputMode === 'massal' && form.selectedMonths.length === 0 ? 'Pilih minimal satu bulan untuk menampilkan siswa yang belum lunas.' : 'Tidak ada siswa yang sesuai.' }}
            </div>

            <div v-else-if="inputMode === 'satuan'" class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-72 overflow-y-auto overscroll-contain pr-1">
              <button
                v-for="s in filteredStudents"
                :key="s.id"
                type="button"
                @click="form.siswa_id = s.id"
                :class="[
                  'min-h-[54px] p-3.5 rounded-[11px] text-left transition-[background-color,border-color,box-shadow,transform] duration-150 ease-out active:scale-[0.97] flex items-center gap-3',
                  form.siswa_id === s.id
                    ? 'bg-[#F9EFE7] border border-[#A8551E] shadow-[0_0_0_2px_oklch(0.55_0.13_42_/_0.14)]'
                    : 'bg-[#F7F4ED] border border-transparent shadow-[var(--shadow-border)] hover:bg-[#FDFCF9] hover:shadow-[var(--shadow-border-hover)]'
                ]"
              >
                <div class="w-9 h-9 rounded-[9px] bg-[#292524] text-[#FDFCF9] text-xs font-bold flex items-center justify-center shrink-0">
                  {{ getInitials(s.fullName) }}
                </div>
                <span class="text-sm font-semibold text-[#1C1917] truncate">{{ s.fullName }}</span>
              </button>
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-72 overflow-y-auto overscroll-contain pr-1">
              <label
                v-for="s in filteredStudents"
                :key="s.id"
                class="min-h-[50px] p-3 rounded-[11px] cursor-pointer flex items-center gap-3 transition-[background-color,border-color,box-shadow] duration-150 ease-out"
                :class="form.siswa_ids.includes(s.id)
                  ? 'bg-[#F9EFE7] border border-[#A8551E] shadow-[0_0_0_2px_oklch(0.55_0.13_42_/_0.14)]'
                  : 'bg-[#F7F4ED] border border-transparent shadow-[var(--shadow-border)] hover:bg-[#FDFCF9] hover:shadow-[var(--shadow-border-hover)]'"
              >
                <input type="checkbox" :value="s.id" v-model="form.siswa_ids" class="w-4 h-4 rounded border-[#D6D0C4] text-[#A8551E] focus:ring-[#A8551E]">
                <span class="text-sm font-semibold text-[#1C1917] truncate">{{ s.fullName }}</span>
              </label>
            </div>
          </div>

          <!-- Nominal -->
          <div class="pt-4 border-t border-black/[0.06]">
            <label class="block text-sm font-bold text-[#1C1917] mb-2.5" for="nominalInput">Nominal per Bulan (Rp)</label>
            <div class="flex flex-wrap gap-2 mb-3">
              <button
                v-for="preset in [25000, 30000, 40000, 50000]"
                :key="preset"
                type="button"
                @click="form.nominal = preset"
                :class="[
                  'min-h-[40px] px-4 rounded-[10px] border border-transparent text-xs font-mono font-bold tabular-nums transition-[background-color,color,box-shadow,transform] duration-150 ease-out active:scale-[0.96]',
                  Number(form.nominal) === preset
                    ? 'bg-[#292524] text-[#FDFCF9] shadow-[var(--shadow-btn-navy)]'
                    : 'bg-[#F7F4ED] text-[#1C1917] shadow-[var(--shadow-border)] hover:bg-[#FDFCF9] hover:shadow-[var(--shadow-border-hover)]'
                ]"
              >
                Rp {{ formatRupiah(preset) }}
              </button>
            </div>
            <input id="nominalInput" v-model.number="form.nominal" type="number" min="1000" step="1000" class="ui-input max-w-xs font-mono font-bold tabular-nums">
          </div>
        </section>
      </div>

      <!-- Summary Panel -->
      <aside class="lg:col-span-4 ui-panel-padded lg:sticky lg:top-24 flex flex-col gap-5">
        <h3 class="text-base font-bold text-[#1C1917] pb-3 border-b border-black/[0.06]">Ringkasan Input</h3>

        <div class="space-y-3 text-sm">
          <div class="flex justify-between">
            <span class="text-[#78716C]">Mode Input</span>
            <span class="font-semibold text-[#1C1917] capitalize">{{ inputMode }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-[#78716C]">Bulan Dipilih</span>
            <span class="font-semibold text-[#1C1917] tabular-nums">{{ form.selectedMonths.length }} bulan</span>
          </div>
          <div class="flex justify-between">
            <span class="text-[#78716C]">Jumlah Item Transaksi</span>
            <span class="font-mono font-semibold text-[#1C1917] tabular-nums">{{ totalTransaksi }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-[#78716C]">Nominal / Bulan</span>
            <span class="font-mono font-semibold text-[#1C1917] tabular-nums">Rp {{ formatRupiah(form.nominal) }}</span>
          </div>
        </div>

        <div class="pt-4 border-t border-black/[0.06] flex justify-between items-center">
          <span class="text-sm font-bold text-[#1C1917]">Total Penerimaan</span>
          <span class="text-xl font-extrabold font-mono tabular-nums text-[#A8551E]">Rp {{ formatRupiah(totalBayar) }}</span>
        </div>

        <button
          type="button"
          @click="saveTransaction"
          :disabled="isSubmitDisabled"
          class="ui-btn-primary ui-btn-icon-l w-full"
        >
          <UiIcon v-if="isSubmitting" name="progress_activity" :size="17" :weight="600" class="animate-spin" />
          <UiIcon v-else name="save" :size="17" :weight="600" />
          <span>{{ isSubmitting ? 'Menyimpan...' : 'Simpan Penerimaan Infak' }}</span>
        </button>

        <div class="pt-3 border-t border-black/[0.06] text-xs text-[#78716C] space-y-1.5">
          <p class="font-semibold text-[#44403C] inline-flex items-center gap-1.5">
            <UiIcon name="lightbulb" :size="14" :weight="600" class="text-[#A8551E]" />
            Tips Cepat
          </p>
          <p>Gunakan <strong>Mode Massal</strong> untuk mencatat beberapa siswa sekaligus. Konfirmasi WhatsApp akan berjalan bertahap di latar belakang.</p>
        </div>
      </aside>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import { drainOutbox } from '../../utils/waOutbox';
import { notify } from '@/utils/toast';

const monthsList = ['Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];

const isSubmitting = ref(false);
const isSuccess = ref(false);
const inputMode = ref('satuan');

const form = reactive({
  kelas_id: '',
  siswa_id: '',
  siswa_ids: [],
  tanggal: new Date().toISOString().split('T')[0],
  nominal: 30000,
  selectedMonths: []
});

const kelasi = ref([]);
const students = ref([]);
const searchQuery = ref('');
const isFetchingStudents = ref(false);
const paidMonths = ref([]);

const filteredStudents = computed(() => {
  if (!searchQuery.value) return students.value;
  const q = searchQuery.value.toLowerCase();
  return students.value.filter(s => String(s.fullName).toLowerCase().includes(q));
});

const isSubmitDisabled = computed(() => {
  if (isSubmitting.value) return true;
  if (form.selectedMonths.length === 0) return true;
  if (inputMode.value === 'satuan' && !form.siswa_id) return true;
  if (inputMode.value === 'massal' && form.siswa_ids.length === 0) return true;
  if (!form.kelas_id || !form.tanggal) return true;
  return false;
});

const setInputMode = (mode) => {
  inputMode.value = mode;
  form.kelas_id = '';
  form.siswa_id = '';
  form.siswa_ids = [];
  form.selectedMonths = [];
  students.value = [];
  paidMonths.value = [];
};

const selectAllSiswa = () => {
  form.siswa_ids = filteredStudents.value.map(s => s.id);
};

const deselectAllSiswa = () => {
  form.siswa_ids = [];
};

const getInitials = (name) => {
  if (!name) return 'S';
  const parts = String(name).trim().split(/\s+/);
  return parts.length >= 2 ? (parts[0][0] + parts[1][0]).toUpperCase() : String(name).substring(0, 2).toUpperCase();
};

onMounted(async () => {
  try {
    const res = await axios.get('/kelas');
    kelasi.value = res.data || [];
  } catch (e) {
    console.error('Gagal mengambil kelas', e);
  }
});

watch(() => form.kelas_id, async (newKelasId) => {
  if (inputMode.value !== 'satuan') return;
  form.siswa_id = '';
  form.selectedMonths = [];
  paidMonths.value = [];
  students.value = [];

  if (newKelasId) {
    isFetchingStudents.value = true;
    try {
      const res = await axios.get('/siswa');
      const cls = kelasi.value.find(k => k.id === newKelasId);
      students.value = (res.data || [])
        .filter(s => cls && s.kelas === cls.nama_kelas)
        .sort((a, b) => String(a.fullName).localeCompare(String(b.fullName), 'id'));
    } catch (e) {
      console.error(e);
    } finally {
      isFetchingStudents.value = false;
    }
  }
});

watch(() => form.siswa_id, async (newSiswaId) => {
  if (inputMode.value !== 'satuan') return;
  form.selectedMonths = [];
  paidMonths.value = [];

  if (newSiswaId) {
    try {
      const currentYear = new Date().getFullYear();
      const res = await axios.get(`/siswa/${newSiswaId}/infak?tahun=${currentYear}`);
      const engToIdMap = {
        January: 'Jan', February: 'Feb', March: 'Mar', April: 'Apr',
        May: 'Mei', June: 'Jun', July: 'Jul', August: 'Agu',
        September: 'Sep', October: 'Okt', November: 'Nov', December: 'Des'
      };
      paidMonths.value = (res.data || []).map(i => engToIdMap[i.bulan] || i.bulan);
    } catch (error) {
      console.error(error);
    }
  }
});

watch([() => form.kelas_id, () => form.selectedMonths], async ([newKelasId, newSelectedMonths]) => {
  if (inputMode.value !== 'massal') return;
  form.siswa_ids = [];
  students.value = [];

  if (newKelasId && newSelectedMonths.length > 0) {
    const selectedMonth = newSelectedMonths[0];
    isFetchingStudents.value = true;
    try {
      const currentYear = new Date().getFullYear();
      const res = await axios.get(`/kelas/${newKelasId}/unpaid-students?bulan=${selectedMonth}&tahun=${currentYear}`);
      students.value = [...(res.data || [])].sort((a, b) => String(a.fullName).localeCompare(String(b.fullName), 'id'));
    } catch (e) {
      console.error(e);
    } finally {
      isFetchingStudents.value = false;
    }
  }
}, { deep: true });

const toggleMonth = (month) => {
  const index = form.selectedMonths.indexOf(month);
  if (index === -1) form.selectedMonths.push(month);
  else form.selectedMonths.splice(index, 1);
};

const toggleMonthMassal = (month) => {
  const index = form.selectedMonths.indexOf(month);
  if (index === -1) form.selectedMonths.push(month);
  else form.selectedMonths.splice(index, 1);
  form.siswa_ids = [];
  students.value = [];
};

const totalTransaksi = computed(() => {
  if (inputMode.value === 'massal') return form.siswa_ids.length * form.selectedMonths.length;
  return form.selectedMonths.length;
});

const totalBayar = computed(() => totalTransaksi.value * Number(form.nominal || 0));

const formatRupiah = (angka) => Number(angka || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');

const saveTransaction = async () => {
  if (isSubmitDisabled.value) return;
  isSubmitting.value = true;

  try {
    const payload = {
      kelas_id: form.kelas_id,
      tanggal_bayar: form.tanggal,
      nominal: form.nominal,
      months: form.selectedMonths
    };
    if (inputMode.value === 'massal') payload.siswa_ids = form.siswa_ids;
    else payload.siswa_id = form.siswa_id;

    await axios.post('/infak', payload);
    drainOutbox().catch(() => {});

    isSuccess.value = true;
    notify('Penerimaan infak berhasil dicatat.', 'success');
    setTimeout(() => {
      isSuccess.value = false;
      form.kelas_id = '';
      form.siswa_id = '';
      form.siswa_ids = [];
      form.selectedMonths = [];
      form.nominal = 30000;
      form.tanggal = new Date().toISOString().split('T')[0];
    }, 1500);
  } catch (error) {
    console.error(error);
    notify(error.response?.data?.message || 'Gagal menyimpan penerimaan infak.', 'error');
  } finally {
    isSubmitting.value = false;
  }
};
</script>
