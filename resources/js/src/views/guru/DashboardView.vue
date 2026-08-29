<template>
  <main class="w-full max-w-6xl mx-auto px-container-margin py-lg pb-24 md:pb-lg flex flex-col gap-lg">
    
    <!-- User Welcome -->
    <div class="flex flex-col gap-1">
      <h2 class="font-headline-lg-mobile md:font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background">Dashboard Guru</h2>
      <p class="font-body-md text-on-surface-variant">Ringkasan infak kelas Anda.</p>
    </div>

    <!-- Alert / Notice (Guru only) -->
    <div class="bg-[#FFF8E1] rounded-[24px] shadow-sm p-md flex flex-col justify-between hover:shadow-md transition-shadow duration-300 min-h-[120px]">
       <div class="flex justify-between items-start mb-2">
        <span class="font-label-md text-label-md text-[#F57F17] font-bold">Perlu Perhatian</span>
        <div class="bg-[#FFF3E0] p-1.5 rounded-full text-[#E65100]">
          <span class="material-symbols-outlined text-[18px]">warning</span>
        </div>
      </div>
      <div class="flex flex-col gap-1 mt-auto">
        <h3 class="font-body-md text-body-md text-[#E65100]">Ada {{ totalNunggak }} Siswa di kelas Anda yang belum membayar infak bulan {{ currentMonth }}.</h3>
        <p class="font-label-sm text-[12px] text-[#F57F17]">Harap pastikan semua sudah terkumpul di akhir bulan.</p>
      </div>
    </div>

    <!-- Financial & Compliance Cards (Guru Version) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
      <!-- Pemasukan Kelas Card -->
      <div class="bg-primary/5 border border-primary/20 rounded-3xl p-lg flex flex-col justify-center gap-2 transition-transform hover:scale-[1.02] cursor-pointer">
        <div class="flex justify-between items-start">
          <span class="font-label-sm text-[12px] font-bold text-primary uppercase tracking-wider">Total Infak Kelas Anda (Bulan Ini)</span>
          <span class="material-symbols-outlined text-primary">account_balance_wallet</span>
        </div>
        <div class="mt-2">
          <span class="font-headline-lg text-[28px] md:text-[32px] font-bold text-primary">Rp {{ formatRupiah(stats.pemasukan_bulan_ini) }}</span>
        </div>
        <div class="flex items-center gap-1 mt-1 text-primary" :class="stats.persen_pemasukan >= 0 ? 'text-primary' : 'text-error'">
          <span class="material-symbols-outlined text-[16px]">{{ stats.persen_pemasukan >= 0 ? 'trending_up' : 'trending_down' }}</span>
          <span class="font-label-sm text-[11px] font-bold">{{ stats.persen_pemasukan >= 0 ? '+' : '' }}{{ stats.persen_pemasukan }}% dari bulan lalu</span>
        </div>
      </div>

      <!-- Kepatuhan Kelas Cards (Pie Chart) - Carousel with Arrows -->
      <div class="relative bg-surface-container-lowest border border-outline-variant/30 rounded-3xl p-md flex items-center justify-between transition-transform hover:scale-[1.02]" v-if="klassesData.length > 0">
        
        <!-- Left Arrow -->
        <button 
          @click="prevSlide"
          :disabled="currentSlide === 0"
          :class="[
            'w-10 h-10 rounded-full flex items-center justify-center shrink-0 z-10 transition-colors',
            currentSlide === 0 ? 'bg-surface-variant text-outline opacity-50 cursor-not-allowed' : 'bg-primary-container text-on-primary-container hover:bg-primary hover:text-white'
          ]"
        >
          <span class="material-symbols-outlined">chevron_left</span>
        </button>

        <!-- Slide Content (Centered) -->
        <div class="flex-1 flex flex-col sm:flex-row items-center justify-center gap-6 px-4">
          <!-- Chart -->
          <div class="w-[120px] h-[120px] relative shrink-0">
            <Doughnut :data="klassesData[currentSlide].pieData" :options="pieOptions" :key="'pie-'+currentSlide" />
            <div class="absolute inset-0 flex flex-col items-center justify-center">
              <span class="font-title-md font-bold text-primary">{{ klassesData[currentSlide].persentase }}%</span>
            </div>
          </div>
          <!-- Legend -->
          <div class="flex flex-col gap-3">
            <span class="font-label-sm text-[12px] font-bold text-on-surface-variant uppercase tracking-wider text-center sm:text-left">Kepatuhan {{ klassesData[currentSlide].nama_kelas }}</span>
            <div class="flex flex-col gap-2">
              <div class="flex items-center gap-2 justify-center sm:justify-start">
                <div class="w-3 h-3 rounded-full bg-[#1B5E20]"></div>
                <span class="font-body-sm text-on-surface">{{ klassesData[currentSlide].lunas }} Lunas</span>
              </div>
              <div class="flex items-center gap-2 justify-center sm:justify-start">
                <div class="w-3 h-3 rounded-full bg-[#E65100]"></div>
                <span class="font-body-sm text-on-surface">{{ klassesData[currentSlide].nunggak }} Menunggak</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Arrow -->
        <button 
          @click="nextSlide"
          :disabled="currentSlide === klassesData.length - 1"
          :class="[
            'w-10 h-10 rounded-full flex items-center justify-center shrink-0 z-10 transition-colors',
            currentSlide === klassesData.length - 1 ? 'bg-surface-variant text-outline opacity-50 cursor-not-allowed' : 'bg-primary-container text-on-primary-container hover:bg-primary hover:text-white'
          ]"
        >
          <span class="material-symbols-outlined">chevron_right</span>
        </button>
      </div>
    </div>

    <!-- Row 2: Chart -->
    <section class="bg-surface-container-lowest rounded-[24px] shadow-sm border border-outline-variant/30 p-md flex flex-col gap-4">
      <h3 class="font-title-md text-title-md font-semibold text-on-surface">Grafik Pemasukan Kelas (6 Bulan Terakhir)</h3>
      
      <!-- Scrollable Chart Container -->
      <div class="w-full overflow-x-auto pb-2" v-if="chartReady">
        <div class="min-w-[600px] h-[250px] relative">
          <Line :data="chartData" :options="chartOptions" />
        </div>
      </div>
    </section>
  </main>
