<template>
  <div class="flex-1 flex flex-col h-full bg-background relative">
    
    <!-- Desktop Layout: Two-column on lg+, single column on mobile -->
    <main class="w-full h-full flex flex-col relative lg:max-w-[1200px] lg:mx-auto lg:shadow-2xl">
      
      <!-- TopAppBar -->
      <header class="sticky top-0 left-0 w-full z-30 flex items-center px-container-margin h-16 bg-surface shadow-sm border-b border-surface-variant flex-none lg:px-lg">
        <button @click="$router.back()" aria-label="Go back" class="p-2 -ml-2 rounded-full hover:bg-primary-container/20 text-primary transition-colors active:scale-95 lg:-ml-0">
          <span class="material-symbols-outlined font-normal">arrow_back</span>
        </button>
        <h1 class="ml-4 font-headline-md text-headline-md text-primary font-bold tracking-tight">Catat Infak</h1>
      </header>

      <!-- Scrollable Body -->
      <div class="flex-1 overflow-y-auto px-container-margin py-lg space-y-lg lg:px-lg lg:py-xl lg:grid lg:grid-cols-[1fr_380px] lg:gap-xl lg:items-start">
        
        <!-- LEFT COLUMN: Form Sections -->
        <div class="flex flex-col gap-lg lg:pr-4 lg:sticky lg:top-20">
          
          <form @submit.prevent="saveTransaction" class="flex flex-col gap-lg pb-24 lg:pb-0">
            
            <!-- Toggle Mode Input -->
            <div class="bg-surface-container rounded-full p-1 border border-outline-variant/50 flex">
              <button 
                type="button"
                @click="setInputMode('satuan')"
                :class="[
                  'flex-1 h-12 rounded-full font-label-md transition-colors',
                  inputMode === 'satuan' ? 'bg-primary text-white shadow-sm font-bold' : 'text-on-surface-variant hover:bg-surface-container-high'
                ]"
              >
                Input Satuan
              </button>
              <button 
                type="button"
                @click="setInputMode('massal')"
                :class="[
                  'flex-1 h-12 rounded-full font-label-md transition-colors',
                  inputMode === 'massal' ? 'bg-primary text-white shadow-sm font-bold' : 'text-on-surface-variant hover:bg-surface-container-high'
                ]"
              >
                Input Massal
              </button>
            </div>

            <!-- ============================== ALUR SATUAN ============================== -->
            <template v-if="inputMode === 'satuan'">
              
              <section class="bg-surface-container-lowest rounded-2xl p-md shadow-sm border border-surface-variant/50 flex flex-col gap-md">
                <div class="flex flex-col gap-xs">
                  <label class="font-label-md text-label-md text-on-surface" for="kelasSatuan">Pilih Kelas</label>
                  <div class="relative">
                    <select v-model="form.kelas_id" required class="w-full h-12 rounded-xl border-outline-variant text-on-surface bg-surface-bright focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 appearance-none pl-4 pr-10 font-body-md text-body-md transition-all" id="kelasSatuan">
                      <option value="" disabled>Pilih Kelas</option>
                      <option v-for="k in kelasi" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-outline pointer-events-none">expand_more</span>
                  </div>
                </div>
                
                <div class="flex flex-col gap-xs">
                  <label class="font-label-md text-label-md text-on-surface" for="siswaSatuan">Pilih Siswa</label>
                  <div class="relative">
                    <select v-model="form.siswa_id" required class="w-full h-12 rounded-xl border-outline-variant text-on-surface bg-surface-bright focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 appearance-none pl-4 pr-10 font-body-md text-body-md transition-all" id="siswaSatuan" :disabled="!form.kelas_id || isFetchingStudents">
                      <option value="" disabled>{{ isFetchingStudents ? 'Memuat siswa...' : 'Pilih Siswa' }}</option>
                      <option v-for="s in students" :key="s.id" :value="s.id">{{ s.fullName }}</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-outline pointer-events-none">expand_more</span>
                  </div>
                </div>
              </section>

              <section class="bg-surface-container-lowest rounded-2xl p-md shadow-sm border border-surface-variant/50 flex flex-col gap-md" v-if="form.siswa_id">
                <h2 class="font-label-md text-label-md text-on-surface">Bulan Belum Dibayar</h2>
                <div class="grid grid-cols-3 lg:grid-cols-6 gap-sm">
                  <button 
                    v-for="month in unpaidMonthsForSatuan" 
                    :key="month"
                    type="button"
                    @click="toggleMonth(month)"
                    :class="[
                      'h-12 lg:h-14 rounded-lg font-label-md text-label-md active:scale-95 transition-transform flex items-center justify-center',
                      form.selectedMonths.includes(month) 
                        ? 'bg-primary-container text-white shadow-sm gap-1' 
                        : 'bg-surface-bright text-on-surface-variant border border-outline-variant/30 hover:bg-surface-variant'
                    ]"
                  >
                    <span v-if="form.selectedMonths.includes(month)" class="material-symbols-outlined text-[16px] fill">check</span>
                    {{ month }}
                  </button>
                </div>
                <div v-if="unpaidMonthsForSatuan.length === 0" class="text-center p-3 text-secondary font-label-md">
                  🎉 Semua bulan sudah lunas!
                </div>
              </section>

            </template>

            <!-- ============================== ALUR MASSAL ============================== -->
            <template v-else>
              
              <section class="bg-surface-container-lowest rounded-2xl p-md shadow-sm border border-surface-variant/50 flex flex-col gap-md">
                <h2 class="font-label-md text-label-md text-on-surface">Pilih Bulan (bisa multiple)</h2>
                <div class="grid grid-cols-3 lg:grid-cols-6 gap-sm">
                  <button 
                    v-for="month in monthsList" 
                    :key="month"
                    type="button"
                    @click="toggleMonthMassal(month)"
                    :class="[
                      'h-12 lg:h-14 rounded-lg font-label-md text-label-md active:scale-95 transition-transform flex items-center justify-center',
                      form.selectedMonths.includes(month) 
                        ? 'bg-primary-container text-white shadow-sm gap-1' 
                        : 'bg-surface-bright text-on-surface-variant border border-outline-variant/30 hover:bg-surface-variant'
                    ]"
                  >
                    <span v-if="form.selectedMonths.includes(month)" class="material-symbols-outlined text-[16px] fill">check</span>
                    {{ month }}
                  </button>
                </div>
              </section>

              <section class="bg-surface-container-lowest rounded-2xl p-md shadow-sm border border-surface-variant/50 flex flex-col gap-md" v-if="form.selectedMonths.length > 0">
                <div class="flex flex-col gap-xs">
                  <label class="font-label-md text-label-md text-on-surface" for="kelasMassal">Pilih Kelas</label>
                  <div class="relative">
                    <select v-model="form.kelas_id" required class="w-full h-12 rounded-xl border-outline-variant text-on-surface bg-surface-bright focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 appearance-none pl-4 pr-10 font-body-md text-body-md transition-all" id="kelasMassal">
                      <option value="" disabled>Pilih Kelas</option>
                      <option v-for="k in kelasi" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-outline pointer-events-none">expand_more</span>
                  </div>
                </div>
                
                <div class="flex flex-col gap-3" v-if="form.kelas_id">
                  <div class="bg-surface-bright border border-outline-variant/50 rounded-xl overflow-hidden flex flex-col">
                    <div class="flex justify-between items-center p-3 border-b border-outline-variant/30 bg-surface-container-lowest">
                      <span class="font-label-sm text-on-surface-variant">Siswa Belum Lunas ({{ form.selectedMonths.join(', ') }})</span>
                      <div class="flex gap-3">
                        <button type="button" @click="selectAllSiswa" class="font-label-sm text-primary hover:underline" v-if="students.length > 0">Pilih Semua</button>
                        <button type="button" @click="deselectAllSiswa" class="font-label-sm text-error hover:underline" v-if="students.length > 0">Batal Semua</button>
                      </div>
                    </div>
                    
                    <div class="p-2 border-b border-outline-variant/30 bg-surface-container-lowest" v-if="students.length > 0">
                      <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline-variant text-[20px] pointer-events-none">search</span>
                        <input v-model="searchQuery" type="text" placeholder="Cari nama siswa..." class="w-full h-10 pl-10 pr-4 rounded-lg border border-outline-variant bg-surface-bright focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 font-body-sm text-body-sm">
                      </div>
                    </div>
                    
                    <div class="max-h-[400px] lg:max-h-[500px] overflow-y-auto flex flex-col p-2 gap-1 relative min-h-[150px]">
                      <div v-if="isFetchingStudents" class="absolute inset-0 flex items-center justify-center bg-white/50 z-10">
                        <span class="material-symbols-outlined animate-spin text-primary text-3xl">sync</span>
                      </div>

                      <label v-for="s in filteredStudents" :key="s.id" class="flex items-center gap-3 p-3 rounded-lg hover:bg-surface-container-lowest cursor-pointer transition-colors" :class="{'bg-primary/5': form.siswa_ids.includes(s.id)}">
                        <div class="relative flex items-center justify-center w-6 h-6 shrink-0">
                          <input type="checkbox" :value="s.id" v-model="form.siswa_ids" class="peer appearance-none w-6 h-6 border-2 border-outline rounded-md checked:bg-primary checked:border-primary transition-all">
                          <span class="material-symbols-outlined absolute text-white text-[18px] pointer-events-none opacity-0 peer-checked:opacity-100 transition-opacity">check</span>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center font-label-md shrink-0 uppercase">
                          {{ getInitials(s.fullName) }}
                        </div>
                        <div class="flex flex-col min-w-0">
                          <span class="font-label-md text-on-surface truncate">{{ s.fullName }}</span>
                          <span class="font-body-sm text-on-surface-variant truncate">NISN: {{ s.nis }}</span>
                        </div>
                      </label>
                      <div v-if="!isFetchingStudents && students.length === 0" class="p-4 text-center text-secondary font-body-sm flex flex-col items-center gap-2">
                        <span class="material-symbols-outlined text-4xl">check_circle</span>
                        Semua siswa di kelas ini sudah lunas bulan {{ form.selectedMonths.join(', ') }}!
                      </div>
                    </div>
                  </div>
                </div>
              </section>

            </template>
            <!-- ========================================================================= -->

            <!-- Section: Payment Details -->
            <section class="bg-surface-container-lowest rounded-2xl p-md shadow-sm border border-surface-variant/50 flex flex-col gap-md lg:grid lg:grid-cols-2 lg:gap-md lg:items-start">
              <div class="flex flex-col gap-xs">
                <label class="font-label-md text-label-md text-on-surface" for="tanggal">Tanggal Bayar</label>
                <div class="relative">
                  <input
                    v-model="form.tanggal"
                    required
                    type="date"
                    class="w-full h-12 rounded-xl border-outline-variant text-on-surface bg-surface-bright focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 pl-4 pr-4 font-body-md text-body-md transition-all block"
                    id="tanggal"
                  >
                </div>
              </div>
              
              <div class="flex flex-col gap-xs">
                <label class="font-label-md text-label-md text-on-surface" for="nominal">Nominal Per Bulan</label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <span class="text-on-surface-variant font-body-md text-body-md">Rp</span>
                  </div>
                  <input v-model="form.nominal" required list="nominal-options" class="w-full h-12 rounded-xl border-outline-variant text-on-surface bg-surface-bright focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/20 pl-12 pr-4 font-body-md text-body-md transition-all block" id="nominal" type="number" step="1000">
                  <datalist id="nominal-options">
                    <option value="30000"></option>
                    <option value="40000"></option>
                    <option value="50000"></option>
                  </datalist>
                </div>
              </div>
            </section>

            <!-- Section: Summary -->
            <section class="bg-surface-container-lowest rounded-2xl p-md shadow-sm border-l-4 border-primary flex flex-col gap-sm">
              <h3 class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Ringkasan Transaksi</h3>
              
              <div class="bg-primary/10 rounded-xl p-3 flex gap-3 mb-2" v-if="inputMode === 'massal'">
                <span class="material-symbols-outlined text-primary shrink-0 mt-0.5">info</span>
                <p class="font-body-sm text-on-surface">
                  Akan mencatat pembayaran untuk <strong class="text-primary">{{ form.siswa_ids.length }} siswa</strong> 
                  (bulan <strong class="text-primary">{{ form.selectedMonths.join(', ') }}</strong>) = 
                  <strong class="text-primary">{{ totalTransaksi }} transaksi</strong>, 
                  total <strong class="text-primary">Rp {{ formatRupiah(totalBayar) }}</strong>
                </p>
              </div>

              <div class="bg-primary/10 rounded-xl p-3 flex gap-3 mb-2" v-else-if="inputMode === 'satuan' && form.selectedMonths.length > 0">
                <span class="material-symbols-outlined text-primary shrink-0 mt-0.5">info</span>
                <p class="font-body-sm text-on-surface">
                  Akan mencatat pembayaran untuk <strong class="text-primary">1 siswa</strong> 
                  (bulan <strong class="text-primary">{{ form.selectedMonths.join(', ') }}</strong>) = 
                  <strong class="text-primary">{{ totalTransaksi }} transaksi</strong>, 
                  total <strong class="text-primary">Rp {{ formatRupiah(totalBayar) }}</strong>
                </p>
              </div>

              <div class="flex justify-between items-center py-2 border-b border-surface-container-highest">
                <span class="font-body-md text-body-md text-on-surface">Total Transaksi</span>
                <span class="font-headline-md text-headline-md text-primary">{{ totalTransaksi }}</span>
              </div>
              <div class="flex justify-between items-center py-2">
                <span class="font-body-md text-body-md text-on-surface">Total Bayar</span>
                <span class="font-headline-md text-headline-md text-primary">Rp {{ formatRupiah(totalBayar) }}</span>
              </div>
            </section>
          </form>
        </div>

        <!-- RIGHT COLUMN: Sticky Action & Quick Info -->
        <aside class="hidden lg:block lg:sticky lg:top-20 lg:self-start space-y-4">
          <!-- Save Button Card -->
          <div class="bg-surface-container-lowest rounded-2xl p-md shadow-sm border border-surface-variant/50">
            <button 
              @click="saveTransaction"
              :disabled="isSubmitDisabled"
              :class="[
                'w-full h-[56px] text-white font-label-md text-label-md rounded-xl transition-transform shadow-md flex items-center justify-center gap-2',
                (isSubmitDisabled) ? 'bg-secondary opacity-80 cursor-not-allowed' : 'bg-primary-container active:scale-95'
              ]"
            >
              <span v-if="!isSubmitting && !isSuccess" class="material-symbols-outlined fill">save</span>
              <span v-if="isSubmitting" class="material-symbols-outlined animate-spin">sync</span>
              <span v-if="isSuccess" class="material-symbols-outlined fill">check_circle</span>
              {{ isSuccess ? 'Tersimpan' : (isSubmitting ? 'Menyimpan...' : 'Simpan Transaksi') }}
            </button>
            
            <div v-if="isSubmitDisabled" class="mt-3 text-xs text-on-surface-variant text-center space-y-1">
              <template v-if="form.selectedMonths.length === 0">⚠ Pilih minimal 1 bulan</template>
              <template v-else-if="inputMode === 'satuan' && !form.siswa_id">⚠ Pilih siswa terlebih dahulu</template>
              <template v-else-if="inputMode === 'massal' && form.siswa_ids.length === 0">⚠ Pilih minimal 1 siswa</template>
              <template v-else-if="!form.kelas_id">⚠ Pilih kelas terlebih dahulu</template>
              <template v-else-if="!form.tanggal">⚠ Isi tanggal bayar</template>
            </div>
          </div>

          <!-- Quick Stats Card -->
          <div class="bg-surface-container-lowest rounded-2xl p-md shadow-sm border border-surface-variant/50">
            <h3 class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider mb-3">Ringkasan Cepat</h3>
            <div class="space-y-2 text-sm">
              <div class="flex justify-between">
                <span class="text-on-surface-variant">Mode</span>
                <span class="font-medium text-primary capitalize">{{ inputMode }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-on-surface-variant">Bulan dipilih</span>
                <span class="font-medium">{{ form.selectedMonths.length }}</span>
              </div>
              <div class="flex justify-between" v-if="inputMode === 'massal'">
                <span class="text-on-surface-variant">Siswa dipilih</span>
                <span class="font-medium">{{ form.siswa_ids.length }}</span>
              </div>
              <div class="flex justify-between" v-else>
                <span class="text-on-surface-variant">Siswa</span>
                <span class="font-medium">{{ form.siswa_id ? 'Dipilih' : 'Belum dipilih' }}</span>
              </div>
              <div class="flex justify-between pt-2 border-t border-outline-variant/30">
                <span class="text-on-surface-variant font-medium">Total</span>
                <span class="font-headline-sm text-primary">Rp {{ formatRupiah(totalBayar) }}</span>
              </div>
            </div>
          </div>

          <!-- Shortcut Tips -->
          <div class="bg-surface-container-lowest rounded-2xl p-md shadow-sm border border-surface-variant/50">
            <h3 class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider mb-3 flex items-center gap-2">
              <span class="material-symbols-outlined text-secondary">lightbulb</span>
              Tips
            </h3>
            <ul class="space-y-2 text-sm text-on-surface-variant">
              <li class="flex items-start gap-2"><span class="material-symbols-outlined text-secondary text-[16px] shrink-0 mt-0.5">check</span> Gunakan <strong>Input Massal</strong> untuk banyak siswa sekaligus</li>
              <li class="flex items-start gap-2"><span class="material-symbols-outlined text-secondary text-[16px] shrink-0 mt-0.5">check</span> Pilih bulan dulu, baru pilih kelas & siswa</li>
              <li class="flex items-start gap-2"><span class="material-symbols-outlined text-secondary text-[16px] shrink-0 mt-0.5">check</span> Cari nama siswa di kolom pencarian (massal)</li>
              <li class="flex items-start gap-2"><span class="material-symbols-outlined text-secondary text-[16px] shrink-0 mt-0.5">check</span> WA terkirim otomatis ke wali setelah simpan</li>
            </ul>
          </div>
        </aside>
        
      </div>

      <!-- Mobile Sticky Footer (hidden on lg+) -->
      <div class="lg:hidden fixed bottom-0 left-0 w-full bg-surface-container-lowest p-container-margin shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
        <button 
          @click="saveTransaction"
          :disabled="isSubmitDisabled"
          :class="[
            'w-full h-[56px] text-white font-label-md text-label-md rounded-xl transition-transform shadow-md flex items-center justify-center gap-2',
            (isSubmitDisabled) ? 'bg-secondary opacity-80 cursor-not-allowed' : 'bg-primary-container active:scale-95'
          ]"
        >
          <span v-if="!isSubmitting && !isSuccess" class="material-symbols-outlined fill">save</span>
          <span v-if="isSubmitting" class="material-symbols-outlined animate-spin">sync</span>
          <span v-if="isSuccess" class="material-symbols-outlined fill">check_circle</span>
          {{ isSuccess ? 'Tersimpan' : (isSubmitting ? 'Menyimpan...' : 'Simpan Transaksi') }}
        </button>
      </div>

    </main>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { drainOutbox } from '../../utils/waOutbox';

