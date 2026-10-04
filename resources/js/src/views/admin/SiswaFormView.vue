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
          <h2 class="font-headline-md text-[18px] text-primary-container flex items-center gap-xs mb-sm">
            <span class="material-symbols-outlined text-secondary fill">payments</span>
            Riwayat Infak {{ academicYearLabel }}
          </h2>
          <p class="font-body-md text-on-surface-variant mb-md text-sm">
            Klik salah satu bulan untuk melihat rinciannya: ubah tanggal bayar, ubah nominal,
            hapus pembayaran, atau catat pembayaran bulan yang belum tercatat.
          </p>

          <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2">
            <button
              v-for="cell in monthCells"
              :key="cell.short"
              type="button"
              @click="openMonthModal(cell)"
              :class="[
                'flex items-center justify-center gap-1 p-2 rounded-lg border-2 cursor-pointer transition-all select-none active:scale-95',
                cell.paid ? 'border-primary bg-primary/10 text-primary font-bold' : 'border-outline-variant bg-surface-container-lowest text-on-surface-variant hover:bg-surface-variant'
              ]"
            >
              <span v-if="cell.paid" class="material-symbols-outlined text-[16px] fill">check</span>
              <span class="font-label-md text-sm">{{ cell.short }}</span>
            </button>
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

      <!-- Modal Detail Per Bulan -->
      <div v-if="modalMonth" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50" @click.self="closeMonthModal">
        <div class="w-full sm:max-w-md bg-surface-container-lowest rounded-t-2xl sm:rounded-2xl shadow-xl border border-surface-variant/50 p-5 max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between mb-1">
            <h3 class="font-headline-md text-headline-md text-primary">
              {{ monthFullName(modalMonth.short) }} {{ academicYearLabel }}
            </h3>
            <button type="button" @click="closeMonthModal" aria-label="Tutup" class="p-2 -mr-2 rounded-full hover:bg-surface-container-low text-on-surface-variant transition-colors">
              <span class="material-symbols-outlined font-normal">close</span>
            </button>
          </div>
          <p class="font-label-sm text-label-sm mb-4" :class="modalMonth.paid ? 'text-secondary' : 'text-on-surface-variant'">
            {{ modalMonth.paid ? 'Sudah dibayar — ubah atau hapus pembayaran bulan ini.' : 'Belum tercatat — isi detail untuk mencatat pembayaran.' }}
          </p>

          <div class="flex flex-col gap-3">
            <div class="flex flex-col">
              <label class="font-label-md text-label-md text-on-surface-variant" for="modalTanggal">Tanggal Bayar</label>
              <input
                id="modalTanggal"
                v-model="modalForm.tanggal"
                type="date"
                required
                class="min-h-[48px] px-md py-sm rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all font-body-md text-body-md"
              >
            </div>
            <div class="flex flex-col">
              <label class="font-label-md text-label-md text-on-surface-variant" for="modalNominal">Nominal (Rp)</label>
              <input
                id="modalNominal"
                v-model.number="modalForm.nominal"
                type="number"
                min="0"
                step="1000"
                required
                inputmode="numeric"
                placeholder="Contoh: 30000"
                class="min-h-[48px] px-md py-sm rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all font-body-md text-body-md"
              >
            </div>
          </div>

          <div class="flex flex-col gap-2 mt-5">
            <button
              type="button"
              :disabled="modalBusy"
              @click="saveMonth"
              :class="[
                'w-full h-12 rounded-xl font-label-md text-label-md text-white flex items-center justify-center gap-2 transition-all',
                modalBusy ? 'bg-secondary opacity-80 cursor-not-allowed' : 'bg-primary-container hover:bg-primary-container/90 active:scale-95'
              ]"
            >
              <span v-if="modalBusy" class="material-symbols-outlined animate-spin">sync</span>
              <span v-else class="material-symbols-outlined fill">{{ modalMonth.paid ? 'save' : 'add_circle' }}</span>
              {{ modalMonth.paid ? 'Simpan Perubahan' : 'Catat Pembayaran' }}
            </button>
            <button
              v-if="modalMonth.paid"
              type="button"
              :disabled="modalBusy"
              @click="deleteMonth"
              class="w-full h-12 rounded-xl font-label-md text-label-md text-error border border-error hover:bg-error/10 flex items-center justify-center gap-2 transition-all active:scale-95 disabled:opacity-60"
            >
              <span class="material-symbols-outlined">delete</span>
              Hapus Pembayaran Bulan Ini
            </button>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import { drainOutbox } from '../../utils/waOutbox';

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

