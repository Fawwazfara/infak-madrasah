<template>
  <div class="flex-1 flex flex-col min-w-0 relative h-full bg-surface-dim md:bg-transparent">
    
    <!-- Mobile specific top padding since TopAppBar is in AdminLayout now -->
    <!-- We don't need a specific header here because AdminLayout has it, we just provide content -->
    
    <main class="w-full max-w-2xl mx-auto px-container-margin py-lg space-y-lg pb-24 md:pb-lg h-full overflow-y-auto">
      
      <!-- Form Section: Buat Akun Guru -->
      <section class="bg-surface-container-lowest rounded-[24px] shadow-sm md:shadow-md p-lg border border-outline-variant/30 flex flex-col gap-md">
        <h2 class="font-headline-md text-headline-md text-on-surface">Buat Akun Guru</h2>
        
        <form class="flex flex-col gap-md" @submit.prevent="saveGuru">
          <!-- Input: Full Name -->
          <div class="flex flex-col gap-xs">
            <label class="font-label-md text-label-md text-on-surface-variant" for="fullName">Nama Lengkap</label>
            <input v-model="form.fullName" required class="w-full h-12 px-4 bg-surface rounded-xl border border-outline-variant text-body-md text-on-surface placeholder:text-outline/50 focus:outline-none focus:ring-4 focus:ring-primary/20 focus:border-primary transition-all" id="fullName" placeholder="Masukkan nama lengkap" type="text">
          </div>
          
          <!-- Input: Email/Username -->
          <div class="flex flex-col gap-xs">
            <label class="font-label-md text-label-md text-on-surface-variant" for="email">Email atau Username</label>
            <input v-model="form.email" required class="w-full h-12 px-4 bg-surface rounded-xl border border-outline-variant text-body-md text-on-surface placeholder:text-outline/50 focus:outline-none focus:ring-4 focus:ring-primary/20 focus:border-primary transition-all" id="email" placeholder="email@madrasah.edu" type="email">
          </div>

          <!-- Input: Password -->
          <div class="flex flex-col gap-xs">
            <label class="font-label-md text-label-md text-on-surface-variant" for="password">Password (Awal)</label>
            <div class="relative">
              <input v-model="form.password" required :type="showPasswordInput ? 'text' : 'password'" class="w-full h-12 pl-4 pr-12 bg-surface rounded-xl border border-outline-variant text-body-md text-on-surface placeholder:text-outline/50 focus:outline-none focus:ring-4 focus:ring-primary/20 focus:border-primary transition-all block" id="password" placeholder="Masukkan password awal">
              <button type="button" @click="showPasswordInput = !showPasswordInput" class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-on-surface-variant hover:text-primary transition-colors flex items-center justify-center rounded-full">
                <span class="material-symbols-outlined text-[20px]">{{ showPasswordInput ? 'visibility_off' : 'visibility' }}</span>
              </button>
            </div>
          </div>
          
          <!-- Chips: Penugasan Kelas -->
          <div class="flex flex-col gap-sm pt-2">
            <label class="font-label-md text-label-md text-on-surface-variant">Penugasan Kelas (Opsional)</label>
            <div class="flex flex-wrap gap-2">
              <button 
                v-for="kelas in availableKelas" 
                :key="kelas.id"
                type="button"
                @click="toggleKelas(kelas.id)"
                :class="[
                  'h-10 px-4 rounded-full font-label-md text-label-md transition-all focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2',
                  form.kelas.includes(kelas.id)
                    ? 'bg-primary-container text-on-primary-container shadow-sm'
                    : 'bg-surface-container border border-outline-variant/50 text-on-surface-variant hover:bg-surface-container-high'
                ]"
              >
                {{ kelas.nama_kelas }}
              </button>
            </div>
          </div>
          
          <!-- Submit Button -->
          <div class="pt-sm mt-2 border-t border-outline-variant/30 pt-4">
            <button 
              type="submit"
              :disabled="isSubmitting"
              :class="[
                'w-full h-12 font-label-md text-label-md rounded-xl transition-all shadow-md focus:outline-none flex items-center justify-center gap-2',
                isSubmitting ? 'bg-secondary opacity-80 cursor-not-allowed text-white' : 'bg-primary-container text-on-primary-container hover:bg-primary-container/90 active:scale-95'
              ]"
            >
              <span v-if="!isSubmitting" class="material-symbols-outlined fill">save</span>
              <span v-if="isSubmitting" class="material-symbols-outlined animate-spin">sync</span>
              {{ isSubmitting ? 'Menyimpan...' : 'Simpan Akun' }}
            </button>
          </div>
        </form>
      </section>

      <!-- List Section: Daftar Guru Aktif -->
      <section class="flex flex-col gap-md pt-4">
        <div class="flex items-center justify-between px-2 md:px-0">
          <h2 class="font-headline-md text-headline-md text-on-surface">Daftar Guru Aktif</h2>
          <span class="bg-surface-container text-on-surface-variant px-3 py-1 rounded-full font-label-sm text-label-sm">{{ teachers.length }} Guru</span>
        </div>
        
        <!-- Grid layout for desktop, vertical list for mobile -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Teacher Cards -->
          <div 
            v-for="teacher in teachers" 
            :key="teacher.id"
            class="bg-surface-container-lowest rounded-[24px] shadow-sm p-4 border border-outline-variant/30 flex items-center justify-between gap-2 hover:shadow-md transition-shadow"
          >
            <div class="flex items-center gap-4 min-w-0 flex-1">
              <!-- Avatar Placeholder -->
              <div :class="['h-12 w-12 rounded-full flex items-center justify-center flex-shrink-0', teacher.colorClass]">
                <span class="font-headline-md text-headline-md">{{ getInitials(teacher.name) }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="font-label-md text-label-md text-on-surface font-semibold truncate">{{ teacher.name }}</h3>
                <p class="font-label-sm text-[11px] text-on-surface-variant truncate mb-1">{{ teacher.email }}</p>
                <div class="flex flex-wrap gap-1">
                  <span 
                    v-for="kls in teacher.kelas" 
                    :key="kls" 
                    class="px-2 py-0.5 bg-surface-container-high text-on-surface-variant rounded font-label-sm text-[10px]"
                  >
                    {{ kls }}
                  </span>
                </div>
              </div>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-1 flex-shrink-0">
              <button 
                @click="viewPassword(teacher)"
                title="Lihat Password" 
                class="text-primary hover:bg-primary/10 p-2 rounded-full transition-colors flex items-center justify-center"
              >
                <span class="material-symbols-outlined text-[20px]">key</span>
              </button>
              <button 
                @click="deleteTeacher(teacher)"
                title="Hapus" 
                class="text-error hover:bg-error/10 p-2 rounded-full transition-colors flex items-center justify-center"
              >
                <span class="material-symbols-outlined text-[20px]">delete</span>
              </button>
            </div>
          </div>
        </div>
      </section>
      
    </main>

    <!-- Password Modal -->
    <div 
      v-if="modalTeacher" 
      class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 p-container-margin backdrop-blur-sm" 
      @click="closeModal"
    >
      <div class="bg-surface-container-lowest rounded-[24px] shadow-2xl w-full max-w-sm overflow-hidden" @click.stop>
        <div class="p-lg border-b border-outline-variant/30 flex justify-between items-center bg-surface">
          <h3 class="font-headline-md text-primary flex items-center gap-2">
            <span class="material-symbols-outlined">lock_reset</span> Reset Password
          </h3>
          <button class="text-on-surface-variant hover:bg-surface-container p-1.5 rounded-full transition-colors" @click="closeModal">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <form @submit.prevent="resetPassword" class="p-lg flex flex-col gap-md">
          <p class="font-body-md text-body-md text-on-surface text-center mb-2">
            Masukkan password baru untuk guru <strong>{{ modalTeacher.name }}</strong>:
          </p>
          <div class="flex flex-col gap-xs">
            <div class="relative">
              <input v-model="newPassword" required :type="showNewPassword ? 'text' : 'password'" class="w-full h-12 pl-4 pr-12 bg-surface rounded-xl border border-outline-variant text-body-md text-on-surface placeholder:text-outline/50 focus:outline-none focus:ring-4 focus:ring-primary/20 focus:border-primary transition-all block" placeholder="Password baru">
              <button type="button" @click="showNewPassword = !showNewPassword" class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-on-surface-variant hover:text-primary transition-colors flex items-center justify-center rounded-full">
                <span class="material-symbols-outlined text-[20px]">{{ showNewPassword ? 'visibility_off' : 'visibility' }}</span>
              </button>
            </div>
          </div>
          
          <button type="submit" :disabled="isResetting" :class="[
            'mt-4 w-full h-12 rounded-xl font-label-md transition-all shadow-md focus:outline-none flex items-center justify-center gap-2',
            isResetting ? 'bg-secondary opacity-80 cursor-not-allowed text-white' : 'bg-primary-container text-on-primary-container hover:bg-primary-container/90 active:scale-95'
          ]">
            <span v-if="!isResetting" class="material-symbols-outlined fill">save</span>
            <span v-if="isResetting" class="material-symbols-outlined animate-spin">sync</span>
            {{ isResetting ? 'Menyimpan...' : 'Simpan Password Baru' }}
          </button>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