const router = useRouter();

const monthsList = ['Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];

const isSubmitting = ref(false);
const isSuccess = ref(false);
const inputMode = ref('satuan'); // 'satuan' atau 'massal'

const form = reactive({
  kelas_id: '',
  siswa_id: '',
  siswa_ids: [],
  tanggal: '',
  nominal: 30000,
  selectedMonths: []
});

const kelasi = ref([]);
const students = ref([]);
const searchQuery = ref('');
const isFetchingStudents = ref(false);
const paidMonths = ref([]); // for satuan

const filteredStudents = computed(() => {
  if (!searchQuery.value) return students.value;
  const q = searchQuery.value.toLowerCase();
  return students.value.filter(s => s.fullName.toLowerCase().includes(q));
});

const unpaidMonthsForSatuan = computed(() => {
  return monthsList.filter(m => !paidMonths.value.includes(m));
});

const isSubmitDisabled = computed(() => {
  if (isSubmitting.value) return true;
  if (form.selectedMonths.length === 0) return true;
  if (inputMode.value === 'satuan' && !form.siswa_id) return true;
  if (inputMode.value === 'massal' && form.siswa_ids.length === 0) return true;
  if (!form.kelas_id) return true;
  if (!form.tanggal) return true;
  return false;
});

const setInputMode = (mode) => {
  inputMode.value = mode;
  // reset form when mode changes
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
  if (!name) return '';
  const parts = name.split(' ');
  if (parts.length >= 2) {
    return parts[0][0] + parts[1][0];
  }
  return name.substring(0, 2);
};

onMounted(async () => {
  // Set default date to today
  const today = new Date();
  form.tanggal = today.toISOString().split('T')[0];

  try {
    const res = await axios.get('/kelas');
    kelasi.value = res.data;
  } catch (e) {
    console.error("Gagal mengambil kelas", e);
  }
});

// -------------- WATCHERS FOR SATUAN --------------
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
      students.value = res.data.filter(s => {
        const cls = kelasi.value.find(k => k.id === newKelasId);
        return s.kelas === cls.nama_kelas;
      }).sort((a, b) => String(a.fullName).localeCompare(String(b.fullName), 'id'));
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
        'January': 'Jan', 'February': 'Feb', 'March': 'Mar', 'April': 'Apr',
        'May': 'Mei', 'June': 'Jun', 'July': 'Jul', 'August': 'Agu',
        'September': 'Sep', 'October': 'Okt', 'November': 'Nov', 'December': 'Des'
      };
      
      paidMonths.value = res.data.map(i => engToIdMap[i.bulan] || i.bulan);
    } catch (error) {
      console.error(error);
    }
  }
});

