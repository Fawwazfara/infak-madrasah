<template>
  <div class="flex-1 flex flex-col h-full bg-background relative">
    
    <!-- Sticky Header (Custom for Form) -->
    <header class="sticky top-0 z-10 bg-surface shadow-sm border-b border-surface-variant flex-none">
      <div class="flex items-center justify-between px-container-margin h-16 w-full max-w-3xl mx-auto">
        <div class="flex items-center gap-sm">
          <router-link to="/admin/siswa" aria-label="Tutup form" class="p-2 -ml-2 rounded-full hover:bg-surface-container-low text-on-surface-variant transition-colors active:scale-95">
            <span class="material-symbols-outlined font-normal">close</span>
          </router-link>
          <h1 class="font-headline-md text-headline-md text-primary truncate">Data Siswa</h1>
        </div>
      </div>
    </header>

    <!-- Scrollable Content Area -->
    <main class="flex-1 overflow-y-auto w-full max-w-3xl mx-auto px-container-margin py-lg scroll-smooth bg-background">
      <form class="flex flex-col gap-md pb-md" @submit.prevent="saveSiswa">
        
        <!-- Section 1: Data Diri -->
        <section class="bg-surface-container-lowest rounded-2xl shadow-sm p-md sm:p-lg border border-surface-variant/50">
          <h2 class="font-headline-md text-[18px] text-primary-container flex items-center gap-xs mb-sm">
            <span class="material-symbols-outlined text-secondary fill">person</span>
            Data Diri
          </h2>
          <div class="flex flex-col gap-sm">
            <div class="flex flex-col">
              <label class="font-label-md text-label-md text-on-surface-variant" for="namaLengkap">Nama Lengkap <span class="text-error">*</span></label>
              <input v-model="form.namaLengkap" required class="min-h-[48px] px-md py-sm rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all placeholder:text-outline/50 font-body-md text-body-md" id="namaLengkap" placeholder="Masukkan nama lengkap siswa" type="text">
            </div>
            
            <div class="flex flex-col">
              <label class="font-label-md text-label-md text-on-surface-variant" for="alamat">Alamat</label>
              <textarea v-model="form.alamat" class="min-h-[48px] px-md py-sm rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all placeholder:text-outline/50 font-body-md text-body-md resize-y" id="alamat" placeholder="Masukkan alamat lengkap" rows="3"></textarea>
            </div>
            
            <div class="flex flex-col">
              <label class="font-label-md text-label-md text-on-surface-variant" for="blok">Blok / Asrama</label>
              <input v-model="form.blok" class="min-h-[48px] px-md py-sm rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all placeholder:text-outline/50 font-body-md text-body-md" id="blok" placeholder="Contoh: Blok A" type="text">
            </div>
            
            <div class="flex flex-col">
              <label class="font-label-md text-label-md text-on-surface-variant" for="pilihKelas">Pilih Kelas <span class="text-error">*</span></label>
              <select v-model="form.kelas_id" required class="min-h-[48px] px-md py-sm rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all font-body-md text-body-md" id="pilihKelas">
                <option value="" disabled>Pilih kelas siswa</option>
                <option v-for="k in kelasi" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
              </select>
            </div>
          </div>
        </section>

        <!-- Section 1.5: Manajemen Infak (Hanya Edit) -->
        <section v-if="isEdit" class="bg-surface-container-lowest rounded-2xl shadow-sm p-md sm:p-lg border border-surface-variant/50">
          <div class="flex items-center justify-between mb-sm">
            <h2 class="font-headline-md text-[18px] text-primary-container flex items-center gap-xs">
              <span class="material-symbols-outlined text-secondary fill">payments</span>
              Riwayat Infak {{ academicYearLabel }}
            </h2>
            <span class="flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium" :class="hasDraftChanges ? 'bg-warning/20 text-warning' : 'bg-secondary/20 text-on-surface-variant'">
              <span class="material-symbols-outlined text-[12px]">{{ hasDraftChanges ? 'edit' : 'check_circle' }}</span>
              {{ hasDraftChanges ? 'Ada perubahan belum disimpan' : 'Tersinkronkan' }}
            </span>
          </div>
          <p class="font-body-md text-on-surface-variant mb-md text-sm">
            Klik bulan untuk mengubah status. Perubahan <strong class="text-primary">hanya disimpan ke server</strong> saat tombol "Simpan Data" ditekan.
          </p>

          <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2">
            <button
              v-for="cell in monthCells"
              :key="cell.short"
              type="button"
              @click="toggleMonthDraft(cell)"
              :class="[
                'flex items-center justify-center gap-1 p-2 rounded-lg border-2 cursor-pointer transition-all select-none active:scale-95',
                cell.draftPaid !== null
                  ? 'border-warning bg-warning/10 text-warning font-bold ring-2 ring-warning/30'
                  : cell.paid
                    ? 'border-primary bg-primary/10 text-primary font-bold'
                    : 'border-outline-variant bg-surface-container-lowest text-on-surface-variant hover:bg-surface-variant'
              ]"
              :title="cell.draftPaid !== null ? (cell.draftPaid ? 'Akan dicatat (draft)' : 'Akan dihapus (draft)') : (cell.paid ? 'Sudah dibayar - klik untuk hapus' : 'Belum dibayar - klik untuk catat')"
            >
              <span v-if="cell.draftPaid !== null" class="material-symbols-outlined text-[16px] fill">{{ cell.draftPaid ? 'pending' : 'delete_forever' }}</span>
              <span v-else-if="cell.paid" class="material-symbols-outlined text-[16px] fill">check</span>
              <span class="font-label-md text-sm">{{ cell.short }}</span>
            </button>
          </div>

          <!-- Draft Summary -->
          <div v-if="hasDraftChanges" class="mt-4 p-3 bg-warning/10 border border-warning/30 rounded-lg">
            <div class="flex items-center gap-2 text-warning mb-2">
              <span class="material-symbols-outlined">info</span>
              <span class="font-label-md">Perubahan menunggu disimpan:</span>
            </div>
            <div class="flex flex-wrap gap-1">
              <span 
                v-for="m in draftChangesSummary" 
                :key="m" 
                class="px-2 py-1 text-xs rounded-full font-medium"
                :class="m.startsWith('+') ? 'bg-primary/20 text-primary' : 'bg-error/20 text-error'"
              >
                {{ m }}
              </span>
            </div>
          </div>

        </section>


        <!-- Section 2: Data Wali Utama -->
        <section class="bg-surface-container-lowest rounded-2xl shadow-sm p-md sm:p-lg border border-surface-variant/50">
          <h2 class="font-headline-md text-[18px] text-primary-container flex items-center gap-xs mb-sm">
            <span class="material-symbols-outlined text-secondary fill">family_restroom</span>
            Data Wali Utama
          </h2>
          <div class="flex flex-col gap-sm">
            <div class="flex flex-col">
              <label class="font-label-md text-label-md text-on-surface-variant" for="namaWali1">Nama Wali 1 <span class="text-error">*</span></label>
              <input v-model="form.namaWali1" required class="min-h-[48px] px-md py-sm rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all placeholder:text-outline/50 font-body-md text-body-md" id="namaWali1" placeholder="Masukkan nama wali utama" type="text">
            </div>
            
            <div class="flex flex-col">
              <label class="font-label-md text-label-md text-on-surface-variant" for="waWali1">No. WhatsApp <span class="text-error">*</span></label>
              <div class="relative flex items-center focus-within:z-10 focus-within:border-primary focus-within:ring-4 focus-within:ring-primary/20 transition-all rounded-lg border border-outline-variant bg-surface-container-lowest overflow-hidden">
                <span class="px-3 border-r border-outline-variant text-on-surface-variant bg-surface-container-low h-full flex items-center min-h-[48px] font-label-md text-label-md">+62</span>
                <input v-model="form.waWali1" required class="min-h-[48px] w-full px-md py-sm border-none bg-transparent text-on-surface focus:ring-0 focus:outline-none placeholder:text-outline/50 font-body-md text-body-md" id="waWali1" pattern="[0-9\s\-]*" placeholder="812 3456 7890" type="tel">
              </div>
              <p class="font-label-sm text-label-sm text-on-surface-variant mt-1">Gunakan nomor yang aktif di WhatsApp.</p>
            </div>
          </div>
        </section>

        <!-- Section 3: Data Wali Opsional -->
        <section class="bg-surface-container-low rounded-2xl shadow-sm p-md sm:p-lg border border-surface-variant border-dashed opacity-90">
          <h2 class="font-headline-md text-[18px] text-secondary flex items-center gap-xs mb-sm">
            <span class="material-symbols-outlined text-outline">group_add</span>
            Data Wali Opsional
          </h2>
          <div class="flex flex-col gap-sm">
            <div class="flex flex-col">
              <label class="font-label-md text-label-md text-on-surface-variant" for="namaWali2">Nama Wali 2</label>
              <input v-model="form.namaWali2" class="min-h-[48px] px-md py-sm rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all placeholder:text-outline/50 font-body-md text-body-md" id="namaWali2" placeholder="Nama wali alternatif" type="text">
            </div>
            
            <div class="flex flex-col">
              <label class="font-label-md text-label-md text-on-surface-variant" for="waWali2">No. WhatsApp Alternatif</label>
              <div class="relative flex items-center focus-within:z-10 focus-within:border-primary focus-within:ring-4 focus-within:ring-primary/20 transition-all rounded-lg border border-outline-variant bg-surface-container-lowest overflow-hidden">
                <span class="px-3 border-r border-outline-variant text-on-surface-variant bg-surface-container-low h-full flex items-center min-h-[48px] font-label-md text-label-md">+62</span>
                <input v-model="form.waWali2" class="min-h-[48px] w-full px-md py-sm border-none bg-transparent text-on-surface focus:ring-0 focus:outline-none placeholder:text-outline/50 font-body-md text-body-md" id="waWali2" pattern="[0-9\s\-]*" placeholder="812 3456 7890" type="tel">
              </div>
            </div>
          </div>
        </section>

        <!-- Submit Button -->
        <div class="mt-4 pb-12">
          <button 
            type="submit" 
            :disabled="isSubmitting"
            :class="[
              'w-full h-[56px] text-white font-label-md text-label-md rounded-xl transition-all shadow-md flex items-center justify-center gap-2',
              isSubmitting ? 'bg-secondary opacity-80 cursor-not-allowed' : 'bg-primary-container hover:bg-primary-container/90 active:scale-95'
            ]"
          >
            <span v-if="!isSubmitting && !isSuccess" class="material-symbols-outlined fill">save</span>
            <span v-if="isSubmitting" class="material-symbols-outlined animate-spin">sync</span>
            <span v-if="isSuccess" class="material-symbols-outlined fill">check_circle</span>
            {{ isSuccess ? 'Tersimpan' : (isSubmitting ? 'Menyimpan...' : 'Simpan Data') }}
          </button>
        </div>
      </form>
    </main>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const route = useRoute();

