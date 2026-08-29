<template>
  <main class="w-full max-w-7xl mx-auto px-container-margin pt-lg pb-xl flex flex-col gap-lg overflow-y-auto">
    <!-- Action Banner -->
    <section class="bg-primary-container rounded-[24px] p-md flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <span class="material-symbols-outlined text-primary text-3xl">notifications_active</span>
        <div>
          <h3 class="font-title-md font-bold text-on-primary-container">Pengingat Tunggakan</h3>
          <p class="font-body-sm text-on-primary-container/80">Kirim notifikasi ke semua guru untuk mengingatkan siswa yang menunggak.</p>
        </div>
      </div>
      <button @click="sendTunggakanReminder" :disabled="isSending" class="bg-primary text-white font-label-md px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-primary/90 transition-colors disabled:opacity-50 shrink-0">
        <span class="material-symbols-outlined text-[18px]" v-if="!isSending">send</span>
        <span class="material-symbols-outlined text-[18px] animate-spin" v-else>sync</span>
        {{ isSending ? 'Mengirim...' : 'Kirim Sekarang' }}
      </button>
    </section>

    <!-- Row 1: KPI Cards -->
    <section class="grid grid-cols-2 gap-md md:grid-cols-3">
      <!-- Card 1: Total Kas -->
      <div class="bg-surface-container-lowest rounded-[24px] shadow-sm p-md flex flex-col justify-between hover:shadow-md transition-shadow duration-300 min-h-[120px]">
        <div class="flex justify-between items-start mb-2">
          <span class="font-label-md text-label-md text-on-surface-variant">Total Kas</span>
          <div class="bg-primary-container/10 p-1.5 rounded-full text-primary">
            <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>
          </div>
        </div>
        <div class="flex flex-col gap-1">
          <h3 class="font-headline-md text-headline-md font-bold text-on-surface">Rp {{ formatRupiah(stats.pemasukan_all_time) }}</h3>
          <div class="flex items-center gap-1 font-label-sm text-label-sm text-on-surface-variant">
            <span class="material-symbols-outlined text-[14px]" :class="stats.persen_pemasukan >= 0 ? 'text-primary' : 'text-error'">
              {{ stats.persen_pemasukan >= 0 ? 'trending_up' : 'trending_down' }}
            </span>
            <span class="font-medium" :class="stats.persen_pemasukan >= 0 ? 'text-primary' : 'text-error'">
              {{ stats.persen_pemasukan >= 0 ? '+' : '' }}{{ stats.persen_pemasukan }}%
            </span> vs last month (Bulan ini: Rp {{ formatRupiah(stats.pemasukan_bulan_ini) }})
          </div>
        </div>
      </div>

      <!-- Card 2: Pengeluaran -->
      <div class="bg-surface-container-lowest rounded-[24px] shadow-sm p-md flex flex-col justify-between hover:shadow-md transition-shadow duration-300 min-h-[120px]">
        <div class="flex justify-between items-start mb-2">
          <span class="font-label-md text-label-md text-on-surface-variant">Pengeluaran Bulan Ini</span>
          <div class="bg-error-container/10 p-1.5 rounded-full text-error">
            <span class="material-symbols-outlined text-[18px]">money_off</span>
          </div>
        </div>
        <div class="flex flex-col gap-1">
          <h3 class="font-headline-md text-headline-md font-bold text-on-surface">Rp {{ formatRupiah(stats.pengeluaran_bulan_ini) }}</h3>
          <div class="flex items-center gap-1 font-label-sm text-label-sm text-on-surface-variant">
            <span class="material-symbols-outlined text-[14px]" :class="stats.persen_pengeluaran >= 0 ? 'text-error' : 'text-primary'">
              {{ stats.persen_pengeluaran >= 0 ? 'trending_up' : 'trending_down' }}
            </span>
            <span class="font-medium" :class="stats.persen_pengeluaran >= 0 ? 'text-error' : 'text-primary'">
              {{ stats.persen_pengeluaran >= 0 ? '+' : '' }}{{ stats.persen_pengeluaran }}%
            </span> vs last month
          </div>
        </div>
      </div>

      <!-- Card 3: Saldo -->
      <div class="bg-surface-container-lowest rounded-[24px] shadow-sm p-md flex flex-col justify-between hover:shadow-md transition-shadow duration-300 min-h-[120px]">
         <div class="flex justify-between items-start mb-2">
          <span class="font-label-md text-label-md text-on-surface-variant">Saldo Akhir Saat Ini</span>
          <div class="bg-primary-container/10 p-1.5 rounded-full text-primary">
            <span class="material-symbols-outlined text-[18px]">savings</span>
          </div>
        </div>
        <div class="flex flex-col gap-1">
          <h3 class="font-headline-md text-headline-md font-bold text-on-surface">Rp {{ formatRupiah(stats.saldo_all_time) }}</h3>
        </div>
      </div>
    </section>

    <!-- Kepatuhan Kelas Cards (Pie Chart) - Carousel with Arrows -->
    <section v-if="klassesData.length > 0" class="relative bg-surface-container-lowest border border-outline-variant/30 rounded-[24px] p-md flex items-center justify-between transition-transform hover:shadow-md">
      
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
          <span class="font-label-sm text-[12px] font-bold text-on-surface-variant uppercase tracking-wider text-center sm:text-left">Kepatuhan {{ klassesData[currentSlide].nama_kelas }} (Bulan {{ currentMonth }})</span>
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
    </section>

    <!-- Row 2: Chart -->
    <section class="bg-surface-container-lowest rounded-[24px] shadow-sm border border-outline-variant/30 p-md flex flex-col gap-4">
      <h3 class="font-title-md text-title-md font-semibold text-on-surface">Grafik Pemasukan (6 Bulan Terakhir)</h3>
      
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
import { ref, onMounted } from 'vue';
import axios from 'axios';
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

