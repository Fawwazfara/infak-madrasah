<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 ui-stagger ui-stagger-1">
      <div>
        <span class="inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-[#A8551E] mb-1">
          <UiIcon name="groups" :size="14" />
          Hak Akses &amp; Pengajar
        </span>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-[#1C1917] tracking-tight">Manajemen Akun Guru</h2>
        <p class="text-sm text-[#78716C] mt-1">
          Buat akun pengajar, atur penugasan wali kelas, dan kelola kata sandi akses portal guru.
        </p>
      </div>
      <div class="inline-flex items-center gap-2 self-start sm:self-auto px-3.5 py-2 rounded-xl bg-[#FDFCF9] text-xs font-bold text-[#292524]" style="box-shadow: var(--shadow-border);">
        <span class="w-2 h-2 rounded-full bg-[#A8551E]"></span>
        <span class="tabular-nums">{{ teachers.length }}</span>
        <span class="text-[#78716C] font-medium">Guru Terdaftar</span>
      </div>
    </div>

    <!-- Main 12-Col Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
      <!-- Left Sticky Form: Buat Akun Guru -->
      <section class="lg:col-span-5 ui-panel-padded lg:sticky lg:top-6 ui-stagger ui-stagger-2">
        <div class="flex items-center gap-3 mb-5 pb-4 border-b border-[#E7E2D8]">
          <div class="w-10 h-10 rounded-xl bg-[#F9EFE7] text-[#A8551E] flex items-center justify-center shrink-0">
            <UiIcon name="person_add" :size="20" />
          </div>
          <div>
            <h3 class="text-base font-bold text-[#1C1917] leading-tight">Buat Akun Guru Baru</h3>
            <p class="text-xs text-[#78716C] mt-0.5">Tetapkan satu atau beberapa kelas binaan</p>
          </div>
        </div>

        <form @submit.prevent="createTeacher" class="space-y-4">
          <div>
            <label for="guru-name" class="block text-xs font-semibold text-[#57534E] uppercase tracking-wider mb-1.5">
              Nama Lengkap / Ustadz
            </label>
            <input
              id="guru-name"
              v-model="form.fullName"
              type="text"
              placeholder="Contoh: Ust. Ahmad Fauzi"
              required
              class="ui-input"
            />
          </div>

          <div>
            <label for="guru-email" class="block text-xs font-semibold text-[#57534E] uppercase tracking-wider mb-1.5">
              Email Login
            </label>
            <input
              id="guru-email"
              v-model="form.email"
              type="email"
              placeholder="nama@madrasah.sch.id"
              required
              class="ui-input"
            />
          </div>

          <div>
            <label for="guru-password" class="block text-xs font-semibold text-[#57534E] uppercase tracking-wider mb-1.5">
              Password Sementara
            </label>
            <div class="relative">
              <input
                id="guru-password"
                v-model="form.password"
                :type="showCreatePassword ? 'text' : 'password'"
                placeholder="Minimal 6 karakter"
                minlength="6"
                required
                class="ui-input pr-11"
              />
              <button
                type="button"
                @click="showCreatePassword = !showCreatePassword"
                class="absolute right-1.5 top-1/2 -translate-y-1/2 w-9 h-9 rounded-lg flex items-center justify-center text-[#78716C] hover:text-[#1C1917] hover:bg-[#ECE8DF]/70 transition-colors"
                :aria-label="showCreatePassword ? 'Sembunyikan password' : 'Lihat password'"
              >
                <UiIcon :name="showCreatePassword ? 'visibility_off' : 'visibility'" :size="17" />
              </button>
            </div>
          </div>

          <div>
            <div class="flex items-center justify-between mb-2">
              <label class="text-xs font-semibold text-[#57534E] uppercase tracking-wider">
                Penugasan Wali Kelas
              </label>
              <span class="text-[11px] font-semibold text-[#A8551E] tabular-nums">
                {{ form.kelas_ids.length }} dipilih
              </span>
            </div>

            <div class="p-3 rounded-xl bg-[#F7F4ED] border border-[#E7E2D8] max-h-48 overflow-y-auto space-y-1.5">
              <div v-if="kelasList.length === 0" class="py-4 text-center text-xs text-[#78716C]">
                Memuat daftar kelas...
              </div>
              <label
                v-for="k in kelasList"
                :key="k.id"
                class="flex items-center justify-between gap-3 px-3 py-2 rounded-lg cursor-pointer transition-colors"
                :class="form.kelas_ids.includes(k.id) ? 'bg-[#FDFCF9] text-[#1C1917]' : 'hover:bg-[#FDFCF9]/70 text-[#57534E]'"
                :style="form.kelas_ids.includes(k.id) ? 'box-shadow: var(--shadow-border)' : ''"
              >
                <div class="flex items-center gap-2.5 min-w-0">
                  <input
                    type="checkbox"
                    :value="k.id"
                    v-model="form.kelas_ids"
                    class="w-4 h-4 rounded border-[#D6D0C4] text-[#A8551E] focus:ring-[#A8551E]"
                  />
                  <span class="text-sm font-semibold truncate">{{ k.nama_kelas }}</span>
                </div>
                <UiIcon
                  v-if="form.kelas_ids.includes(k.id)"
                  name="done"
                  :size="15"
                  class="text-[#A8551E]"
                />
              </label>
            </div>
          </div>

          <button
            type="submit"
            :disabled="isSubmitting"
            class="w-full ui-btn-primary mt-2"
          >
            <UiIcon v-if="isSubmitting" name="progress_activity" :size="18" class="animate-spin" />
            <UiIcon v-else name="person_add" :size="18" />
            <span>{{ isSubmitting ? 'Menyimpan...' : 'Daftarkan Akun Guru' }}</span>
          </button>
        </form>
      </section>

      <!-- Right Panel: Daftar Guru Aktif -->
      <section class="lg:col-span-7 space-y-4 ui-stagger ui-stagger-3">
        <div class="ui-panel p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div class="flex items-center gap-2.5">
            <UiIcon name="school" :size="18" class="text-[#A8551E]" />
            <h3 class="text-base font-bold text-[#1C1917]">Daftar Guru Aktif</h3>
          </div>
          <div class="relative w-full sm:w-64">
            <UiIcon name="search" :size="16" class="text-[#78716C] absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari nama atau kelas..."
              class="ui-input !min-h-[38px] !py-1.5 pl-9 text-xs"
            />
          </div>
        </div>

        <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div v-for="i in 4" :key="i" class="ui-panel p-5 animate-pulse space-y-3">
            <div class="flex items-center gap-3">
              <div class="w-11 h-11 rounded-xl bg-[#ECE8DF]"></div>
              <div class="space-y-2 flex-1">
                <div class="h-3.5 bg-[#ECE8DF] rounded w-2/3"></div>
                <div class="h-3 bg-[#ECE8DF] rounded w-1/2"></div>
              </div>
            </div>
            <div class="h-8 bg-[#F7F4ED] rounded-lg"></div>
          </div>
        </div>

        <div v-else-if="filteredTeachers.length === 0" class="ui-panel p-10 text-center">
          <div class="w-12 h-12 rounded-2xl bg-[#ECE8DF] text-[#78716C] flex items-center justify-center mx-auto mb-3">
            <UiIcon :name="searchQuery ? 'search_off' : 'group_off'" :size="22" />
          </div>
          <p class="text-sm font-bold text-[#1C1917]">
            {{ searchQuery ? 'Tidak ada guru yang cocok' : 'Belum ada akun guru terdaftar' }}
          </p>
          <p class="text-xs text-[#78716C] mt-1">
            {{ searchQuery ? 'Coba kata kunci nama, email, atau kelas lain.' : 'Gunakan formulir di samping untuk menambahkan akun wali kelas.' }}
          </p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <article
            v-for="teacher in filteredTeachers"
            :key="teacher.id"
            class="ui-panel ui-panel-interactive p-5 flex flex-col justify-between gap-4"
          >
            <div>
              <div class="flex items-start gap-3">
                <div class="w-11 h-11 rounded-xl bg-[#292524] text-[#FDFCF9] font-bold text-sm flex items-center justify-center shrink-0">
                  {{ getInitials(teacher.name) }}
                </div>
                <div class="min-w-0 flex-1">
                  <h4 class="font-bold text-[#1C1917] text-base leading-snug truncate" :title="teacher.name">
                    {{ teacher.name }}
                  </h4>
                  <p class="text-xs text-[#78716C] truncate mt-0.5" :title="teacher.email">
                    {{ teacher.email }}
                  </p>
                </div>
              </div>

              <div class="mt-4 pt-3.5 border-t border-[#ECE8DF]">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-[#78716C] mb-2">Kelas Diampu</p>
                <div v-if="teacher.kelas && teacher.kelas.length > 0" class="flex flex-wrap gap-1.5">
                  <span
                    v-for="(k, idx) in teacher.kelas"
                    :key="idx"
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-[#F9EFE7] text-[#A8551E]"
                  >
                    <UiIcon name="class" :size="12" />
                    {{ k }}
                  </span>
                </div>
                <span v-else class="text-xs text-[#A8A29E] italic">Belum ditugaskan ke kelas</span>
              </div>
            </div>

            <div class="flex items-center gap-2 pt-1">
              <button
                type="button"
                @click="openResetModal(teacher)"
                class="flex-1 ui-btn-secondary !min-h-[38px] !py-1.5 !px-3 text-xs"
              >
                <UiIcon name="key" :size="14" />
                <span>Reset Sandi</span>
              </button>
              <button
                type="button"
                @click="openDeleteModal(teacher)"
                class="ui-btn-danger !min-h-[38px] !w-[38px] !p-0 shrink-0"
                :title="`Hapus akun ${teacher.name}`"
                :aria-label="`Hapus akun ${teacher.name}`"
              >
                <UiIcon name="delete" :size="15" />
              </button>
            </div>
          </article>
        </div>
      </section>
    </div>

    <!-- Modal Reset Password -->
    <div
      v-if="showResetModal"
      class="fixed inset-0 z-50 bg-[#1C1917]/50 backdrop-blur-[2px] flex items-center justify-center p-4"
      @click.self="showResetModal = false"
    >
      <div class="ui-panel-elevated w-full max-w-md p-6">
        <div class="flex items-start justify-between gap-3 mb-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#F9EFE7] text-[#A8551E] flex items-center justify-center shrink-0">
              <UiIcon name="lock_reset" :size="20" />
            </div>
            <div>
              <h3 class="text-lg font-bold text-[#1C1917]">Reset Password Guru</h3>
              <p class="text-xs text-[#78716C] mt-0.5">
                Akun: <span class="font-semibold text-[#1C1917]">{{ selectedTeacher?.name }}</span>
              </p>
            </div>
          </div>
          <button
            type="button"
            @click="showResetModal = false"
            class="w-9 h-9 rounded-lg flex items-center justify-center text-[#78716C] hover:text-[#1C1917] hover:bg-[#ECE8DF]"
            aria-label="Tutup modal"
          >
            <UiIcon name="close" :size="18" />
          </button>
        </div>

        <form @submit.prevent="submitResetPassword" class="space-y-4">
          <div>
            <label for="reset-new-pw" class="block text-xs font-semibold text-[#57534E] uppercase tracking-wider mb-1.5">
              Password Baru
            </label>
            <div class="relative">
              <input
                id="reset-new-pw"
                v-model="newPassword"
                :type="showResetPassword ? 'text' : 'password'"
                placeholder="Minimal 6 karakter"
                required
                minlength="6"
                class="ui-input pr-11"
              />
              <button
                type="button"
                @click="showResetPassword = !showResetPassword"
                class="absolute right-1.5 top-1/2 -translate-y-1/2 w-9 h-9 rounded-lg flex items-center justify-center text-[#78716C] hover:text-[#1C1917] hover:bg-[#ECE8DF]/70"
                :aria-label="showResetPassword ? 'Sembunyikan password' : 'Lihat password'"
              >
                <UiIcon :name="showResetPassword ? 'visibility_off' : 'visibility'" :size="17" />
              </button>
            </div>
          </div>

          <div class="flex justify-end gap-2.5 pt-2">
            <button type="button" @click="showResetModal = false" class="ui-btn-secondary">
              Batal
            </button>
            <button type="submit" :disabled="isResetting" class="ui-btn-primary">
              <UiIcon v-if="isResetting" name="progress_activity" :size="16" class="animate-spin" />
              <span>{{ isResetting ? 'Menyimpan...' : 'Simpan Password' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Konfirmasi Hapus Guru -->
    <div
      v-if="teacherToDelete"
      class="fixed inset-0 z-50 bg-[#1C1917]/50 backdrop-blur-[2px] flex items-center justify-center p-4"
      @click.self="teacherToDelete = null"
    >
      <div class="ui-panel-elevated w-full max-w-md p-6">
        <div class="flex items-start gap-3.5">
          <div class="w-11 h-11 rounded-xl bg-[#FDF2F4] text-[#9F1239] flex items-center justify-center shrink-0">
            <UiIcon name="warning" :size="22" />
          </div>
          <div>
            <h3 class="text-lg font-bold text-[#1C1917]">Hapus Akun Guru?</h3>
            <p class="text-sm text-[#57534E] mt-1 leading-relaxed">
              Akun <span class="font-bold text-[#1C1917]">{{ teacherToDelete.name }}</span> akan dihapus permanen dan tidak lagi bisa masuk ke portal wali kelas.
            </p>
          </div>
        </div>

        <div class="flex justify-end gap-2.5 mt-6">
          <button type="button" @click="teacherToDelete = null" class="ui-btn-secondary">
            Batal
          </button>
          <button
            type="button"
            :disabled="isDeleting"
            @click="confirmDeleteTeacher"
            class="ui-btn-base bg-[#9F1239] text-white hover:bg-[#881337]"
          >
            <UiIcon v-if="isDeleting" name="progress_activity" :size="16" class="animate-spin" />
            <span>{{ isDeleting ? 'Menghapus...' : 'Ya, Hapus Akun' }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';
import { notify } from '../../utils/toast';

const teachers = ref([]);
const kelasList = ref([]);
const searchQuery = ref('');
const isLoading = ref(true);
const isSubmitting = ref(false);
const showCreatePassword = ref(false);

const form = reactive({
  fullName: '',
  email: '',
  password: '',
  kelas_ids: [],
});

const showResetModal = ref(false);
const selectedTeacher = ref(null);
const newPassword = ref('');
const showResetPassword = ref(false);
const isResetting = ref(false);

const teacherToDelete = ref(null);
const isDeleting = ref(false);

const filteredTeachers = computed(() => {
  const q = searchQuery.value.trim().toLowerCase();
  if (!q) return teachers.value;
  return teachers.value.filter((t) => {
    const inName = t.name?.toLowerCase().includes(q);
    const inEmail = t.email?.toLowerCase().includes(q);
    const inKelas = Array.isArray(t.kelas) && t.kelas.some((k) => k.toLowerCase().includes(q));
    return inName || inEmail || inKelas;
  });
});

const getInitials = (name) => {
  if (!name) return 'GR';
  const cleaned = name.replace(/^(Ust\.|Ustadz|Ustadzah|Bpk\.|Ibu)\s+/i, '');
  const parts = cleaned.trim().split(/\s+/);
  return ((parts[0]?.[0] || '') + (parts[1]?.[0] || '')).toUpperCase() || 'GR';
};

const fetchData = async () => {
  isLoading.value = true;
  try {
    const [resKelas, resGuru] = await Promise.all([
      axios.get('/kelas'),
      axios.get('/guru'),
    ]);
    kelasList.value = resKelas.data;
    teachers.value = resGuru.data;
  } catch (err) {
    console.error('Gagal load guru/kelas', err);
    notify('Gagal memuat daftar guru dan kelas.', 'error');
  } finally {
    isLoading.value = false;
  }
};

const createTeacher = async () => {
  if (!form.fullName || !form.email || !form.password) return;

  isSubmitting.value = true;
  try {
    await axios.post('/guru', {
      fullName: form.fullName,
      email: form.email,
      password: form.password,
      kelas_ids: form.kelas_ids,
    });
    notify(`Akun guru ${form.fullName} berhasil dibuat.`, 'success');
    form.fullName = '';
    form.email = '';
    form.password = '';
    form.kelas_ids = [];
    showCreatePassword.value = false;
    await fetchData();
  } catch (err) {
    const msg = err.response?.data?.message || 'Gagal membuat akun guru. Pastikan email unik.';
    notify(msg, 'error');
  } finally {
    isSubmitting.value = false;
  }
};

const openResetModal = (teacher) => {
  selectedTeacher.value = teacher;
  newPassword.value = '';
  showResetPassword.value = false;
  showResetModal.value = true;
};

const submitResetPassword = async () => {
  if (!newPassword.value || !selectedTeacher.value) return;
  isResetting.value = true;
  try {
    await axios.put(`/guru/${selectedTeacher.value.id}/reset-password`, {
      new_password: newPassword.value,
    });
    notify(`Password untuk ${selectedTeacher.value.name} berhasil diubah.`, 'success');
    showResetModal.value = false;
  } catch (err) {
    const msg = err.response?.data?.message || 'Gagal mereset password.';
    notify(msg, 'error');
  } finally {
    isResetting.value = false;
  }
};

const openDeleteModal = (teacher) => {
  teacherToDelete.value = teacher;
};

const confirmDeleteTeacher = async () => {
  if (!teacherToDelete.value) return;
  isDeleting.value = true;
  const target = teacherToDelete.value;
  try {
    await axios.delete(`/guru/${target.id}`);
    notify(`Akun ${target.name} telah dihapus.`, 'success');
    teacherToDelete.value = null;
    await fetchData();
  } catch (err) {
    notify('Gagal menghapus akun guru.', 'error');
  } finally {
    isDeleting.value = false;
  }
};

onMounted(fetchData);
</script>