const isSubmitting = ref(false);
const isSuccess = ref(false);
const kelasi = ref([]);

const studentId = computed(() => route.params.id);
const isEdit = computed(() => !!studentId.value);

onMounted(async () => {
  try {
    const res = await axios.get('/kelas');
    kelasi.value = res.data;
    
    // If Edit mode, fetch student data
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
      // Muat riwayat infak (chip bulan terbayar)
      await refreshInfak();
    }
  } catch (err) {
    console.error("Gagal mengambil data", err);
    alert('Gagal memuat data siswa. Silakan coba lagi.');
  }
});

// Urut sesuai tahun ajaran: Jul s/d Jun
const monthsList = ['Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];
const academicYearLabel = (() => {
  const now = new Date();
  const start = now.getMonth() >= 6 ? now.getFullYear() : now.getFullYear() - 1;
  return `${start}/${start + 1}`;
})();

// Singkat -> nama bulan penuh (sesuai yang tersimpan di tabel infaks)
const monthNameMap = {
  Jul: 'July', Agu: 'August', Sep: 'September', Okt: 'October',
  Nov: 'November', Des: 'December', Jan: 'January', Feb: 'February',
  Mar: 'March', Apr: 'April', Mei: 'May', Jun: 'June'
};

// Nama bulan penuh (Inggris) -> singkat
const engToShortMap = Object.fromEntries(
  Object.entries(monthNameMap).map(([short, full]) => [full, short])
);

// short bulan -> record infak terbaru (untuk chip & modal)
const paidByMonth = ref({});

// Draft state: null = no change, true = will add, false = will delete
const draftMonths = ref({});

const monthCells = computed(() =>
  monthsList.map((short) => {
    const record = paidByMonth.value[short] || null;
    const draft = draftMonths.value[short];
    return { 
      short, 
      paid: !!record, 
      record,
      draftPaid: draft === null ? null : draft  // null=no change, true=add, false=delete
    };
  })
);

const hasDraftChanges = computed(() => 
  Object.values(draftMonths.value).some(v => v !== null)
);

const draftChangesSummary = computed(() => {
  const summary = [];
  Object.entries(draftMonths.value).forEach(([short, val]) => {
    if (val === true) summary.push(`+ ${short}`);
    if (val === false) summary.push(`- ${short}`);
  });
  return summary;
});

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
    // Reset draft on fresh load
    draftMonths.value = {};
  } catch (error) {
    console.error('Gagal memuat riwayat infak', error);
  }
};

