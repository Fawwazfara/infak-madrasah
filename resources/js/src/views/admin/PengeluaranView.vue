<template>
  <div class="ui-page flex flex-col gap-6">
    <!-- Header -->
    <div class="ui-stagger ui-stagger-1">
      <h2 class="text-2xl sm:text-[26px] font-bold text-[#1C1917] tracking-tight">Pengeluaran &amp; Kasbon Guru</h2>
      <p class="text-sm text-[#57534E] mt-1">
        Catat biaya operasional maupun kasbon gaji guru. Entri berkategori <strong>Kasbon Guru</strong> tampil lengkap di Admin/DKM dan otomatis disamarkan menjadi <em>Kasbon A, B, ...</em> di portal guru.
      </p>
    </div>

    <div class="ui-stagger ui-stagger-2 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
      <!-- Left Column: Form Tambah / Edit -->
      <section class="lg:col-span-5 ui-panel-padded lg:sticky lg:top-24 space-y-4">
        <div class="flex items-center justify-between gap-2 pb-3 border-b border-black/[0.06]">
          <h3 class="text-base font-bold text-[#1C1917]">
            {{ editingId ? 'Edit Catatan Pengeluaran' : 'Tambah Pengeluaran Baru' }}
          </h3>
          <button
            v-if="editingId"
            type="button"
            @click="resetForm"
            class="text-xs font-semibold text-[#78716C] hover:text-[#1C1917] hover:underline"
          >
            Batal Edit
          </button>
        </div>

        <form class="space-y-4" @submit.prevent="submitPengeluaran">
          <!-- Kategori Chips -->
          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-[#44403C]">Kategori</label>
            <div class="flex flex-wrap gap-1.5">
              <button
                v-for="kat in categories"
                :key="kat"
                type="button"
                @click="selectCategory(kat)"
                :class="[
                  'min-h-[34px] px-3 rounded-[9px] border text-xs font-semibold transition-[background-color,color,border-color,transform] duration-150 ease-out active:scale-[0.96]',
                  form.kategori === kat
                    ? 'bg-[#292524] text-[#FDFCF9] border-transparent shadow-[var(--shadow-btn-navy)]'
                    : 'bg-[#F7F4ED] text-[#57534E] border-[#E6E1D6] hover:bg-[#FDFCF9] hover:text-[#1C1917]'
                ]"
              >
                {{ kat }}
              </button>
            </div>
          </div>

          <!-- Helper pilih guru jika Kasbon Guru -->
          <div
            v-if="form.kategori === 'Kasbon Guru'"
            class="p-3.5 rounded-[12px] bg-[#FAF4E4] shadow-[inset_0_0_0_1px_oklch(0.62_0.12_75_/_0.26)] space-y-2.5"
          >
            <div class="flex items-start gap-2 text-xs text-[#6E4A0B]">
              <UiIcon name="info" :size="15" :weight="600" class="shrink-0 mt-0.5" />
              <span>Pilih nama guru (atau ketik langsung di Keperluan). Nama asli hanya muncul di Admin &amp; cetak DKM; di portal guru tampil <strong>Kasbon A, B, ...</strong></span>
            </div>
            <div v-if="teachers.length > 0" class="flex flex-wrap gap-1.5">
              <button
                v-for="t in teachers"
                :key="t.id"
                type="button"
                @click="applyTeacherKasbon(t.name)"
                class="px-2.5 py-1 rounded-[7px] bg-[#FDFCF9] border border-[#E6E1D6] text-[11px] font-semibold text-[#1C1917] hover:bg-[#F9EFE7] hover:border-[#A8551E]/40 transition-colors active:scale-[0.96]"
              >
                + {{ t.name }}
              </button>
            </div>
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-[#44403C]" for="keperluan">Keperluan / Keterangan</label>
            <input
              id="keperluan"
              v-model="form.keperluan"
              type="text"
              required
              :placeholder="form.kategori === 'Kasbon Guru' ? 'Contoh: Kasbon Ust. Syukron (potong gaji)' : 'Contoh: Beli Spidol & Penghapus Papan'"
              class="ui-input"
            >
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-[#44403C]" for="nominal">Nominal (Rp)</label>
              <input
                id="nominal"
                v-model.number="form.nominal"
                type="number"
                min="1"
                required
                placeholder="0"
                class="ui-input font-mono font-bold tabular-nums"
              >
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-[#44403C]" for="tanggal">Tanggal</label>
              <input
                id="tanggal"
                v-model="form.tanggal"
                type="date"
                required
                class="ui-input"
              >
            </div>
          </div>

          <button
            type="submit"
            :disabled="isSubmitting"
            class="ui-btn-primary ui-btn-icon-l w-full"
          >
            <UiIcon v-if="isSubmitting" name="progress_activity" :size="17" :weight="600" class="animate-spin" />
            <UiIcon v-else :name="editingId ? 'save' : 'add_task'" :size="17" :weight="600" />
            <span>{{ isSubmitting ? 'Menyimpan...' : (editingId ? 'Simpan Perubahan' : 'Catat Pengeluaran') }}</span>
          </button>
        </form>
      </section>

      <!-- Right Column: Filter & Riwayat -->
      <section class="lg:col-span-7 flex flex-col gap-4">
        <div class="flex overflow-x-auto gap-2 pb-1 no-scrollbar">
          <button
            v-for="f in filterOptions"
            :key="f"
            type="button"
            @click="activeMonth = f"
            :class="[
              'min-h-[36px] px-3.5 rounded-full text-xs font-semibold transition-[background-color,color,transform] duration-150 ease-out active:scale-[0.96] shrink-0 border',
              activeMonth === f
                ? 'bg-[#292524] text-[#FDFCF9] border-transparent shadow-[var(--shadow-btn-navy)]'
                : 'bg-[#FDFCF9] text-[#57534E] border-[#E6E1D6] hover:text-[#1C1917] hover:bg-[#F7F4ED]'
            ]"
          >
            {{ f }}
          </button>
        </div>

        <div class="ui-panel overflow-hidden">
          <div class="px-5 py-4 border-b border-black/[0.06] flex flex-wrap items-center justify-between gap-3">
            <div>
              <h3 class="text-base font-bold text-[#1C1917]">Riwayat Pengeluaran</h3>
              <p class="text-xs text-[#78716C]">{{ filteredRiwayat.length }} catatan pada filter {{ activeMonth }}</p>
            </div>
            <div class="px-3.5 py-2 rounded-[10px] bg-[#FDF2F4] text-right">
              <span class="block text-[11px] font-semibold text-[#9F1239]">Total {{ activeMonth }}</span>
              <span class="text-sm font-extrabold font-mono tabular-nums text-[#9F1239]">Rp {{ formatRupiah(totalPengeluaran) }}</span>
            </div>
          </div>

          <div v-if="filteredRiwayat.length === 0" class="p-12 text-center">
            <div class="w-11 h-11 mx-auto rounded-[11px] bg-[#ECE8DF] text-[#78716C] flex items-center justify-center mb-2.5">
              <UiIcon name="receipt_long" :size="20" :weight="500" />
            </div>
            <p class="text-sm font-bold text-[#1C1917]">Belum ada catatan pengeluaran</p>
            <p class="text-xs text-[#78716C] mt-1">Tambahkan catatan pengeluaran atau ubah filter bulan di atas.</p>
          </div>

          <div v-else class="divide-y divide-black/[0.05]">
            <div
              v-for="item in filteredRiwayat"
              :key="item.id"
              class="px-5 py-4 flex items-center gap-3.5 hover:bg-[#F7F4ED]/70 transition-colors duration-100"
            >
              <div class="w-11 h-12 rounded-[10px] bg-[#F7F4ED] shadow-[var(--shadow-border)] flex flex-col items-center justify-center shrink-0">
                <span class="text-xs font-extrabold font-mono tabular-nums text-[#1C1917] leading-none">{{ getDay(item.tanggal) }}</span>
                <span class="text-[10px] font-semibold uppercase text-[#78716C] mt-1 leading-none">{{ getMonthShort(item.tanggal) }}</span>
              </div>

              <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-1.5">
                  <h4 class="text-sm font-bold text-[#1C1917] truncate">{{ item.keterangan }}</h4>
                  <span
                    class="text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0"
                    :class="isKasbonItem(item) ? 'bg-[#FAF4E4] text-[#9A6B16]' : 'bg-[#ECE8DF] text-[#57534E]'"
                  >
                    {{ isKasbonItem(item) ? 'Kasbon Guru' : (item.kategori || 'Operasional') }}
                  </span>
                </div>
                <p class="text-sm font-extrabold font-mono tabular-nums text-[#A8551E] mt-1">
                  Rp {{ formatRupiah(item.jumlah) }}
                </p>
              </div>

              <div class="flex items-center gap-1.5 shrink-0">
                <button
                  type="button"
                  @click="startEdit(item)"
                  aria-label="Edit pengeluaran"
                  class="min-w-[36px] min-h-[36px] rounded-[9px] bg-[#F7F4ED] text-[#44403C] hover:bg-[#ECE8DF] hover:text-[#1C1917] flex items-center justify-center transition-[background-color,color,transform] duration-150 ease-out active:scale-[0.96]"
                >
                  <UiIcon name="edit" :size="16" :weight="600" />
                </button>
                <button
                  type="button"
                  @click="confirmDelete(item)"
                  aria-label="Hapus pengeluaran"
                  class="min-w-[36px] min-h-[36px] rounded-[9px] bg-[#FDF2F4] text-[#9F1239] hover:bg-[#FCE4E9] flex items-center justify-center transition-[background-color,transform] duration-150 ease-out active:scale-[0.96]"
                >
                  <UiIcon name="delete" :size="16" :weight="600" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="itemToDelete"
      class="fixed inset-0 z-[110] flex items-center justify-center bg-[#1C1917]/50 backdrop-blur-[2px] p-4 overscroll-contain"
      @click="itemToDelete = null"
    >
      <div class="ui-panel-elevated w-full max-w-sm p-6 text-center overscroll-contain" @click.stop>
        <div class="mx-auto w-14 h-14 bg-[#FDF2F4] shadow-[var(--shadow-btn-danger)] rounded-full flex items-center justify-center mb-4 text-[#9F1239]">
          <UiIcon name="warning" :size="24" :weight="600" />
        </div>
        <h3 class="text-lg font-bold text-[#1C1917]">Hapus Pengeluaran?</h3>
        <p class="text-sm text-[#57534E] mt-2 mb-6 leading-relaxed">
          Anda yakin ingin menghapus catatan <strong>{{ itemToDelete.keterangan }}</strong> senilai <strong>Rp {{ formatRupiah(itemToDelete.jumlah) }}</strong>?
        </p>
        <div class="flex gap-3">
          <button type="button" class="ui-btn-secondary flex-1" @click="itemToDelete = null">Batal</button>
          <button type="button" class="ui-btn-danger flex-1" @click="executeDelete">Ya, Hapus</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';
import { notify } from '@/utils/toast';

const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const categories = ['Operasional', 'Kegiatan', 'Gaji / Honor', 'Kasbon Guru', 'Lainnya'];
const filterOptions = ['Semua', ...months];

const activeMonth = ref(months[new Date().getMonth()]);
const isSubmitting = ref(false);
const editingId = ref(null);
const riwayat = ref([]);
const teachers = ref([]);
const itemToDelete = ref(null);

const form = reactive({
  kategori: 'Operasional',
  keperluan: '',
  nominal: '',
  tanggal: new Date().toISOString().split('T')[0],
});

const fetchPengeluaran = async () => {
  try {
    const res = await axios.get('/pengeluaran');
    riwayat.value = res.data || [];
  } catch (error) {
    console.error('Gagal mengambil data pengeluaran', error);
    notify('Gagal memuat riwayat pengeluaran.', 'error');
  }
};

const fetchTeachers = async () => {
  try {
    const res = await axios.get('/guru');
    teachers.value = res.data || [];
  } catch (error) {
    console.error('Gagal memuat daftar guru', error);
  }
};

onMounted(() => {
  fetchPengeluaran();
  fetchTeachers();
});

const isKasbonItem = (item) => {
  const kat = String(item.kategori || '').toLowerCase();
  const ket = String(item.keterangan || '').toLowerCase();
  return kat === 'kasbon guru' || ket.includes('kasbon');
};

const selectCategory = (kat) => {
  form.kategori = kat;
};

const applyTeacherKasbon = (teacherName) => {
  form.keperluan = `Kasbon ${teacherName}`;
};

const resetForm = () => {
  editingId.value = null;
  form.kategori = 'Operasional';
  form.keperluan = '';
  form.nominal = '';
  form.tanggal = new Date().toISOString().split('T')[0];
};

const startEdit = (item) => {
  editingId.value = item.id;
  form.kategori = isKasbonItem(item) ? 'Kasbon Guru' : (item.kategori || 'Operasional');
  form.keperluan = item.keterangan;
  form.nominal = Number(item.jumlah);
  form.tanggal = String(item.tanggal).split('T')[0];
};

const submitPengeluaran = async () => {
  isSubmitting.value = true;
  try {
    const payload = {
      keperluan: form.keperluan,
      nominal: form.nominal,
      tanggal: form.tanggal,
      kategori: form.kategori,
    };

    if (editingId.value) {
      await axios.put(`/pengeluaran/${editingId.value}`, payload);
      notify('Catatan pengeluaran berhasil diperbarui.', 'success');
    } else {
      await axios.post('/pengeluaran', payload);
      notify('Catatan pengeluaran berhasil disimpan.', 'success');
    }

    await fetchPengeluaran();
    resetForm();
  } catch (error) {
    console.error('Gagal menyimpan pengeluaran', error);
    notify(error.response?.data?.message || 'Gagal menyimpan catatan pengeluaran.', 'error');
  } finally {
    isSubmitting.value = false;
  }
};

const confirmDelete = (item) => {
  itemToDelete.value = item;
};

const executeDelete = async () => {
  if (!itemToDelete.value) return;
  try {
    await axios.delete(`/pengeluaran/${itemToDelete.value.id}`);
    notify('Catatan pengeluaran dihapus.', 'success');
    await fetchPengeluaran();
  } catch (error) {
    console.error('Gagal menghapus pengeluaran', error);
    notify('Gagal menghapus pengeluaran.', 'error');
  } finally {
    itemToDelete.value = null;
  }
};

const filteredRiwayat = computed(() => {
  if (activeMonth.value === 'Semua') return riwayat.value;
  return riwayat.value.filter(item => {
    const itemMonthIndex = new Date(item.tanggal).getMonth();
    return months[itemMonthIndex] === activeMonth.value;
  });
});

const totalPengeluaran = computed(() => {
  return filteredRiwayat.value.reduce((acc, curr) => acc + parseInt(curr.jumlah || 0, 10), 0);
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

const formatRupiah = (angka) => {
  return Number(angka || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};
</script>
