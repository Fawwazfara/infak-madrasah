<template>
  <div class="ui-page flex flex-col gap-6">
    <!-- Top Welcome & Actions -->
    <div class="ui-stagger ui-stagger-1 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl sm:text-[26px] font-bold text-[#1C1917] tracking-tight">Ringkasan Guru &amp; Keuangan</h2>
        <p class="text-sm text-[#57534E] mt-1">Pantau kepatuhan kelas Anda sekaligus perkembangan pendapatan dan kas madrasah.</p>
      </div>
      <div class="flex flex-wrap items-center gap-2.5">
        <router-link to="/guru/siswa" class="ui-btn-secondary ui-btn-icon-l">
          <UiIcon name="groups" :size="17" :weight="600" />
          <span>Daftar Siswa</span>
        </router-link>
        <router-link to="/guru/laporan" class="ui-btn-primary ui-btn-icon-l">
          <UiIcon name="account_balance" :size="17" :weight="600" />
          <span>Lihat Laporan</span>
        </router-link>
      </div>
    </div>

    <!-- Notice Banner (Kelas Guru) -->
    <div
      class="ui-stagger ui-stagger-1 rounded-[16px] p-4 sm:p-5 flex items-start sm:items-center justify-between gap-4 border border-transparent"
      :class="totalNunggak > 0
        ? 'bg-[#FAF4E4] shadow-[inset_0_0_0_1px_oklch(0.62_0.12_75_/_0.26)]'
        : 'bg-[#F9EFE7] shadow-[inset_0_0_0_1px_oklch(0.55_0.13_42_/_0.22)]'"
    >
      <div class="flex items-start gap-3.5">
        <div
          class="w-10 h-10 rounded-[11px] flex items-center justify-center shrink-0"
          :class="totalNunggak > 0 ? 'bg-[#F4E6C3] text-[#9A6B16]' : 'bg-[#F2DFCF] text-[#A8551E]'"
        >
          <UiIcon :name="totalNunggak > 0 ? 'warning' : 'check_circle'" :size="20" :weight="600" :filled="totalNunggak === 0" />
        </div>
        <div>
          <p class="text-xs font-bold uppercase tracking-wider" :class="totalNunggak > 0 ? 'text-[#9A6B16]' : 'text-[#A8551E]'">
            {{ totalNunggak > 0 ? 'Perlu Perhatian' : 'Kepatuhan Kelas Baik' }}
          </p>
          <h3 class="text-sm sm:text-base font-bold text-[#1C1917] mt-0.5">
            <template v-if="totalNunggak > 0">
              Ada <span class="font-mono tabular-nums">{{ totalNunggak }}</span> siswa di kelas Anda yang belum membayar infak bulan {{ currentMonth || 'ini' }}.
            </template>
            <template v-else>
              Seluruh siswa di kelas Anda sudah melunasi infak bulan {{ currentMonth || 'ini' }}.
            </template>
          </h3>
        </div>
      </div>
      <router-link to="/guru/siswa" class="text-xs font-bold text-[#1C1917] hover:underline shrink-0">Cek Siswa &rarr;</router-link>
    </div>

    <!-- Row 1: KPI Cards (Pendapatan Madrasah + Kelas Anda) -->
    <section class="ui-stagger ui-stagger-2 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Saldo Kas Madrasah -->
      <div class="rounded-[18px] bg-gradient-to-br from-[#292524] to-[#1C1917] text-[#FDFCF9] p-5 shadow-[var(--shadow-navy-card)] flex flex-col justify-between gap-3">
        <div class="flex items-start justify-between gap-2">
          <span class="text-xs font-semibold uppercase tracking-wider text-[#D6CFC2]">Saldo Kas Madrasah</span>
          <UiIcon name="savings" :size="18" :weight="600" class="text-[#F4A26E]" />
        </div>
        <div>
          <h3 class="text-xl sm:text-2xl font-extrabold font-mono tabular-nums">Rp {{ formatRupiah(stats.saldo_all_time) }}</h3>
          <p class="text-[11px] text-[#D6CFC2] mt-1">Saldo bersih seluruh periode</p>
        </div>
      </div>

      <!-- Total Pemasukan Madrasah -->
      <div class="ui-panel p-5 flex flex-col justify-between gap-3">
        <div class="flex items-start justify-between gap-2">
          <span class="text-xs font-semibold uppercase tracking-wider text-[#78716C]">Pemasukan Madrasah</span>
          <UiIcon name="account_balance_wallet" :size="18" :weight="600" class="text-[#A8551E]" />
        </div>
        <div>
          <h3 class="text-xl sm:text-2xl font-extrabold font-mono tabular-nums text-[#1C1917]">Rp {{ formatRupiah(stats.pemasukan_all_time) }}</h3>
          <div class="flex items-center justify-between gap-2 mt-1.5 text-xs">
            <span class="font-mono tabular-nums font-semibold" :class="stats.persen_pemasukan >= 0 ? 'text-[#A8551E]' : 'text-[#9F1239]'">
              {{ stats.persen_pemasukan >= 0 ? '+' : '' }}{{ stats.persen_pemasukan }}%
            </span>
            <span class="text-[#78716C] font-mono tabular-nums">Bln ini: Rp {{ formatRupiah(stats.pemasukan_bulan_ini) }}</span>
          </div>
        </div>
      </div>

      <!-- Pengeluaran Madrasah Bulan Ini -->
      <div class="ui-panel p-5 flex flex-col justify-between gap-3">
        <div class="flex items-start justify-between gap-2">
          <span class="text-xs font-semibold uppercase tracking-wider text-[#78716C]">Pengeluaran Bulan Ini</span>
          <UiIcon name="money_off" :size="18" :weight="600" class="text-[#9F1239]" />
        </div>
        <div>
          <h3 class="text-xl sm:text-2xl font-extrabold font-mono tabular-nums text-[#1C1917]">Rp {{ formatRupiah(stats.pengeluaran_bulan_ini) }}</h3>
          <p class="text-xs text-[#78716C] mt-1.5 flex items-center justify-between">
            <span class="font-mono tabular-nums font-semibold" :class="stats.persen_pengeluaran > 0 ? 'text-[#9F1239]' : 'text-[#A8551E]'">
              {{ stats.persen_pengeluaran >= 0 ? '+' : '' }}{{ stats.persen_pengeluaran }}% vs bln lalu
            </span>
            <router-link to="/guru/laporan" class="font-semibold text-[#A8551E] hover:underline">Rincian</router-link>
          </p>
        </div>
      </div>

      <!-- Infak Kelas Anda -->
      <div class="ui-panel p-5 flex flex-col justify-between gap-3 bg-[#F9EFE7]/55">
        <div class="flex items-start justify-between gap-2">
          <span class="text-xs font-semibold uppercase tracking-wider text-[#7C3A10]">Infak Kelas Anda (Bln Ini)</span>
          <UiIcon name="class" :size="18" :weight="600" class="text-[#A8551E]" />
        </div>
        <div>
          <h3 class="text-xl sm:text-2xl font-extrabold font-mono tabular-nums text-[#A8551E]">
            Rp {{ formatRupiah(stats.kelas_summary?.pemasukan_bulan_ini || 0) }}
          </h3>
          <p class="text-xs text-[#7C3A10] mt-1.5 font-mono tabular-nums">
            {{ (stats.kelas_summary?.persen_pemasukan ?? 0) >= 0 ? '+' : '' }}{{ stats.kelas_summary?.persen_pemasukan ?? 0 }}% dari bulan lalu
          </p>
        </div>
      </div>
    </section>

    <!-- Row 2: 8/4 Split — Grafik Pendapatan & Pengeluaran + Kepatuhan Kelas -->
    <section class="ui-stagger ui-stagger-3 grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
      <!-- Line Chart (8 cols) -->
      <div class="lg:col-span-8 ui-panel-padded flex flex-col gap-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <h3 class="text-base font-bold text-[#1C1917]">
              {{ chartScope === 'madrasah' ? 'Grafik Pendapatan & Pengeluaran Madrasah' : 'Grafik Pemasukan Kelas Anda' }}
            </h3>
            <p class="text-xs text-[#78716C] mt-0.5">Tren 6 bulan terakhir dalam rupiah.</p>
          </div>

          <div class="ui-segmented">
            <button
              type="button"
              @click="chartScope = 'madrasah'"
              :class="['ui-segmented-item text-xs', chartScope === 'madrasah' ? 'ui-segmented-item-active' : 'ui-segmented-item-idle']"
            >
              Madrasah
            </button>
            <button
              type="button"
              @click="chartScope = 'kelas'"
              :class="['ui-segmented-item text-xs', chartScope === 'kelas' ? 'ui-segmented-item-active' : 'ui-segmented-item-idle']"
            >
              Kelas Anda
            </button>
          </div>
        </div>

        <div class="flex items-center gap-4 text-xs font-semibold">
          <span class="inline-flex items-center gap-1.5 text-[#1C1917]">
            <span class="w-2.5 h-2.5 rounded-full bg-[#A8551E]"></span>
            {{ chartScope === 'madrasah' ? 'Pemasukan Madrasah' : 'Pemasukan Kelas' }}
          </span>
          <span v-if="chartScope === 'madrasah'" class="inline-flex items-center gap-1.5 text-[#57534E]">
            <span class="w-2.5 h-2.5 rounded-full bg-[#9F1239]"></span> Pengeluaran Madrasah
          </span>
        </div>

        <div class="w-full overflow-x-auto pb-1 flex-1 flex items-center" v-if="chartReady">
          <div class="min-w-[460px] w-full h-[250px] relative">
            <Line :data="activeChartData" :options="chartOptions" :key="'line-' + chartScope" />
          </div>
        </div>
        <div v-else class="h-[250px] rounded-[12px] bg-[#F7F4ED] animate-pulse"></div>
      </div>

      <!-- Kepatuhan Carousel (4 cols) -->
      <div class="lg:col-span-4 ui-panel-padded flex flex-col justify-between gap-4">
        <div class="flex items-center justify-between gap-2">
          <div>
            <h3 class="text-base font-bold text-[#1C1917]">Kepatuhan {{ currentMonth }}</h3>
            <p class="text-xs text-[#78716C] mt-0.5">Kelas yang Anda ampu</p>
          </div>
          <div v-if="klassesData.length > 1" class="flex items-center gap-1.5">
            <button
              type="button"
              @click="prevSlide"
              :disabled="currentSlide === 0"
              aria-label="Kelas sebelumnya"
              class="min-w-[36px] min-h-[36px] rounded-[9px] border border-transparent bg-[#F7F4ED] shadow-[var(--shadow-border)] hover:bg-[#FDFCF9] text-[#1C1917] flex items-center justify-center transition-[background-color,box-shadow,transform] duration-150 ease-out enabled:active:scale-[0.96] disabled:opacity-40 disabled:cursor-not-allowed"
            >
              <UiIcon name="chevron_left" :size="18" :weight="600" />
            </button>
            <button
              type="button"
              @click="nextSlide"
              :disabled="currentSlide === klassesData.length - 1"
              aria-label="Kelas berikutnya"
              class="min-w-[36px] min-h-[36px] rounded-[9px] border border-transparent bg-[#F7F4ED] shadow-[var(--shadow-border)] hover:bg-[#FDFCF9] text-[#1C1917] flex items-center justify-center transition-[background-color,box-shadow,transform] duration-150 ease-out enabled:active:scale-[0.96] disabled:opacity-40 disabled:cursor-not-allowed"
            >
              <UiIcon name="chevron_right" :size="18" :weight="600" />
            </button>
          </div>
        </div>

        <div v-if="klassesData.length > 0" class="flex-1 flex flex-col items-center justify-center gap-4 py-2">
          <span class="text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-full bg-[#ECE8DF] text-[#292524]">
            {{ klassesData[currentSlide].nama_kelas }}
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
        <div v-else class="p-6 text-center text-xs text-[#78716C] rounded-[12px] bg-[#F7F4ED]">
          Belum ada kelas yang ditugaskan ke akun Anda.
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
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

const currentSlide = ref(0);
const klassesData = ref([]);
const totalNunggak = ref(0);
const currentMonth = ref('');
const chartScope = ref('madrasah');
const chartReady = ref(false);

const stats = ref({
  pemasukan_all_time: 0,
  pemasukan_bulan_ini: 0,
  pengeluaran_bulan_ini: 0,
  saldo_all_time: 0,
  persen_pemasukan: 0,
  persen_pengeluaran: 0,
  chart: { labels: [], pemasukan: [], pengeluaran: [] },
  kelas_summary: { pemasukan_bulan_ini: 0, persen_pemasukan: 0, chart_pemasukan: [] }
});

const formatRupiah = (angka) => {
  return Number(angka || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};

const activeChartData = computed(() => {
  if (chartScope.value === 'kelas') {
    return {
      labels: stats.value.chart?.labels || [],
      datasets: [
        {
          label: 'Pemasukan Kelas',
          backgroundColor: 'rgba(168, 85, 30, 0.12)',
          borderColor: '#A8551E',
          borderWidth: 2.5,
          pointBackgroundColor: '#FDFCF9',
          pointBorderColor: '#A8551E',
          pointBorderWidth: 2,
          pointRadius: 4,
          fill: true,
          data: stats.value.kelas_summary?.chart_pemasukan || [],
          tension: 0.35
        }
      ]
    };
  }

  return {
    labels: stats.value.chart?.labels || [],
    datasets: [
      {
        label: 'Pemasukan Madrasah',
        backgroundColor: 'rgba(168, 85, 30, 0.12)',
        borderColor: '#A8551E',
        borderWidth: 2.5,
        pointBackgroundColor: '#FDFCF9',
        pointBorderColor: '#A8551E',
        pointBorderWidth: 2,
        pointRadius: 4,
        fill: true,
        data: stats.value.chart?.pemasukan || [],
        tension: 0.35
      },
      {
        label: 'Pengeluaran Madrasah',
        backgroundColor: 'rgba(159, 18, 57, 0.0)',
        borderColor: '#9F1239',
        borderWidth: 1.75,
        pointBackgroundColor: '#FDFCF9',
        pointBorderColor: '#9F1239',
        pointBorderWidth: 1.5,
        pointRadius: 3,
        fill: false,
        data: stats.value.chart?.pengeluaran || [],
        tension: 0.35
      }
    ]
  };
});

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

const nextSlide = () => {
  if (currentSlide.value < klassesData.value.length - 1) currentSlide.value++;
};

const prevSlide = () => {
  if (currentSlide.value > 0) currentSlide.value--;
};

onMounted(async () => {
  try {
    const [resKepatuhan, resStat] = await Promise.all([
      axios.get('/dashboard/kepatuhan'),
      axios.get('/dashboard/statistik')
    ]);
    klassesData.value = resKepatuhan.data.kepatuhan_per_kelas || [];
    totalNunggak.value = resKepatuhan.data.total_belum_bayar_bulan_ini || 0;
    currentMonth.value = resKepatuhan.data.bulan || '';

    stats.value = resStat.data;
    chartReady.value = true;
  } catch (e) {
    console.error('Gagal memuat dashboard guru', e);
    notify('Gagal memuat data dashboard.', 'error');
  }
});
</script>