</template>

<script setup>
import { ref } from 'vue';
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

// --- PIE / DOUGHNUT CHART DATA ---
const currentSlide = ref(0);
const klassesData = ref([]);
const totalNunggak = ref(0);
const currentMonth = ref('');

const stats = ref({
  pemasukan_bulan_ini: 0,
  persen_pemasukan: 0
});

const chartReady = ref(false);

const formatRupiah = (angka) => {
  return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};

import axios from 'axios';
import { onMounted } from 'vue';

onMounted(async () => {
  try {
    const resKepatuhan = await axios.get('/dashboard/kepatuhan');
    klassesData.value = resKepatuhan.data.kepatuhan_per_kelas;
    totalNunggak.value = resKepatuhan.data.total_belum_bayar_bulan_ini;
    currentMonth.value = resKepatuhan.data.bulan;

    const resStat = await axios.get('/dashboard/statistik');
    stats.value = resStat.data;
    
    chartData.value.labels = resStat.data.chart.labels;
    chartData.value.datasets[0].data = resStat.data.chart.pemasukan;
    
    chartReady.value = true;
  } catch (e) {
    console.error("Gagal memuat dashboard data", e);
  }
});

const pieOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  cutout: '70%', // Makes it a doughnut
  plugins: {
    legend: {
      display: false // We use our custom HTML legend
    },
    tooltip: {
      backgroundColor: '#1B5E20',
      padding: 10,
      cornerRadius: 8,
      callbacks: {
        label: function(context) {
          return ' ' + context.label + ': ' + context.parsed + ' Siswa';
        }
      }
    }
  }
});

// --- LINE CHART DATA ---
const chartData = ref({
  labels: [],
  datasets: [
    {
      label: 'Pemasukan Kelas',
      backgroundColor: 'rgba(27, 94, 32, 0.1)', // #1B5E20 with opacity
      borderColor: '#1B5E20',
      borderWidth: 2,
      pointBackgroundColor: '#fff',
      pointBorderColor: '#1B5E20',
      pointBorderWidth: 2,
      pointRadius: 4,
      fill: true,
      data: [],
      tension: 0.4 // Makes the line curved
    }
  ]
});

const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false
    },
    tooltip: {
      backgroundColor: '#1B5E20',
      padding: 10,
      cornerRadius: 8,
      displayColors: false,
      callbacks: {
        label: function(context) {
          let label = context.dataset.label || '';
          if (label) {
            label += ': ';
          }
          if (context.parsed.y !== null) {
            label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
          }
          return label;
        }
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: {
        color: '#E0E0E0',
        drawBorder: false,
      },
      ticks: {
        display: false // hide y-axis labels as in mockup
      }
    },
    x: {
      grid: {
        display: false,
        drawBorder: false,
      }
    }
  }
});

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
</script>
