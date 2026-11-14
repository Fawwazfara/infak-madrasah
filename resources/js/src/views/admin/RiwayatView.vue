<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 ui-stagger ui-stagger-1">
      <div>
        <span class="inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-[#A8551E] mb-1">
          <UiIcon name="history" :size="14" />
          Audit Trail Sistem
        </span>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-[#1C1917] tracking-tight">Riwayat Aktivitas</h2>
        <p class="text-sm text-[#78716C] mt-1">
          Jejak pencatatan infak, pengeluaran dana, dan aktivitas pengelolaan madrasah.
        </p>
      </div>

      <button
        type="button"
        @click="showClearModal = true"
        :disabled="logs.length === 0 || isClearing"
        class="ui-btn-danger self-start sm:self-auto"
      >
        <UiIcon name="delete_sweep" :size="17" />
        <span>Bersihkan Log</span>
      </button>
    </div>

    <!-- Search & Filter Bar -->
    <div class="ui-panel p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 ui-stagger ui-stagger-2">
      <div class="relative flex-1 sm:max-w-sm">
        <UiIcon name="search" :size="16" class="text-[#78716C] absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
        <input
          v-model="searchQuery"
          @input="handleSearch"
          type="text"
          placeholder="Cari nama siswa, nominal, atau aktivitas..."
          class="ui-input !min-h-[40px] !py-1.5 pl-10 text-sm"
        />
      </div>

      <div class="ui-segmented overflow-x-auto no-scrollbar max-w-full">
        <button
          v-for="tab in filterTabs"
          :key="tab.value"
          type="button"
          @click="typeFilter = tab.value"
          class="ui-segmented-item whitespace-nowrap text-xs"
          :class="typeFilter === tab.value ? 'ui-segmented-item-active' : 'ui-segmented-item-idle'"
        >
          {{ tab.label }}
        </button>
      </div>
    </div>

    <!-- Log List -->
    <div class="ui-panel overflow-hidden ui-stagger ui-stagger-3">
      <div v-if="isLoading && logs.length === 0" class="divide-y divide-[#ECE8DF]">
        <div v-for="i in 5" :key="i" class="p-4 flex items-center gap-4 animate-pulse">
          <div class="w-10 h-10 rounded-xl bg-[#ECE8DF] shrink-0"></div>
          <div class="flex-1 space-y-2">
            <div class="h-3.5 bg-[#ECE8DF] rounded w-1/3"></div>
            <div class="h-3 bg-[#F7F4ED] rounded w-2/3"></div>
          </div>
          <div class="h-4 bg-[#ECE8DF] rounded w-20"></div>
        </div>
      </div>

      <div v-else-if="filteredLogs.length === 0" class="p-12 text-center">
        <div class="w-12 h-12 rounded-2xl bg-[#ECE8DF] text-[#78716C] flex items-center justify-center mx-auto mb-3">
          <UiIcon :name="searchQuery || typeFilter !== 'all' ? 'search_off' : 'history'" :size="22" />
        </div>
        <p class="text-sm font-bold text-[#1C1917]">Tidak ada riwayat aktivitas</p>
        <p class="text-xs text-[#78716C] mt-1">
          {{ searchQuery || typeFilter !== 'all' ? 'Coba ubah filter atau kata kunci pencarian.' : 'Belum ada aktivitas pencatatan yang tersimpan.' }}
        </p>
      </div>

      <div v-else class="divide-y divide-[#ECE8DF]">
        <div
          v-for="log in filteredLogs"
          :key="log.id"
          class="p-4 sm:px-6 flex items-start sm:items-center justify-between gap-4 hover:bg-[#F7F4ED]/60 transition-colors"
        >
          <div class="flex items-start gap-3.5 min-w-0">
            <div
              class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 mt-0.5 sm:mt-0"
              :class="badgeStyle(log.type)"
            >
              <UiIcon :name="badgeIcon(log.type)" :size="18" />
            </div>

            <div class="min-w-0">
              <div class="flex flex-wrap items-center gap-2">
                <h4 class="text-sm font-bold text-[#1C1917] leading-snug">{{ log.title }}</h4>
                <span
                  class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider"
                  :class="badgeStyle(log.type)"
                >
                  {{ typeLabel(log.type) }}
                </span>
              </div>
              <p class="text-xs text-[#57534E] mt-1 leading-relaxed">{{ log.description }}</p>
              <div class="flex flex-wrap items-center gap-3 mt-1.5 text-[11px] text-[#78716C]">
                <span class="inline-flex items-center gap-1">
                  <UiIcon name="person" :size="12" />
                  {{ log.actor }}
                </span>
                <span class="tabular-nums">{{ log.timestamp }}</span>
              </div>
            </div>
          </div>

          <div v-if="log.amount" class="text-right shrink-0">
            <span
              class="text-sm font-extrabold font-mono tabular-nums"
              :class="log.type === 'expense' ? 'text-[#9F1239]' : 'text-[#A8551E]'"
            >
              {{ log.type === 'expense' ? '-' : '+' }}Rp {{ Number(log.amount).toLocaleString('id-ID') }}
            </span>
          </div>
        </div>
      </div>

      <div v-if="hasMore" class="p-4 bg-[#F7F4ED] border-t border-[#E7E2D8] text-center">
        <button
          type="button"
          @click="loadMore"
          :disabled="isLoadingMore"
          class="ui-btn-secondary !min-h-[38px] !py-1.5 text-xs"
        >
          <UiIcon v-if="isLoadingMore" name="progress_activity" :size="15" class="animate-spin" />
          <span>{{ isLoadingMore ? 'Memuat...' : 'Muat Lebih Banyak' }}</span>
        </button>
      </div>
    </div>

    <!-- Modal Konfirmasi Bersihkan Log -->
    <div
      v-if="showClearModal"
      class="fixed inset-0 z-50 bg-[#1C1917]/50 backdrop-blur-[2px] flex items-center justify-center p-4"
      @click.self="showClearModal = false"
    >
      <div class="ui-panel-elevated w-full max-w-md p-6">
        <div class="flex items-start gap-3.5">
          <div class="w-11 h-11 rounded-xl bg-[#FDF2F4] text-[#9F1239] flex items-center justify-center shrink-0">
            <UiIcon name="warning" :size="22" />
          </div>
          <div>
            <h3 class="text-lg font-bold text-[#1C1917]">Bersihkan Semua Log?</h3>
            <p class="text-sm text-[#57534E] mt-1 leading-relaxed">
              Seluruh catatan riwayat aktivitas audit akan dihapus permanen. Data transaksi infak dan pengeluaran tidak akan berubah.
            </p>
          </div>
        </div>

        <div class="flex justify-end gap-2.5 mt-6">
          <button type="button" @click="showClearModal = false" class="ui-btn-secondary">
            Batal
          </button>
          <button
            type="button"
            :disabled="isClearing"
            @click="confirmClearLogs"
            class="ui-btn-base bg-[#9F1239] text-white hover:bg-[#881337]"
          >
            <UiIcon v-if="isClearing" name="progress_activity" :size="16" class="animate-spin" />
            <span>{{ isClearing ? 'Membersihkan...' : 'Ya, Bersihkan Log' }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { notify } from '../../utils/toast';

const logs = ref([]);
const searchQuery = ref('');
const typeFilter = ref('all');
const isLoading = ref(true);
const isLoadingMore = ref(false);
const isClearing = ref(false);
const showClearModal = ref(false);

const currentPage = ref(1);
const hasMore = ref(false);
let searchTimeout = null;

const filterTabs = [
  { label: 'Semua', value: 'all' },
  { label: 'Pemasukan', value: 'income' },
  { label: 'Pengeluaran', value: 'expense' },
  { label: 'Sistem', value: 'system' },
];

const filteredLogs = computed(() => {
  if (typeFilter.value === 'all') return logs.value;
  return logs.value.filter((l) => l.type === typeFilter.value);
});

const badgeStyle = (type) => {
  if (type === 'income') return 'bg-[#F9EFE7] text-[#A8551E]';
  if (type === 'expense') return 'bg-[#FDF2F4] text-[#9F1239]';
  return 'bg-[#ECE8DF] text-[#292524]';
};

const badgeIcon = (type) => {
  if (type === 'income') return 'account_balance_wallet';
  if (type === 'expense') return 'money_off';
  return 'person_add';
};

const typeLabel = (type) => {
  if (type === 'income') return 'Pemasukan';
  if (type === 'expense') return 'Pengeluaran';
  return 'Sistem';
};

const fetchLogs = async (page = 1, append = false) => {
  if (!append) isLoading.value = true;
  else isLoadingMore.value = true;

  try {
    const params = { per_page: 30, page };
    if (searchQuery.value.trim()) params.search = searchQuery.value.trim();

    const res = await axios.get('/log-aktivitas', { params });
    const data = res.data;

    if (Array.isArray(data)) {
      logs.value = append ? [...logs.value, ...data] : data;
      hasMore.value = false;
    } else if (data && data.data) {
      logs.value = append ? [...logs.value, ...data.data] : data.data;
      currentPage.value = data.current_page;
      hasMore.value = data.current_page < data.last_page;
    }
  } catch (err) {
    console.error('Gagal load log aktivitas', err);
    notify('Gagal memuat riwayat aktivitas.', 'error');
  } finally {
    isLoading.value = false;
    isLoadingMore.value = false;
  }
};

const handleSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    currentPage.value = 1;
    fetchLogs(1, false);
  }, 350);
};

const loadMore = () => {
  if (hasMore.value && !isLoadingMore.value) {
    fetchLogs(currentPage.value + 1, true);
  }
};

const confirmClearLogs = async () => {
  isClearing.value = true;
  try {
    await axios.post('/log-aktivitas/clear');
    logs.value = [];
    hasMore.value = false;
    showClearModal.value = false;
    notify('Semua riwayat aktivitas berhasil dibersihkan.', 'success');
  } catch (err) {
    console.error('Gagal clear logs', err);
    notify('Gagal membersihkan riwayat aktivitas.', 'error');
  } finally {
    isClearing.value = false;
  }
};

onMounted(() => {
  fetchLogs();
});
</script>
