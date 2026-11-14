<template>
  <div class="ui-page flex flex-col gap-6">
    <!-- Top Header & Quick Actions -->
    <div class="ui-stagger ui-stagger-1 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl sm:text-[26px] font-bold text-[#1C1917] tracking-tight">Ringkasan Keuangan Madrasah</h2>
        <p class="text-sm text-[#57534E] mt-1">Pantau arus kas infak, pengeluaran, serta tingkat kepatuhan kelas bulan {{ currentMonth || 'berjalan' }}.</p>
      </div>

      <div class="flex flex-wrap items-center gap-2.5">
        <router-link to="/admin/infak/create" class="ui-btn-primary ui-btn-icon-l">
          <UiIcon name="volunteer_activism" :size="17" :weight="600" />
          <span>Catat Infak</span>
        </router-link>
        <router-link to="/admin/laporan" class="ui-btn-secondary ui-btn-icon-l">
          <UiIcon name="account_balance" :size="17" :weight="600" />
          <span>Laporan Bulanan</span>
        </router-link>
        <button
          type="button"
          @click="confirmReminderModal = true"
          :disabled="isSending"
          class="ui-btn-navy ui-btn-icon-l"
        >
          <UiIcon v-if="isSending" name="progress_activity" :size="17" :weight="600" class="animate-spin" />
          <UiIcon v-else name="notifications_active" :size="17" :weight="600" />
          <span>{{ isSending ? 'Mengirim...' : 'Ingatkan Guru' }}</span>
        </button>
      </div>
    </div>

    <!-- Row 1: KPI Cards -->
    <section class="ui-stagger ui-stagger-2 grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-5">
      <!-- Hero Card: Saldo Akhir -->
      <div class="rounded-[18px] bg-gradient-to-br from-[#292524] to-[#1C1917] text-[#FDFCF9] p-5 sm:p-6 shadow-[var(--shadow-navy-card)] flex flex-col justify-between gap-4">
        <div class="flex items-start justify-between gap-3">
          <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-[#D6CFC2]">Saldo Kas Saat Ini</span>
            <h3 class="text-2xl sm:text-[28px] font-extrabold font-mono tabular-nums mt-1.5 tracking-tight">
              Rp {{ formatRupiah(stats.saldo_all_time) }}
            </h3>
          </div>
          <div class="w-10 h-10 rounded-[11px] bg-white/10 border border-white/15 flex items-center justify-center text-[#F4A26E] shrink-0">
            <UiIcon name="savings" :size="20" :weight="600" />
          </div>
        </div>
        <p class="text-xs text-[#D6CFC2]">Akumulasi kas bersih seluruh periode setelah dikurangi pengeluaran.</p>
      </div>

      <!-- Pemasukan -->
      <div class="ui-panel-padded flex flex-col justify-between gap-4">
        <div class="flex items-start justify-between gap-3">
          <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-[#78716C]">Total Pemasukan</span>
            <h3 class="text-2xl sm:text-[26px] font-extrabold font-mono tabular-nums text-[#1C1917] mt-1.5">
              Rp {{ formatRupiah(stats.pemasukan_all_time) }}
            </h3>
          </div>
          <div class="w-10 h-10 rounded-[11px] bg-[#F9EFE7] text-[#A8551E] flex items-center justify-center shrink-0">
            <UiIcon name="account_balance_wallet" :size="20" :weight="600" />
          </div>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-2 pt-3 border-t border-black/[0.06] text-xs">
          <span class="inline-flex items-center gap-1 font-semibold" :class="stats.persen_pemasukan >= 0 ? 'text-[#A8551E]' : 'text-[#9F1239]'">
            <UiIcon :name="stats.persen_pemasukan >= 0 ? 'trending_up' : 'trending_down'" :size="15" :weight="600" />
            <span class="font-mono tabular-nums">{{ stats.persen_pemasukan >= 0 ? '+' : '' }}{{ stats.persen_pemasukan }}%</span>
            <span class="text-[#78716C] font-normal">vs bulan lalu</span>
          </span>
          <span class="font-mono tabular-nums font-semibold text-[#44403C]">
            Bln ini: Rp {{ formatRupiah(stats.pemasukan_bulan_ini) }}
          </span>
        </div>
      </div>

      <!-- Pengeluaran -->
      <div class="ui-panel-padded flex flex-col justify-between gap-4">
        <div class="flex items-start justify-between gap-3">
          <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-[#78716C]">Pengeluaran Bulan Ini</span>
            <h3 class="text-2xl sm:text-[26px] font-extrabold font-mono tabular-nums text-[#1C1917] mt-1.5">
              Rp {{ formatRupiah(stats.pengeluaran_bulan_ini) }}
            </h3>
          </div>
          <div class="w-10 h-10 rounded-[11px] bg-[#FDF2F4] text-[#9F1239] flex items-center justify-center shrink-0">
            <UiIcon name="money_off" :size="20" :weight="600" />
          </div>
        </div>
        <div class="flex items-center justify-between gap-2 pt-3 border-t border-black/[0.06] text-xs">
          <span class="inline-flex items-center gap-1 font-semibold" :class="stats.persen_pengeluaran > 0 ? 'text-[#9F1239]' : 'text-[#A8551E]'">
            <UiIcon :name="stats.persen_pengeluaran >= 0 ? 'trending_up' : 'trending_down'" :size="15" :weight="600" />
            <span class="font-mono tabular-nums">{{ stats.persen_pengeluaran >= 0 ? '+' : '' }}{{ stats.persen_pengeluaran }}%</span>
            <span class="text-[#78716C] font-normal">dibanding bulan lalu</span>
          </span>
          <router-link to="/admin/pengeluaran" class="font-semibold text-[#A8551E] hover:underline">Kelola</router-link>
        </div>
      </div>
    </section>

    <!-- Row 2: 8/4 Split — 6-Month Chart + Kepatuhan Carousel -->
    <section class="ui-stagger ui-stagger-3 grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
      <!-- Line Chart (8 cols) -->
      <div class="lg:col-span-8 ui-panel-padded flex flex-col gap-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <h3 class="text-base font-bold text-[#1C1917]">Grafik Arus Kas (6 Bulan Terakhir)</h3>
            <p class="text-xs text-[#78716C] mt-0.5">Perbandingan total penerimaan infak dan pengeluaran madrasah.</p>
          </div>
          <div class="flex items-center gap-4 text-xs font-semibold">
            <span class="inline-flex items-center gap-1.5 text-[#1C1917]">
              <span class="w-2.5 h-2.5 rounded-full bg-[#A8551E]"></span> Pemasukan
            </span>
            <span class="inline-flex items-center gap-1.5 text-[#57534E]">
              <span class="w-2.5 h-2.5 rounded-full bg-[#9F1239]"></span> Pengeluaran
            </span>
          </div>
        </div>

        <div class="w-full overflow-x-auto pb-1 flex-1 flex items-center" v-if="chartReady">
          <div class="min-w-[460px] w-full h-[250px] relative">
            <Line :data="chartData" :options="chartOptions" />
          </div>
        </div>
        <div v-else class="h-[250px] rounded-[12px] bg-[#F7F4ED] animate-pulse"></div>
      </div>

      <!-- Kepatuhan Carousel (4 cols) -->
      <div class="lg:col-span-4 ui-panel-padded flex flex-col justify-between gap-4">
        <div class="flex items-center justify-between gap-2">
          <div>
            <h3 class="text-base font-bold text-[#1C1917]">Kepatuhan {{ currentMonth }}</h3>
            <p class="text-xs text-[#78716C] mt-0.5">Status lunas per kelas</p>
          </div>
          <div v-if="klassesData.length > 0" class="flex items-center gap-1.5">
            <button
              type="button"
              @click="prevSlide"
              :disabled="currentSlide === 0"
              aria-label="Kelas sebelumnya"
              class="min-w-[36px] min-h-[36px] rounded-[9px] border border-transparent bg-[#F7F4ED] shadow-[var(--shadow-border)] hover:bg-[#FDFCF9] hover:shadow-[var(--shadow-border-hover)] text-[#1C1917] flex items-center justify-center transition-[background-color,box-shadow,transform] duration-150 ease-out enabled:active:scale-[0.96] disabled:opacity-40 disabled:cursor-not-allowed"
            >
              <UiIcon name="chevron_left" :size="18" :weight="600" />
            </button>
            <button
              type="button"
              @click="nextSlide"
              :disabled="currentSlide === klassesData.length - 1"
              aria-label="Kelas berikutnya"
              class="min-w-[36px] min-h-[36px] rounded-[9px] border border-transparent bg-[#F7F4ED] shadow-[var(--shadow-border)] hover:bg-[#FDFCF9] hover:shadow-[var(--shadow-border-hover)] text-[#1C1917] flex items-center justify-center transition-[background-color,box-shadow,transform] duration-150 ease-out enabled:active:scale-[0.96] disabled:opacity-40 disabled:cursor-not-allowed"
            >
              <UiIcon name="chevron_right" :size="18" :weight="600" />
            </button>
          </div>
        </div>

        <div v-if="klassesData.length > 0" class="flex-1 flex flex-col items-center justify-center gap-4 py-2">
          <span class="text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-full bg-[#ECE8DF] text-[#292524]">
            {{ klassesData[currentSlide].nama_kelas }} · {{ currentSlide + 1 }}/{{ klassesData.length }}
          </span>

          <div class="w-[136px] h-[136px] relative">
            <Doughnut :data="klassesData[currentSlide].pieData" :options="pieOptions" :key="'pie-' + currentSlide" />
            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
              <span class="text-2xl font-extrabold font-mono tabular-nums text-[#1C1917]">{{ klassesData[currentSlide].persentase }}%</span>
              <span class="text-[11px] font-medium text-[#78716C]">Lunas</span>
            </div>
          </div>

          <div class="w-full grid grid-cols-2 gap-2.5 pt-2 border-t border-black/[0.06]">
            <div class="p-2.5 rounded-[10px] bg-[#F9EFE7] text-center">
              <p class="text-base font-extrabold font-mono tabular-nums text-[#A8551E]">{{ klassesData[currentSlide].lunas }}</p>
              <p class="text-[11px] font-semibold text-[#7C3A10]">Sudah Bayar</p>
            </div>
            <div class="p-2.5 rounded-[10px] bg-[#F7F4ED] text-center">
              <p class="text-base font-extrabold font-mono tabular-nums text-[#44403C]">{{ klassesData[currentSlide].nunggak }}</p>
              <p class="text-[11px] font-semibold text-[#78716C]">Belum Bayar</p>
            </div>
          </div>
        </div>
        <div v-else class="flex-1 min-h-[210px] rounded-[12px] bg-[#F7F4ED] animate-pulse"></div>
      </div>
    </section>

    <!-- Confirmation Modal: Send Push Reminder -->
    <div
      v-if="confirmReminderModal"
      class="fixed inset-0 z-[110] flex items-center justify-center bg-[#1C1917]/50 backdrop-blur-[2px] p-4 overscroll-contain"
      @click="confirmReminderModal = false"
    >
      <div class="ui-panel-elevated w-full max-w-sm p-6 text-center overscroll-contain" @click.stop>
        <div class="mx-auto w-14 h-14 bg-[#F9EFE7] text-[#A8551E] rounded-full flex items-center justify-center mb-4">
          <UiIcon name="notifications_active" :size="24" :weight="600" />
        </div>
        <h3 class="text-lg font-bold text-[#1C1917]">Kirim Pengingat Tunggakan?</h3>
        <p class="text-sm text-[#57534E] mt-2 mb-6 leading-relaxed">
          Semua wali kelas akan menerima notifikasi push untuk mengingatkan siswa yang menunggak bulan ini.
        </p>
        <div class="flex gap-3">
          <button type="button" class="ui-btn-secondary flex-1" @click="confirmReminderModal = false">Batal</button>
          <button type="button" class="ui-btn-navy flex-1" @click="sendTunggakanReminder">Ya, Kirim</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { notify } from '@/utils/toast';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  ArcElement,
  Title,
  Tooltip,
  Filler
} from 'chart.js';
import { Line, Doughnut } from 'vue-chartjs';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  ArcElement,
  Title,
  Tooltip,
  Filler
);

const stats = ref({
  pemasukan_all_time: 0,
  pemasukan_bulan_ini: 0,
  pengeluaran_bulan_ini: 0,
  saldo_all_time: 0,
  persen_pemasukan: 0,
  persen_pengeluaran: 0
});

const chartReady = ref(false);
const isSending = ref(false);
const confirmReminderModal = ref(false);

const sendTunggakanReminder = async () => {
  confirmReminderModal.value = false;
  isSending.value = true;
  try {
    const res = await axios.post('/push-send-reminders');
    notify(res.data.message || 'Notifikasi pengingat berhasil dikirim ke wali kelas.', 'success');
  } catch (err) {
    console.error(err);
    notify('Gagal mengirim notifikasi pengingat.', 'error');
  } finally {
    isSending.value = false;
  }
};

const formatRupiah = (angka) => {
  return Number(angka || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};

const chartData = ref({
  labels: [],
  datasets: [
    {
      label: 'Pemasukan',
      backgroundColor: 'rgba(168, 85, 30, 0.12)',
      borderColor: '#A8551E',
      borderWidth: 2.5,
      pointBackgroundColor: '#FDFCF9',
      pointBorderColor: '#A8551E',
      pointBorderWidth: 2,
      pointRadius: 4,
      fill: true,
      data: [],
      tension: 0.35
    },
    {
      label: 'Pengeluaran',
      backgroundColor: 'rgba(159, 18, 57, 0.0)',
      borderColor: '#9F1239',
      borderWidth: 1.75,
      pointBackgroundColor: '#FDFCF9',
      pointBorderColor: '#9F1239',
      pointBorderWidth: 1.5,
      pointRadius: 3,
      fill: false,
      data: [],
      tension: 0.35
    }
  ]
});

const currentSlide = ref(0);
const klassesData = ref([]);
const currentMonth = ref('');

const nextSlide = () => {
  if (currentSlide.value < klassesData.value.length - 1) {
    currentSlide.value++;
  }
};

const prevSlide = () => {
  if (currentSlide.value > 0) {
    currentSlide.value--;
  }
};

const pieOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  cutout: '72%',
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#292524',
      padding: 10,
      cornerRadius: 8,
      callbacks: {
        label: (context) => ` ${context.label}: ${context.parsed} Siswa`
      }
    }
  }
});