const toggleMonthDraft = (cell) => {
  if (cell.draftPaid !== null) {
    // Already has draft - toggle back to no change
    draftMonths.value[cell.short] = null;
  } else if (cell.paid) {
    // Currently paid - draft to delete
    draftMonths.value[cell.short] = false;
  } else {
    // Currently unpaid - draft to add
    draftMonths.value[cell.short] = true;
  }
};

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

const saveSiswa = async () => {
  isSubmitting.value = true;
  
  try {
    // First save student basic data
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
    } else {
      await axios.post('/siswa', payload);
    }

    // Then apply draft infak changes if any
    if (isEdit.value && hasDraftChanges.value) {
      await applyDraftInfakChanges();
    }
    
    isSuccess.value = true;
    setTimeout(() => {
      router.push('/admin/siswa');
    }, 1500);
  } catch (error) {
    console.error("Failed to save siswa:", error);
    alert("Gagal menyimpan data siswa:\n\n" + extractError(error));
  } finally {
    isSubmitting.value = false;
  }
};

const applyDraftInfakChanges = async () => {
  const changes = Object.entries(draftMonths.value).filter(([, v]) => v !== null);
  if (changes.length === 0) return;

  try {
    // Group changes by action type
    const toAdd = changes.filter(([, v]) => v === true).map(([short]) => short);
    const toDelete = changes.filter(([, v]) => v === false).map(([short]) => short);

    // Use syncBySiswa endpoint for bulk sync
    const currentYear = new Date().getFullYear();
    const akademikStart = new Date().getMonth() >= 6 ? currentYear : currentYear - 1;

    if (toAdd.length > 0 || toDelete.length > 0) {
      // Get latest nominal for new entries
      let nominal = 30000;
      const latestRecord = Object.values(paidByMonth.value)[0];
      if (latestRecord) nominal = Number(latestRecord.jumlah) || 30000;

      const today = new Date().toISOString().split('T')[0];

      await axios.post('/infak/sync', {
        siswa_id: studentId.value,
        kelas_id: form.kelas_id,
        tahun: akademikStart,
        months: [...toAdd, ...toDelete], // sync endpoint handles diff
        nominal: nominal,
        tanggal_bayar: today,
        update_existing: false
      });

      // For deletions, we need to call delete individually or handle differently
      // Actually, syncBySiswa handles this - it computes diff from requested months
      // So we need to pass the FINAL desired state (all months that should exist)
      
      // Better approach: get current paid months, apply draft, send final list
      const currentPaidMonths = Object.keys(paidByMonth.value);
      const finalMonths = currentPaidMonths
        .filter(m => draftMonths.value[m] !== false) // keep unless drafted for deletion
        .concat(toAdd); // add drafted additions
      
      // Remove duplicates
      const uniqueFinalMonths = [...new Set(finalMonths)];

      await axios.post('/infak/sync', {
        siswa_id: studentId.value,
        kelas_id: form.kelas_id,
        tahun: akademikStart,
        months: uniqueFinalMonths,
        nominal: nominal,
        tanggal_bayar: today,
        update_existing: true
      });
    }

    // Clear draft after successful save
    draftMonths.value = {};
    await refreshInfak();
  } catch (error) {
    console.error('Gagal menerapkan perubahan infak:', error);
    throw error;
  }
};

const extractError = (error) => {
  const data = error.response?.data;
  if (!data) return 'Koneksi gagal atau sesi berakhir. Silakan login ulang.';
  if (data.message && !data.errors) return data.message;
  if (data.errors) return Object.values(data.errors).flat().join('\n');
  return 'Terjadi kesalahan tidak diketahui.';
};
</script>