const sendTunggakanReminder = async () => {
  if (confirm('Apakah Anda yakin ingin mengirim notifikasi pengingat tunggakan ke semua wali kelas sekarang?')) {
    isSending.value = true;
    try {
      const res = await axios.post('/push-send-reminders');
      alert(res.data.message || 'Notifikasi berhasil dikirim!');
    } catch (err) {
      console.error(err);
      alert('Gagal mengirim notifikasi.');
    } finally {
      isSending.value = false;
    }
  }
};

const formatRupiah = (angka) => {
  return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};

const chartData = ref({
  labels: [],
  datasets: [
    {
      label: 'Pemasukan',
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
    },
    {
      label: 'Pengeluaran',
      backgroundColor: 'rgba(183, 28, 28, 0.0)', // #B71C1C transparent
      borderColor: '#B71C1C',
      borderWidth: 1, // Thinner line as requested
      pointBackgroundColor: '#fff',
      pointBorderColor: '#B71C1C',
      pointBorderWidth: 1,
      pointRadius: 3,
      fill: false,
      data: [],
      tension: 0.4
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
  cutout: '70%',
  plugins: {
    legend: {
      display: false
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

onMounted(async () => {
  try {
    const resKepatuhan = await axios.get('/dashboard/kepatuhan');
    klassesData.value = resKepatuhan.data.kepatuhan_per_kelas;
    currentMonth.value = resKepatuhan.data.bulan;

    const res = await axios.get('/dashboard/statistik');
    stats.value = res.data;
    
    chartData.value.labels = res.data.chart.labels;
    chartData.value.datasets[0].data = res.data.chart.pemasukan;
    chartData.value.datasets[1].data = res.data.chart.pengeluaran;
    
    chartReady.value = true;
  } catch (err) {
    console.error("Gagal mengambil statistik", err);
  }
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
      displayColors: true,
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
</script>
