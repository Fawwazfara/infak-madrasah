<template>
  <div class="ui-page flex flex-col gap-6">
    <!-- Header & Export PDF -->
    <div class="ui-stagger ui-stagger-1 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <div class="flex flex-wrap items-center gap-2 mb-1">
          <h2 class="text-2xl sm:text-[26px] font-bold text-[#1C1917] tracking-tight">Laporan Keuangan Bulanan</h2>
          <span
            v-if="isGuruView"
            class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-0.5 rounded-full bg-[#FAF4E4] text-[#9A6B16]"
          >
            <UiIcon name="info" :size="13" :weight="600" />
            Kasbon Guru Disamarkan
          </span>
        </div>
        <p class="text-sm text-[#57534E]">
          Rekapitulasi pemasukan infak per kelas dan pengeluaran madrasah untuk bulan <strong>{{ activeMonth }}</strong>.
        </p>
      </div>

      <button
        type="button"
        @click="cetakPdf"
        :disabled="isDownloading"
        class="ui-btn-navy ui-btn-icon-l self-start sm:self-auto"
      >
        <UiIcon v-if="isDownloading" name="progress_activity" :size="17" :weight="600" class="animate-spin" />
        <UiIcon v-else name="print" :size="17" :weight="600" />
        <span>{{ isDownloading ? 'Menyiapkan PDF...' : 'Cetak Laporan PDF' }}</span>
      </button>
    </div>

    <!-- Month Filter Pills -->
    <div class="ui-stagger ui-stagger-1 flex overflow-x-auto gap-2 pb-1 no-scrollbar">
      <button
        v-for="month in months"
        :key="month"
        type="button"
        @click="activeMonth = month"
        :class="[
          'min-h-[38px] px-4 rounded-full text-xs sm:text-sm font-semibold transition-[background-color,color,box-shadow,transform] duration-150 ease-out active:scale-[0.96] shrink-0 border',
          activeMonth === month
            ? 'bg-[#292524] text-[#FDFCF9] border-transparent shadow-[var(--shadow-btn-navy)]'
            : 'bg-[#FDFCF9] text-[#57534E] border-[#E6E1D6] hover:text-[#1C1917] hover:bg-[#F7F4ED]'
        ]"
      >
        {{ month }}
      </button>
    </div>

    <!-- Summary KPI Cards -->
    <section class="ui-stagger ui-stagger-2 grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="ui-panel p-5 bg-[#F9EFE7]/75 flex items-center justify-between gap-3">
        <div>
          <span class="text-xs font-semibold uppercase tracking-wider text-[#7C3A10]">Total Pemasukan</span>
          <p class="text-2xl font-extrabold font-mono tabular-nums text-[#A8551E] mt-1">
            Rp {{ formatRupiah(totalPemasukan) }}
          </p>
        </div>
        <div class="w-10 h-10 rounded-[11px] bg-[#F2DFCF] text-[#A8551E] flex items-center justify-center shrink-0">
          <UiIcon name="trending_up" :size="20" :weight="600" />
        </div>
      </div>

      <div class="ui-panel p-5 bg-[#FDF2F4]/80 flex items-center justify-between gap-3">
        <div>
          <span class="text-xs font-semibold uppercase tracking-wider text-[#9F1239]">Total Pengeluaran</span>
          <p class="text-2xl font-extrabold font-mono tabular-nums text-[#9F1239] mt-1">
            Rp {{ formatRupiah(totalPengeluaran) }}
          </p>
        </div>
        <div class="w-10 h-10 rounded-[11px] bg-[#FADFE5] text-[#9F1239] flex items-center justify-center shrink-0">
          <UiIcon name="trending_down" :size="20" :weight="600" />
        </div>
      </div>

      <div class="rounded-[16px] p-5 bg-gradient-to-br from-[#292524] to-[#1C1917] text-[#FDFCF9] shadow-[var(--shadow-navy-card)] flex items-center justify-between gap-3">
        <div>
          <span class="text-xs font-semibold uppercase tracking-wider text-[#D6CFC2]">Saldo Bulan {{ activeMonth }}</span>
          <p class="text-2xl font-extrabold font-mono tabular-nums mt-1">
            Rp {{ formatRupiah(saldoAkhir) }}
          </p>
        </div>
        <div class="w-10 h-10 rounded-[11px] bg-white/10 text-[#F4A26E] flex items-center justify-center shrink-0">
          <UiIcon name="account_balance" :size="20" :weight="600" />
        </div>
      </div>
    </section>

    <!-- Mobile Concentric Switch -->
    <div class="md:hidden">
      <div class="ui-segmented w-full">
        <button
          type="button"
          @click="mobileView = 'pemasukan'"
          :class="['ui-segmented-item flex-1 text-xs', mobileView === 'pemasukan' ? 'ui-segmented-item-active' : 'ui-segmented-item-idle']"
        >
          Pemasukan Kelas
        </button>
        <button
          type="button"
          @click="mobileView = 'pengeluaran'"
          :class="['ui-segmented-item flex-1 text-xs', mobileView === 'pengeluaran' ? 'ui-segmented-item-active' : 'ui-segmented-item-idle']"
        >
          Daftar Pengeluaran
        </button>
      </div>
    </div>

    <!-- Main 2-Column Ledger -->
    <div class="ui-stagger ui-stagger-3 grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
      <!-- Pemasukan per Kelas (6 cols) -->
      <section v-show="mobileView === 'pemasukan' || isDesktop" class="md:col-span-6 ui-panel-padded space-y-4">
        <div class="flex items-center justify-between gap-2 pb-3 border-b border-black/[0.06]">
          <div>
            <h3 class="text-base font-bold text-[#1C1917]">Pemasukan per Kelas</h3>
            <p class="text-xs text-[#78716C]">Ketuk salah satu kelas untuk melihat rincian siswa yang membayar.</p>
          </div>
          <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-[8px] bg-[#ECE8DF] text-[#292524]">
            {{ pemasukanList.length }} Kelas
          </span>
        </div>

        <div v-if="isLoading" class="space-y-2.5">
          <div v-for="n in 5" :key="n" class="h-14 rounded-[12px] bg-[#F7F4ED] animate-pulse"></div>
        </div>

        <div v-else class="flex flex-col gap-2.5">
          <div v-for="item in pemasukanList" :key="item.kelas_id" class="flex flex-col">
            <button
              type="button"
              @click="toggleDetail(item)"
              :aria-expanded="openKelasId === item.kelas_id"
              class="w-full p-3.5 rounded-[12px] border border-transparent text-left flex flex-col gap-2 transition-[background-color,box-shadow,transform] duration-150 ease-out active:scale-[0.99]"
              :class="openKelasId === item.kelas_id
                ? 'bg-[#F9EFE7] shadow-[inset_0_0_0_1px_oklch(0.55_0.13_42_/_0.24)]'
                : 'bg-[#F7F4ED] shadow-[var(--shadow-border)] hover:bg-[#FDFCF9] hover:shadow-[var(--shadow-border-hover)]'"
            >
              <div class="flex items-center justify-between gap-3 w-full">
                <div class="flex items-center gap-3 min-w-0">
                  <div class="w-8 h-8 rounded-[8px] bg-[#FDFCF9] text-[#A8551E] flex items-center justify-center shrink-0 shadow-[var(--shadow-border)]">
                    <UiIcon name="account_balance_wallet" :size="16" :weight="600" />
                  </div>
                  <span class="text-sm font-bold text-[#1C1917] truncate">{{ item.kelas }}</span>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                  <span class="text-sm font-extrabold font-mono tabular-nums text-[#A8551E]">
                    Rp {{ formatRupiah(item.nominal) }}
                  </span>
                  <UiIcon
                    name="expand_more"
                    :size="17"
                    :weight="600"
                    class="text-[#78716C] transition-transform duration-150"
                    :class="{ 'rotate-180': openKelasId === item.kelas_id }"
                  />
                </div>
              </div>
              <!-- Visual percentage bar relative to max class income -->
              <div class="w-full h-1.5 rounded-full bg-[#ECE8DF] overflow-hidden">
                <div
                  class="h-full rounded-full bg-[#A8551E] transition-[width] duration-300 ease-out-expo"
                  :style="{ width: barWidth(item.nominal) + '%' }"
                ></div>
              </div>
            </button>

            <!-- Drill-down student breakdown -->
            <div
              v-if="openKelasId === item.kelas_id"
              class="mt-2 ml-3 pl-3.5 border-l-2 border-[#A8551E]/40 space-y-2"
            >
              <div v-if="detailLoading" class="flex items-center gap-2 py-2.5 text-xs text-[#78716C]">
                <UiIcon name="progress_activity" :size="15" :weight="600" class="animate-spin" />
                <span>Memuat rincian pembayaran siswa...</span>
              </div>
              <div v-else-if="detailError" class="py-2 text-xs text-[#9F1239]">{{ detailError }}</div>
              <template v-else-if="detailData">
                <div class="flex items-center justify-between text-xs text-[#78716C] py-1">
                  <span>{{ detailData.jumlah_transaksi }} santri membayar · {{ detailData.bulan }}</span>
                  <span class="font-mono font-bold text-[#A8551E]">Rp {{ formatRupiah(detailData.total) }}</span>
                </div>
                <div
                  v-for="(row, idx) in detailData.rows"
                  :key="idx"
                  class="p-2.5 rounded-[10px] bg-[#FDFCF9] shadow-[var(--shadow-border)] flex items-center justify-between gap-3"
                >
                  <div class="min-w-0">
                    <p class="text-xs font-bold text-[#1C1917] truncate">{{ row.siswa }}</p>
                    <p class="text-[11px] text-[#78716C] mt-0.5">{{ formatTanggal(row.tanggal_bayar) }} &middot; {{ row.bulan }}</p>
                  </div>
                  <span class="text-xs font-bold font-mono tabular-nums text-[#A8551E] shrink-0">
                    Rp {{ formatRupiah(row.jumlah) }}
                  </span>
                </div>
                <p v-if="!detailData.rows.length" class="text-xs text-[#78716C] italic py-2">
                  Belum ada pembayaran tercatat di kelas ini pada bulan {{ activeMonth }}.
                </p>
              </template>
            </div>
          </div>
        </div>
      </section>

      <!-- Riwayat Pengeluaran (6 cols) -->
      <section v-show="mobileView === 'pengeluaran' || isDesktop" class="md:col-span-6 ui-panel-padded space-y-4">
        <div class="flex items-center justify-between gap-2 pb-3 border-b border-black/[0.06]">
          <div>
            <h3 class="text-base font-bold text-[#1C1917]">Riwayat Pengeluaran</h3>
            <p class="text-xs text-[#78716C]">
              {{ isGuruView ? 'Nama guru pada pos kasbon disamarkan untuk menjaga privasi.' : 'Daftar lengkap pengeluaran operasional & kasbon bulan ini.' }}
            </p>
          </div>
          <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-[8px] bg-[#FDF2F4] text-[#9F1239]">
            {{ pengeluaranList.length }} Item
          </span>
        </div>

        <div v-if="isLoading" class="space-y-2.5">
          <div v-for="n in 4" :key="n" class="h-16 rounded-[12px] bg-[#F7F4ED] animate-pulse"></div>
        </div>

        <div v-else-if="pengeluaranList.length === 0" class="py-10 text-center">
          <div class="w-11 h-11 mx-auto rounded-[11px] bg-[#ECE8DF] text-[#78716C] flex items-center justify-center mb-2.5">
            <UiIcon name="receipt_long" :size="20" :weight="500" />
          </div>
          <p class="text-sm font-bold text-[#1C1917]">Tidak ada pengeluaran</p>
          <p class="text-xs text-[#78716C] mt-1">Belum ada catatan pengeluaran untuk bulan {{ activeMonth }}.</p>
        </div>

        <div v-else class="flex flex-col gap-2.5">
          <div
            v-for="item in pengeluaranList"
            :key="item.id"
            class="p-3.5 rounded-[12px] bg-[#F7F4ED] border border-transparent shadow-[var(--shadow-border)] flex items-center gap-3.5"
          >
            <div class="w-11 h-12 rounded-[10px] bg-[#FDFCF9] shadow-[var(--shadow-border)] flex flex-col items-center justify-center shrink-0">
              <span class="text-xs font-extrabold font-mono tabular-nums text-[#1C1917] leading-none">{{ getDay(item.tanggal) }}</span>
              <span class="text-[10px] font-semibold uppercase text-[#78716C] mt-1 leading-none">{{ getMonthShort(item.tanggal) }}</span>
            </div>

            <div class="flex-1 min-w-0">
              <div class="flex flex-wrap items-center gap-1.5">
                <h4 class="text-sm font-bold text-[#1C1917] truncate">{{ item.keterangan }}</h4>
                <span
                  v-if="item.is_anonymized"
                  class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#FAF4E4] text-[#9A6B16] shrink-0"
                >
                  Disamarkan
                </span>
                <span
                  v-else-if="item.kategori"
                  class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-[#ECE8DF] text-[#57534E] shrink-0"
                >
                  {{ item.kategori }}
                </span>
              </div>
              <p class="text-xs text-[#78716C] mt-0.5 font-mono">{{ formatTanggal(item.tanggal) }}</p>
            </div>

            <span class="text-sm font-extrabold font-mono tabular-nums text-[#9F1239] shrink-0">
              Rp {{ formatRupiah(item.jumlah) }}
            </span>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';
import { notify } from '@/utils/toast';

const getCurrentMonthIndo = () => {
  const month = new Intl.DateTimeFormat('id-ID', { month: 'long' }).format(new Date());
  return month.charAt(0).toUpperCase() + month.slice(1);
};

const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const activeMonth = ref(getCurrentMonthIndo());

const mobileView = ref('pemasukan');
const windowWidth = ref(window.innerWidth);
const updateWidth = () => { windowWidth.value = window.innerWidth; };
const isDesktop = computed(() => windowWidth.value >= 768);

const pemasukanList = ref([]);
const pengeluaranList = ref([]);
const totalPemasukan = ref(0);
const totalPengeluaran = ref(0);
const isGuruView = ref(false);
const isLoading = ref(false);

const openKelasId = ref(null);
const detailData = ref(null);
const detailLoading = ref(false);
const detailError = ref('');

const maxClassIncome = computed(() => {
  const vals = pemasukanList.value.map(i => Number(i.nominal || 0));
  return Math.max(1, ...vals);
});

const barWidth = (nominal) => {
  const n = Number(nominal || 0);
  if (n <= 0) return 0;
  return Math.max(4, Math.round((n / maxClassIncome.value) * 100));
};

const saldoAkhir = computed(() => Number(totalPemasukan.value || 0) - Number(totalPengeluaran.value || 0));

const toggleDetail = async (item) => {
  if (openKelasId.value === item.kelas_id) {
    openKelasId.value = null;
    detailData.value = null;
    detailError.value = '';
    return;
  }
  const requestedId = item.kelas_id;
  openKelasId.value = requestedId;
  detailData.value = null;
  detailError.value = '';
  detailLoading.value = true;
  try {
    const res = await axios.get('/laporan/detail', {
      params: { bulan: activeMonth.value, kelas_id: requestedId }
    });
    if (openKelasId.value === requestedId) detailData.value = res.data;
  } catch (err) {
    console.error('Gagal mengambil rincian laporan', err);
    if (openKelasId.value === requestedId) detailError.value = 'Gagal memuat rincian pembayaran.';
  } finally {
    if (openKelasId.value === requestedId) detailLoading.value = false;
  }
};

const fetchLaporan = async () => {
  isLoading.value = true;
  try {
    const res = await axios.get('/laporan', { params: { bulan: activeMonth.value } });
    pemasukanList.value = res.data.pemasukan_per_kelas || [];
    pengeluaranList.value = res.data.pengeluaran_list || [];
    totalPemasukan.value = res.data.total_pemasukan || 0;
    totalPengeluaran.value = res.data.total_pengeluaran || 0;
    isGuruView.value = !!res.data.is_guru_view;
  } catch (err) {
    console.error('Gagal mengambil laporan', err);
    notify('Gagal memuat laporan keuangan.', 'error');
  } finally {
    isLoading.value = false;
  }
};

watch(activeMonth, () => {
  openKelasId.value = null;
  detailData.value = null;
  detailError.value = '';
  fetchLaporan();
});

const isDownloading = ref(false);
const cetakPdf = async () => {
  if (isDownloading.value) return;
  isDownloading.value = true;
  try {
    const response = await axios.get('/laporan/cetak-pdf', {
      params: { bulan: activeMonth.value },
      responseType: 'blob'
    });
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `laporan_keuangan_${activeMonth.value.toLowerCase()}_${new Date().getFullYear()}.pdf`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    notify('Laporan PDF berhasil diunduh.', 'success');
  } catch (error) {
    console.error('Gagal export PDF', error);
    notify('Gagal mengunduh file PDF laporan.', 'error');
  } finally {
    isDownloading.value = false;
  }
};

onMounted(() => {
  window.addEventListener('resize', updateWidth);
  fetchLaporan();
});

onUnmounted(() => {
  window.removeEventListener('resize', updateWidth);
});

const getDay = (dateStr) => {
  if (!dateStr) return '';
  return String(dateStr).split('T')[0].split('-')[2] || '';
};

const getMonthShort = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleString('id-ID', { month: 'short' }).replace('.', '');
};

const formatTanggal = (dateStr) => {
  if (!dateStr) return '-';
  const clean = String(dateStr).split('T')[0];
  const [y, , d] = clean.split('-');
  return `${parseInt(d, 10)} ${getMonthShort(clean)} ${y}`;
};

const formatRupiah = (angka) => {
  return Number(angka || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};
</script>

