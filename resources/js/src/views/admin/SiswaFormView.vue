<template>
  <div class="ui-page flex flex-col gap-6">
    <!-- Header -->
    <div class="ui-stagger ui-stagger-1">
      <router-link
        to="/admin/siswa"
        class="text-sm font-semibold text-[#57534E] hover:text-[#1C1917] inline-flex items-center gap-1.5 mb-2 transition-[color,transform] duration-150 ease-out active:scale-[0.97]"
      >
        <UiIcon name="arrow_back" :size="16" :weight="600" directional />
        <span>Kembali ke Daftar Siswa</span>
      </router-link>
      <h2 class="text-2xl sm:text-[26px] font-bold text-[#1C1917] tracking-tight">
        {{ isEdit ? 'Ubah Data Siswa &amp; Status Infak' : 'Tambah Data Siswa Baru' }}
      </h2>
      <p class="text-sm text-[#57534E] mt-1">Lengkapi biodata santri serta kontak wali yang digunakan untuk pengingat WhatsApp.</p>
    </div>

    <div
      v-if="isSuccess"
      role="status"
      class="rounded-[14px] border border-transparent bg-[#F9EFE7] shadow-[0_0_0_1px_oklch(0.55_0.13_42_/_0.22)] p-4 flex items-center gap-3 text-[#7C3A10]"
    >
      <UiIcon name="check_circle" :size="20" :weight="600" filled class="text-[#A8551E]" />
      <p class="text-sm font-semibold">Data siswa berhasil disimpan. Mengalihkan kembali ke daftar...</p>
    </div>

    <form @submit.prevent="saveSiswa" class="ui-stagger ui-stagger-2 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
      <!-- Left: Biodata & Kontak Wali -->
      <div class="lg:col-span-7 flex flex-col gap-6">
        <section class="ui-panel-padded space-y-5">
          <h3 class="text-base font-bold text-[#1C1917] pb-3 border-b border-black/[0.06]">Biodata Siswa</h3>

          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-[#44403C]" for="namaLengkap">Nama Lengkap Siswa</label>
            <input id="namaLengkap" v-model="form.namaLengkap" type="text" required placeholder="Contoh: Ahmad Fauzan" class="ui-input">
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-[#44403C]" for="kelasId">Kelas</label>
              <div class="relative">
                <select id="kelasId" v-model="form.kelas_id" required class="ui-select">
                  <option value="" disabled>Pilih Kelas</option>
                  <option v-for="k in kelasi" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                </select>
                <UiIcon name="expand_more" :size="17" :weight="600" class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-[#78716C]" />
              </div>
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-[#44403C]" for="blok">Blok / RT-RW</label>
              <input id="blok" v-model="form.blok" type="text" placeholder="Contoh: Blok B2 / RT 04" class="ui-input">
            </div>
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-[#44403C]" for="alamat">Alamat Lengkap</label>
            <textarea id="alamat" v-model="form.alamat" rows="3" placeholder="Alamat tempat tinggal siswa" class="ui-input min-h-[88px] py-3"></textarea>
          </div>
        </section>

        <section class="ui-panel-padded space-y-5">
          <h3 class="text-base font-bold text-[#1C1917] pb-3 border-b border-black/[0.06]">Kontak Orang Tua / Wali</h3>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-[#44403C]" for="namaWali1">Nama Wali 1 (Utama)</label>
              <input id="namaWali1" v-model="form.namaWali1" type="text" placeholder="Nama ayah / ibu" class="ui-input">
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-[#44403C]" for="waWali1">No WhatsApp Wali 1</label>
              <input id="waWali1" v-model="form.waWali1" type="tel" placeholder="08xxxxxxxxxx" class="ui-input font-mono tabular-nums">
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-black/[0.06]">
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-[#44403C]" for="namaWali2">Nama Wali 2 (Cadangan)</label>
              <input id="namaWali2" v-model="form.namaWali2" type="text" placeholder="Opsional" class="ui-input">
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-[#44403C]" for="waWali2">No WhatsApp Wali 2</label>
              <input id="waWali2" v-model="form.waWali2" type="tel" placeholder="Opsional" class="ui-input font-mono tabular-nums">
            </div>
          </div>
        </section>
      </div>

      <!-- Right: Status Infak (Edit Mode) & Action -->
      <div class="lg:col-span-5 flex flex-col gap-6">
        <section v-if="isEdit" class="ui-panel-padded space-y-4">
          <div class="flex items-center justify-between gap-2 pb-3 border-b border-black/[0.06]">
            <div>
              <h3 class="text-base font-bold text-[#1C1917]">Status Infak {{ academicYearLabel }}</h3>
              <p class="text-xs text-[#78716C] mt-0.5">Ketuk bulan untuk menandai/ubah, atau ketuk Opsi untuk pindah bulan.</p>
            </div>
            <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-[8px] bg-[#F9EFE7] text-[#A8551E] tabular-nums shrink-0">
              {{ paidMonthsCount }}/12
            </span>
          </div>

          <div class="flex flex-wrap items-center gap-3 text-[11px] font-semibold text-[#78716C]">
            <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#A8551E]"></span> Lunas</span>
            <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#ECE8DF] border border-[#D6D0C4]"></span> Belum</span>
            <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#9A6B16]"></span> Draft Ubah</span>
          </div>

          <div class="grid grid-cols-3 sm:grid-cols-4 gap-2.5">
            <div v-for="cell in monthCells" :key="cell.short" class="flex flex-col gap-1">
              <button
                type="button"
                @click="toggleMonthDraft(cell)"
                :aria-pressed="cell.paid || cell.draftPaid === true"
                :class="[
                  'min-h-[54px] px-2.5 py-2 rounded-[10px] border border-transparent text-center flex flex-col items-center justify-center transition-[background-color,color,box-shadow,transform] duration-150 ease-out active:scale-[0.96]',
                  cell.draftPaid === true
                    ? 'bg-[#A8551E] text-[#FDFCF9] shadow-[var(--shadow-btn-primary)]'
                    : cell.draftPaid === false
                      ? 'bg-[#FDF2F4] text-[#9F1239] shadow-[var(--shadow-btn-danger)]'
                      : cell.paid
                        ? 'bg-[#F9EFE7] text-[#7C3A10] shadow-[inset_0_0_0_1px_oklch(0.55_0.13_42_/_0.22)]'
                        : 'bg-[#F7F4ED] text-[#57534E] shadow-[var(--shadow-border)] hover:bg-[#FDFCF9] hover:shadow-[var(--shadow-border-hover)]'
                ]"
              >
                <span class="text-xs font-bold">{{ cell.short }}</span>
                <span class="text-[11px] mt-0.5">
                  {{ cell.draftPaid === true ? '+Lunas' : cell.draftPaid === false ? '-Hapus' : cell.paid ? 'Lunas' : 'Belum' }}
                </span>
              </button>
              <button
                v-if="cell.paid"
                type="button"
                @click="openMonthModal(cell)"
                class="text-xs text-[#A8551E] hover:underline py-0.5 font-medium"
              >
                Opsi
              </button>
            </div>
          </div>

          <div v-if="hasDraftChanges" class="p-3.5 rounded-[11px] bg-[#FAF4E4] shadow-[inset_0_0_0_1px_oklch(0.62_0.12_75_/_0.28)] text-xs text-[#6E4A0B]">
            <p class="font-bold">Perubahan tertunda (diterapkan saat simpan):</p>
            <p class="font-mono mt-1">{{ draftChangesSummary.join(', ') }}</p>
          </div>
        </section>

        <section class="ui-panel-padded flex flex-col gap-3">
          <button type="submit" :disabled="isSubmitting" class="ui-btn-primary ui-btn-icon-l w-full">
            <UiIcon v-if="isSubmitting" name="progress_activity" :size="17" :weight="600" class="animate-spin" />
            <UiIcon v-else name="save" :size="17" :weight="600" />
            <span>{{ isSubmitting ? 'Menyimpan...' : (isEdit ? 'Simpan Perubahan Siswa' : 'Simpan Data Siswa') }}</span>
          </button>
          <router-link to="/admin/siswa" class="ui-btn-secondary w-full">Batal</router-link>
        </section>
      </div>
    </form>

    <!-- Month Option Modal -->
    <div v-if="showMonthModal && activeModalMonth" class="fixed inset-0 z-50 flex items-center justify-center bg-[#1C1917]/45 backdrop-blur-[2px] p-4 overscroll-contain" @click="closeMonthModal">
      <div class="ui-panel-elevated w-full max-w-sm p-6 space-y-4 overscroll-contain" @click.stop>
        <div class="flex items-center justify-between">
          <h3 class="text-base font-bold text-[#1C1917]">Opsi Bulan {{ monthFullName(activeModalMonth.short) }}</h3>
          <button
            type="button"
            @click="closeMonthModal"
            aria-label="Tutup modal"
            class="min-h-[38px] min-w-[38px] rounded-[9px] text-[#78716C] hover:text-[#1C1917] hover:bg-[#ECE8DF] flex items-center justify-center transition-[background-color,color,transform] duration-150 ease-out active:scale-[0.96]"
          >
            <UiIcon name="close" :size="18" :weight="600" />
          </button>
        </div>

        <div v-if="activeModalMonth.record" class="p-3 rounded-[11px] bg-[#F7F4ED] text-xs space-y-1 text-[#57534E]">
          <div>Tanggal Bayar: <strong class="text-[#1C1917]">{{ activeModalMonth.record.tanggal_bayar || '-' }}</strong></div>
          <div>Nominal: <strong class="font-mono text-[#1C1917]">Rp {{ Number(activeModalMonth.record.jumlah || 0).toLocaleString('id-ID') }}</strong></div>
        </div>

        <div class="flex flex-col gap-2.5">
          <button type="button" @click="modalActionToggle" class="ui-btn-danger ui-btn-icon-l w-full">
            <UiIcon name="delete" :size="16" :weight="600" />
            <span>Tandai Hapus Pembayaran</span>
          </button>
          <button type="button" @click="startMoveMonth" class="ui-btn-secondary ui-btn-icon-l w-full">
            <UiIcon name="swap_horiz" :size="16" :weight="600" />
            <span>Pindahkan ke Bulan Lain</span>
          </button>
        </div>

        <div v-if="isMovingMonth" class="p-4 rounded-[12px] bg-[#F7F4ED] shadow-[inset_0_1px_2px_0_oklch(0.22_0.015_65_/_0.05),0_0_0_1px_oklch(0.22_0.015_65_/_0.07)] space-y-3">
          <label class="block text-xs font-semibold text-[#1C1917]" for="moveMonthSelect">Pilih bulan tujuan:</label>
          <div class="relative">
            <select id="moveMonthSelect" v-model="targetMoveMonth" class="ui-select">
              <option value="" disabled>Pilih bulan tujuan</option>
              <option v-for="m in monthsList.filter(m => !paidByMonth[m] && m !== activeModalMonth?.short)" :key="m" :value="m">
                {{ m }}
              </option>
            </select>
            <UiIcon name="expand_more" :size="17" :weight="600" class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-[#78716C]" />
          </div>
          <div class="flex justify-end gap-2">
            <button type="button" @click="isMovingMonth = false" class="ui-btn-secondary">Batal</button>
            <button type="button" @click="confirmMoveMonth" class="ui-btn-primary">Pindahkan</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import { notify } from '@/utils/toast';

const router = useRouter();
const route = useRoute();

const isSubmitting = ref(false);
const isSuccess = ref(false);
const kelasi = ref([]);

const studentId = computed(() => route.params.id);
const isEdit = computed(() => !!studentId.value);

const monthsList = ['Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];
const academicYearLabel = (() => {
  const now = new Date();
  const start = now.getMonth() >= 6 ? now.getFullYear() : now.getFullYear() - 1;
  return `${start}/${start + 1}`;
})();

const monthNameMap = {
  Jul: 'July', Agu: 'August', Sep: 'September', Okt: 'October',
  Nov: 'November', Des: 'December', Jan: 'January', Feb: 'February',
  Mar: 'March', Apr: 'April', Mei: 'May', Jun: 'June'
};

const engToShortMap = Object.fromEntries(
  Object.entries(monthNameMap).map(([short, full]) => [full, short])
);

const paidByMonth = ref({});
const draftMonths = ref({});

const form = reactive({
  namaLengkap: '',
  alamat: '',
  blok: '',
  kelas_id: '',
  namaWali1: '',
  waWali1: '',
  namaWali2: '',
  waWali2: ''
});

onMounted(async () => {
  try {
    const res = await axios.get('/kelas');
    kelasi.value = res.data || [];

    if (isEdit.value) {
      const studentRes = await axios.get(`/siswa/${studentId.value}`);
      const s = studentRes.data;
      form.namaLengkap = s.nama_lengkap;
      form.kelas_id = s.kelas_id;
      form.alamat = s.alamat || '';
      form.blok = s.blok || '';
      form.namaWali1 = s.nama_wali_1 || '';
      form.waWali1 = s.wa_wali_1 || '';
      form.namaWali2 = s.nama_wali_2 || '';
      form.waWali2 = s.wa_wali_2 || '';
      await refreshInfak();
    }
  } catch (err) {
    console.error('Gagal mengambil data', err);
    notify('Gagal memuat data siswa.', 'error');
  }
});

const monthCells = computed(() =>
  monthsList.map((short) => {
    const record = paidByMonth.value[short] || null;
    const draft = draftMonths.value[short] ?? null;
    return {
      short,
      paid: !!record,
      record,
      draftPaid: draft
    };
  })
);

const hasDraftChanges = computed(() =>
  Object.values(draftMonths.value).some(v => v !== null && v !== undefined)
);

const draftChangesSummary = computed(() => {
  const summary = [];
  Object.entries(draftMonths.value).forEach(([short, val]) => {
    if (val === true) summary.push(`+ ${short}`);
    if (val === false) summary.push(`- ${short}`);
  });
  return summary;
});

const paidMonthsCount = computed(() =>
  monthCells.value.filter(c => (c.paid && c.draftPaid !== false) || c.draftPaid === true).length
);

const monthFullName = (short) => monthNameMap[short] || short;

const refreshInfak = async () => {
  try {
    const res = await axios.get(`/siswa/${studentId.value}/infak`);
    const map = {};
    (res.data || []).forEach((i) => {
      const short = engToShortMap[i.bulan] || i.bulan;
      if (!map[short]) map[short] = i;
    });
    paidByMonth.value = map;
    draftMonths.value = {};
  } catch (error) {
    console.error('Gagal memuat riwayat infak', error);
  }
};

const toggleMonthDraft = (cell) => {
  if (cell.draftPaid !== null) {
    draftMonths.value[cell.short] = null;
  } else if (cell.paid) {
    draftMonths.value[cell.short] = false;
  } else {
    draftMonths.value[cell.short] = true;
  }
};

const showMonthModal = ref(false);
const activeModalMonth = ref(null);
const isMovingMonth = ref(false);
const targetMoveMonth = ref('');

const openMonthModal = (cell) => {
  activeModalMonth.value = cell;
  isMovingMonth.value = false;
  targetMoveMonth.value = '';
  showMonthModal.value = true;
};

const closeMonthModal = () => {
  showMonthModal.value = false;
  activeModalMonth.value = null;
  isMovingMonth.value = false;
  targetMoveMonth.value = '';
};

const modalActionToggle = () => {
  if (!activeModalMonth.value) return;
  const short = activeModalMonth.value.short;
  draftMonths.value[short] = activeModalMonth.value.paid ? false : true;
  closeMonthModal();
};

const startMoveMonth = () => {
  isMovingMonth.value = true;
};

const confirmMoveMonth = () => {
  if (!activeModalMonth.value || !targetMoveMonth.value) return;
  draftMonths.value[activeModalMonth.value.short] = false;
  draftMonths.value[targetMoveMonth.value] = true;
  closeMonthModal();
};

const applyDraftInfakChanges = async () => {
  const changes = Object.entries(draftMonths.value).filter(([, v]) => v !== null && v !== undefined);
  if (changes.length === 0) return;

  const toAdd = changes.filter(([, v]) => v === true).map(([short]) => short);
  const currentPaidMonths = Object.keys(paidByMonth.value);
  const finalMonths = [...new Set(
    currentPaidMonths.filter(m => draftMonths.value[m] !== false).concat(toAdd)
  )];

  const currentYear = new Date().getFullYear();
  const akademikStart = new Date().getMonth() >= 6 ? currentYear : currentYear - 1;
  const latestRecord = Object.values(paidByMonth.value)[0];
  const nominal = latestRecord ? (Number(latestRecord.jumlah) || 30000) : 30000;
  const today = new Date().toISOString().split('T')[0];

  await axios.post('/infak/sync', {
    siswa_id: studentId.value,
    kelas_id: form.kelas_id,
    tahun: akademikStart,
    months: finalMonths,
    nominal,
    tanggal_bayar: today,
    update_existing: true
  });

  draftMonths.value = {};
  await refreshInfak();
};

const extractError = (error) => {
  const data = error.response?.data;
  if (!data) return 'Koneksi gagal atau sesi berakhir.';
  if (data.message && !data.errors) return data.message;
  if (data.errors) return Object.values(data.errors).flat().join(', ');
  return 'Terjadi kesalahan tidak diketahui.';
};

const saveSiswa = async () => {
  isSubmitting.value = true;
  try {
    const payload = {
      nama_lengkap: form.namaLengkap,
      kelas_id: form.kelas_id,
      alamat: form.alamat,
      blok: form.blok,
      nama_wali_1: form.namaWali1,
      wa_wali_1: form.waWali1,
      nama_wali_2: form.namaWali2,
      wa_wali_2: form.waWali2
    };

    if (isEdit.value) {
      await axios.put(`/siswa/${studentId.value}`, payload);
      if (hasDraftChanges.value) {
        await applyDraftInfakChanges();
      }
    } else {
      await axios.post('/siswa', payload);
    }

    isSuccess.value = true;
    notify('Data siswa berhasil disimpan.', 'success');
    setTimeout(() => {
      router.push('/admin/siswa');
    }, 1000);
  } catch (error) {
    console.error('Failed to save siswa:', error);
    notify('Gagal menyimpan data siswa: ' + extractError(error), 'error');
  } finally {
    isSubmitting.value = false;
  }
};
</script>