const availableKelas = ref([]);
const teachers = ref([]);

const showPasswordInput = ref(false);
const isSubmitting = ref(false);

const form = reactive({
  fullName: '',
  email: '',
  password: '',
  kelas: []
});

const modalTeacher = ref(null);
const newPassword = ref('');
const showNewPassword = ref(false);
const isResetting = ref(false);

onMounted(async () => {
  try {
    const resKelas = await axios.get('/kelas');
    availableKelas.value = resKelas.data;

    fetchTeachers();
  } catch (error) {
    console.error("Gagal mengambil data", error);
  }
});

const fetchTeachers = async () => {
  try {
    const res = await axios.get('/guru');
    teachers.value = res.data;
  } catch (error) {
    console.error("Gagal memuat daftar guru", error);
  }
};

const toggleKelas = (kelasId) => {
  const index = form.kelas.indexOf(kelasId);
  if (index === -1) {
    form.kelas.push(kelasId);
  } else {
    form.kelas.splice(index, 1);
  }
};

const getInitials = (name) => {
  const parts = name.split(' ');
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase();
  }
  return parts[0].substring(0, 2).toUpperCase();
};

const saveGuru = async () => {
  isSubmitting.value = true;
  
  try {
    await axios.post('/guru', {
      fullName: form.fullName,
      email: form.email,
      password: form.password,
      kelas_ids: form.kelas
    });
    
    // Reset form
    form.fullName = '';
    form.email = '';
    form.password = '';
    form.kelas = [];
    showPasswordInput.value = false;
    
    alert('Akun guru berhasil dibuat!');
    
    // Refresh teacher list
    fetchTeachers();
  } catch (error) {
    console.error("Gagal menambah guru", error);
    let errorMsg = 'Gagal membuat akun guru.';
    if (error.response?.data?.errors) {
      const errs = error.response.data.errors;
      errorMsg += '\\n' + Object.values(errs).flat().join('\\n');
    } else if (error.response?.data?.message) {
      errorMsg += '\\n' + error.response.data.message;
    }
    alert(errorMsg);
  } finally {
    isSubmitting.value = false;
  }
};