const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#292524',
      padding: 10,
      cornerRadius: 8,
      displayColors: true,
      callbacks: {
        label: (context) => {
          const label = context.dataset.label ? `${context.dataset.label}: ` : '';
          return label + new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(context.parsed.y || 0);
        }
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: { color: '#E6E1D6', drawBorder: false },
      ticks: { display: false }
    },
    x: {
      grid: { display: false, drawBorder: false },
      ticks: { color: '#78716C', font: { size: 12 } }
    }
  }
});

onMounted(async () => {
  try {
    const [resKepatuhan, resStat] = await Promise.all([
      axios.get('/dashboard/kepatuhan'),
      axios.get('/dashboard/statistik')
    ]);
    klassesData.value = resKepatuhan.data.kepatuhan_per_kelas || [];
    currentMonth.value = resKepatuhan.data.bulan || '';

    stats.value = resStat.data;
    chartData.value.labels = resStat.data.chart.labels;
    chartData.value.datasets[0].data = resStat.data.chart.pemasukan;
    chartData.value.datasets[1].data = resStat.data.chart.pengeluaran;
    chartReady.value = true;
  } catch (err) {
    console.error('Gagal mengambil statistik', err);
    notify('Gagal memuat statistik dashboard.', 'error');
  }
});
</script>