// -------------- WATCHERS FOR MASSAL --------------
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
      students.value = [...res.data].sort((a, b) => String(a.fullName).localeCompare(String(b.fullName), 'id'));
    } catch (e) {
      console.error(e);
    } finally {
      isFetchingStudents.value = false;
    }
  }
}, { deep: true });

// -------------- METHODS --------------
const toggleMonth = (month) => {
  // Only for satuan mode (allows multiple)
  const index = form.selectedMonths.indexOf(month);
  if (index === -1) {
    form.selectedMonths.push(month);
  } else {
    form.selectedMonths.splice(index, 1);
  }
};

const toggleMonthMassal = (month) => {
  // For massal mode - allows multiple months now
  const index = form.selectedMonths.indexOf(month);
  if (index === -1) {
    form.selectedMonths.push(month);
  } else {
    form.selectedMonths.splice(index, 1);
  }
  // Reset students when months change
  form.siswa_ids = [];
  students.value = [];
};

const totalTransaksi = computed(() => {
  if (inputMode.value === 'massal') {
    return form.siswa_ids.length * form.selectedMonths.length;
  }
  return form.selectedMonths.length;
});

const totalBayar = computed(() => {
  return totalTransaksi.value * form.nominal;
});

const formatRupiah = (angka) => {
  return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};