const deleteTeacher = async (teacher) => {
  if(confirm(`Anda yakin ingin menghapus akun guru ${teacher.name}?`)) {
    try {
      await axios.delete(`/guru/${teacher.id}`);
      teachers.value = teachers.value.filter(t => t.id !== teacher.id);
    } catch (error) {
      console.error("Gagal menghapus guru", error);
      alert('Gagal menghapus guru.');
    }
  }
};

const viewPassword = (teacher) => {
  modalTeacher.value = teacher;
  newPassword.value = '';
  showNewPassword.value = false;
};

const closeModal = () => {
  modalTeacher.value = null;
};

const resetPassword = async () => {
  if (!newPassword.value || newPassword.value.length < 6) {
    alert('Password baru harus minimal 6 karakter.');
    return;
  }
  
  isResetting.value = true;
  try {
    await axios.put(`/guru/${modalTeacher.value.id}/reset-password`, {
      new_password: newPassword.value
    });
    alert('Password berhasil direset!');
    closeModal();
  } catch (error) {
    console.error("Gagal mereset password", error);
    let errorMsg = 'Gagal mereset password.';
    if (error.response?.data?.errors) {
      const errs = error.response.data.errors;
      errorMsg += '\\n' + Object.values(errs).flat().join('\\n');
    } else if (error.response?.data?.message) {
      errorMsg += '\\n' + error.response.data.message;
    }
    alert(errorMsg);
  } finally {
    isResetting.value = false;
  }
};
</script>