const extractError = (error) => {
  const data = error.response?.data;
  if (!data) return 'Koneksi gagal atau sesi berakhir. Silakan login ulang.';
  if (data.message && !data.errors) return data.message;
  if (data.errors) return Object.values(data.errors).flat().join('\n');
  return 'Terjadi kesalahan tidak diketahui.';
};

// short bulan -> record infak terbaru (untuk chip & modal)
const paidByMonth = ref({});
const modalMonth = ref(null);
const modalForm = reactive({ tanggal: '', nominal: '' });
const modalBusy = ref(false);

const monthCells = computed(() =>
  monthsList.map((short) => {
    const record = paidByMonth.value[short] || null;
    return { short, paid: !!record, record };
  })
);

const monthFullName = (short) => monthNameMap[short] || short;

const refreshInfak = async () => {
  try {
    const res = await axios.get(`/siswa/${studentId.value}/infak`);
    const map = {};
    // Endpoint sudah mengurutkan created_at desc: baris pertama = yang terbaru
    (res.data || []).forEach((i) => {
      const short = engToShortMap[i.bulan] || i.bulan;
      if (!map[short]) map[short] = i;
    });
    paidByMonth.value = map;
  } catch (error) {
    console.error('Gagal memuat riwayat infak', error);
  }
};

const openMonthModal = (cell) => {
  modalMonth.value = cell;
  if (cell.paid && cell.record) {
    modalForm.tanggal = String(cell.record.tanggal_bayar || '').slice(0, 10);
    modalForm.nominal = Number(cell.record.jumlah) || 0;
  } else {
    modalForm.tanggal = new Date().toISOString().split('T')[0];
    const latest = Object.values(paidByMonth.value)[0];
    modalForm.nominal = latest ? Number(latest.jumlah) || 30000 : 30000;
  }
};

const closeMonthModal = () => {
  modalMonth.value = null;
  modalBusy.value = false;
};

const saveMonth = async () => {
  if (!modalMonth.value) return;
  if (!modalForm.tanggal) {
    alert('Tanggal bayar wajib diisi.');
    return;
  }
  if (!(Number(modalForm.nominal) > 0)) {
    alert('Nominal wajib diisi.');
    return;
  }

  modalBusy.value = true;
  try {
    if (modalMonth.value.paid) {
      await axios.put(`/infak/${modalMonth.value.record.id}`, {
        tanggal_bayar: modalForm.tanggal,
        jumlah: modalForm.nominal,
        bulan: modalMonth.value.record.bulan,
        tahun: modalMonth.value.record.tahun
      });
    } else {
      await axios.post('/infak/sync', {
        siswa_id: studentId.value,
        kelas_id: form.kelas_id,
        months: [modalMonth.value.short],
        nominal: modalForm.nominal,
        tanggal_bayar: modalForm.tanggal
      });
      // Kirim konfirmasi WA ke wali secara bertahap (dengan delay) di background
      drainOutbox().catch(() => {});
    }
    await refreshInfak();
    closeMonthModal();
  } catch (error) {
    console.error('Gagal menyimpan infak:', error);
    alert('Gagal menyimpan data infak:\n\n' + extractError(error));
  } finally {
    modalBusy.value = false;
  }
};

const deleteMonth = async () => {
  if (!modalMonth.value || !modalMonth.value.record) return;
  const label = monthFullName(modalMonth.value.short);
  if (!confirm(`Hapus pembayaran bulan ${label}? Data infak bulan ini akan dihapus permanen.`)) return;

  modalBusy.value = true;
  try {
    await axios.delete(`/infak/${modalMonth.value.record.id}`);
    await refreshInfak();
    closeMonthModal();
  } catch (error) {
    console.error('Gagal menghapus infak:', error);
    alert('Gagal menghapus pembayaran:\n\n' + extractError(error));
  } finally {
    modalBusy.value = false;
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
</script>
