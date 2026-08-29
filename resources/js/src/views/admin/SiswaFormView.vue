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
            Riwayat Infak {{ currentYear }}
          </h2>
          <p class="font-body-md text-on-surface-variant mb-md text-sm">
            Centang bulan yang sudah dibayar. Hilangkan centang untuk membatalkan/menghapus infak bulan tersebut.
          </p>
          
          <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2 mb-md">
            <label 
              v-for="month in monthsList" 
              :key="month"
              :class="[
                'flex items-center justify-center p-2 rounded-lg border-2 cursor-pointer transition-all select-none',
                selectedMonths.includes(month) ? 'border-primary bg-primary/10 text-primary font-bold' : 'border-outline-variant bg-surface-container-lowest text-on-surface-variant hover:bg-surface-variant'
              ]"
            >
              <input type="checkbox" :value="month" v-model="selectedMonths" class="hidden">
              <span class="font-label-md text-sm">{{ month }}</span>
            </label>
          </div>

          <div class="flex flex-col sm:flex-row gap-sm border-t border-outline-variant pt-sm">
            <div class="flex-1 flex flex-col">
              <label class="font-label-md text-label-md text-on-surface-variant" for="syncNominal">Nominal (Untuk bulan baru)</label>
              <input v-model="syncForm.nominal" required class="min-h-[48px] px-md py-sm rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all font-body-md" id="syncNominal" type="number">
            </div>
            <div class="flex-1 flex flex-col">
              <label class="font-label-md text-label-md text-on-surface-variant" for="syncTanggal">Tgl Bayar (Untuk bulan baru)</label>
              <input v-model="syncForm.tanggal_bayar" required class="min-h-[48px] px-md py-sm rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all font-body-md" id="syncTanggal" type="date">
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
      // Fetch Infak for this student
      const infakRes = await axios.get(`/siswa/${studentId.value}/infak?tahun=${currentYear}`);
      const existingInfak = infakRes.data;
      
      if (existingInfak.length > 0) {
        // Reverse map English months to Indonesian
        const engToIdMap = {
          'January': 'Jan', 'February': 'Feb', 'March': 'Mar', 'April': 'Apr',
          'May': 'Mei', 'June': 'Jun', 'July': 'Jul', 'August': 'Agu',
          'September': 'Sep', 'October': 'Okt', 'November': 'Nov', 'December': 'Des'
        };
        
        selectedMonths.value = existingInfak.map(i => engToIdMap[i.bulan] || i.bulan);
        
        // Auto-fill form from latest record
        syncForm.nominal = existingInfak[0].jumlah;
        syncForm.tanggal_bayar = existingInfak[0].tanggal_bayar;
      }
    }
  } catch (err) {
    console.error("Gagal mengambil data", err);
  }
});

const monthsList = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
const currentYear = new Date().getFullYear();
const selectedMonths = ref([]);

const syncForm = reactive({
  nominal: '',
  tanggal_bayar: new Date().toISOString().split('T')[0]
});

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
      
      // Sync Infak
      await axios.post('/infak/sync', {
        siswa_id: studentId.value,
        kelas_id: form.kelas_id,
        tahun: currentYear,
        months: selectedMonths.value,
        nominal: syncForm.nominal || 0,
        tanggal_bayar: syncForm.tanggal_bayar
      });
    } else {
      await axios.post('/siswa', payload);
    }
    
    isSuccess.value = true;
    setTimeout(() => {
      router.push('/admin/siswa');
    }, 1500);
  } catch (error) {
    console.error("Failed to add siswa:", error);
    alert("Gagal menambah data siswa. Pastikan form terisi dengan benar.");
  } finally {
    isSubmitting.value = false;
  }
};
</script>