const saveTransaction = async () => {
  if(form.selectedMonths.length === 0) {
    alert("Pilih minimal satu bulan!");
    return;
  }

  const isoTanggal = form.tanggal;
  if (!isoTanggal) {
    alert("Tanggal bayar belum diisi.");
    return;
  }

  if(!form.kelas_id) {
    alert("Lengkapi semua field yang diperlukan!");
    return;
  }
  
    if (inputMode.value === 'satuan' && !form.siswa_id) {
      alert("Silakan pilih siswa!");
      return;
    }
    if (inputMode.value === 'massal' && form.siswa_ids.length === 0) {
      alert("Silakan pilih minimal satu siswa untuk input massal!");
      return;
    }

  isSubmitting.value = true;
  
  try {
    let payload = {
      kelas_id: form.kelas_id,
      tanggal_bayar: form.tanggal,
      nominal: form.nominal,
      months: form.selectedMonths
    };

    if (inputMode.value === 'massal') {
      payload.siswa_ids = form.siswa_ids;
    } else {
      payload.siswa_id = form.siswa_id;
    }

    await axios.post('/infak', payload);

    // Kirim konfirmasi WA ke wali secara bertahap (dengan delay) di background
    drainOutbox().catch(() => {});

    isSuccess.value = true;
    setTimeout(() => {
      isSuccess.value = false;
      // Reset form
      form.kelas_id = '';
      form.siswa_id = '';
      form.siswa_ids = [];
      form.selectedMonths = [];
      form.nominal = 30000;
      form.tanggal = new Date().toISOString().split('T')[0];
    }, 1500);
  } catch (error) {
    console.error(error);
    alert("Gagal menyimpan infak");
  } finally {
    isSubmitting.value = false;
  }
};
</script>