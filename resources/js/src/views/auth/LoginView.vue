<template>
  <div class="min-h-screen bg-[#F5F3EE] text-[#1C1917] grid grid-cols-1 lg:grid-cols-12">
    <!-- Left Column: Warm Architectural Branding -->
    <aside class="hidden lg:flex lg:col-span-5 xl:col-span-6 bg-gradient-to-br from-[#292524] via-[#221E1C] to-[#171412] text-[#FDFCF9] p-12 xl:p-16 flex-col justify-between relative overflow-hidden">
      <div
        class="pointer-events-none absolute -top-24 -right-24 w-96 h-96 rounded-full opacity-25"
        style="background: radial-gradient(circle, #A8551E 0%, transparent 70%);"
      ></div>
      <div
        class="pointer-events-none absolute -bottom-28 -left-24 w-80 h-80 rounded-full opacity-15"
        style="background: radial-gradient(circle, #D97706 0%, transparent 70%);"
      ></div>

      <div class="relative z-10 flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-[12px] bg-[#A8551E] text-[#FDFCF9] flex items-center justify-center shadow-[var(--shadow-btn-primary)]">
          <UiIcon name="school" :size="22" :weight="600" />
        </div>
        <div>
          <p class="font-bold text-base tracking-tight leading-tight">Madrasah Assajjad</p>
          <p class="text-xs text-[#D6CFC2]">Sistem Administrasi &amp; Transparansi Infak</p>
        </div>
      </div>

      <div class="relative z-10 max-w-lg my-auto py-8">
        <span class="inline-flex items-center gap-2 text-xs font-semibold px-3 py-1 rounded-full bg-white/10 text-[#FDE8D7] border border-white/15 mb-5">
          Portal Admin &amp; Wali Kelas
        </span>
        <h1 class="text-3xl xl:text-4xl font-extrabold tracking-tight leading-[1.15] text-[#FDFCF9]">
          Catatan infak tertata, laporan bulanan jelas untuk semua pengajar.
        </h1>
        <p class="mt-4 text-sm xl:text-base text-[#D6CFC2] leading-relaxed">
          Pantau kepatuhan pembayaran siswa per kelas, pemasukan serta pengeluaran bulanan, hingga ekspor PDF laporan keuangan madrasah secara real-time.
        </p>

        <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 gap-3.5">
          <div class="p-4 rounded-[14px] bg-white/[0.06] border border-white/10">
            <UiIcon name="fact_check" :size="18" :weight="600" class="text-[#F4A26E] mb-2" />
            <p class="text-xs font-bold text-[#FDFCF9]">Checklist Kelas Terstruktur</p>
            <p class="text-xs text-[#D6CFC2] mt-0.5 leading-relaxed">Matriks infak 12 bulan ajaran &amp; pengingat wali otomatis.</p>
          </div>
          <div class="p-4 rounded-[14px] bg-white/[0.06] border border-white/10">
            <UiIcon name="account_balance" :size="18" :weight="600" class="text-[#F4A26E] mb-2" />
            <p class="text-xs font-bold text-[#FDFCF9]">Transparansi &amp; Privasi</p>
            <p class="text-xs text-[#D6CFC2] mt-0.5 leading-relaxed">Guru bisa memantau grafik &amp; laporan bulanan dengan kasbon tersamarkan.</p>
          </div>
        </div>
      </div>

      <p class="relative z-10 text-xs text-[#A8A29E]">
        &copy; {{ new Date().getFullYear() }} Madrasah Diniyah Assajjad
      </p>
    </aside>

    <!-- Right Column: Login Card -->
    <main class="lg:col-span-7 xl:col-span-6 flex items-center justify-center p-4 sm:p-8 lg:p-12">
      <div class="w-full max-w-md space-y-6">
        <!-- Mobile Brand Header -->
        <div class="lg:hidden flex items-center gap-3">
          <div class="w-10 h-10 rounded-[11px] bg-[#292524] text-[#FDFCF9] flex items-center justify-center shadow-[var(--shadow-btn-navy)]">
            <UiIcon name="school" :size="20" :weight="600" />
          </div>
          <div>
            <p class="font-bold text-base text-[#1C1917] leading-tight">Madrasah Assajjad</p>
            <p class="text-xs text-[#78716C]">Portal Admin &amp; Wali Kelas</p>
          </div>
        </div>

        <div class="ui-stagger ui-stagger-1 ui-panel-elevated p-6 sm:p-8 space-y-6">
          <div>
            <h2 class="text-2xl font-bold text-[#1C1917] tracking-tight">Masuk ke Portal</h2>
            <p class="text-sm text-[#57534E] mt-1">Gunakan akun Admin Tata Usaha atau akun Guru / Wali Kelas Anda.</p>
          </div>

          <div
            v-if="showError"
            role="alert"
            class="rounded-[12px] bg-[#FDF2F4] border border-[#9F1239]/20 p-3.5 flex items-start gap-2.5 text-sm text-[#9F1239]"
          >
            <UiIcon name="error" :size="18" :weight="600" class="shrink-0 mt-0.5" />
            <span>{{ errorMessage }}</span>
          </div>

          <form class="space-y-4" @submit.prevent="handleLogin">
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-[#44403C]" for="email">Alamat Email</label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                required
                autocomplete="email"
                placeholder="nama@assajjad.com"
                class="ui-input"
              >
            </div>

            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-[#44403C]" for="password">Kata Sandi</label>
              <div class="relative">
                <input
                  id="password"
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  required
                  autocomplete="current-password"
                  placeholder="Masukkan kata sandi"
                  class="ui-input pr-12"
                >
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  aria-label="Tampilkan atau sembunyikan kata sandi"
                  class="absolute right-1.5 top-1/2 -translate-y-1/2 w-9 h-9 rounded-[8px] text-[#78716C] hover:text-[#1C1917] hover:bg-[#ECE8DF] flex items-center justify-center transition-[color,background-color,transform] duration-150 ease-out active:scale-[0.96]"
                >
                  <UiIcon :name="showPassword ? 'visibility_off' : 'visibility'" :size="18" :weight="500" />
                </button>
              </div>
            </div>

            <div class="flex items-center justify-between pt-1">
              <label for="remember-me" class="inline-flex items-center gap-2 text-xs text-[#57534E] cursor-pointer select-none">
                <input
                  v-model="form.remember"
                  type="checkbox"
                  id="remember-me"
                  class="w-4 h-4 rounded border-[#D6D0C4] text-[#A8551E] focus:ring-[#A8551E]"
                >
                <span>Ingat sesi saya</span>
              </label>
            </div>

            <button
              type="submit"
              :disabled="isLoading"
              class="ui-btn-navy ui-btn-icon-l w-full mt-2"
            >
              <UiIcon v-if="isLoading" name="progress_activity" :size="18" :weight="600" class="animate-spin" />
              <span>{{ isLoading ? 'Memverifikasi...' : 'Masuk ke Portal' }}</span>
            </button>
          </form>

          <p class="text-xs text-center text-[#78716C] pt-2 border-t border-black/[0.06]">
            Lupa kata sandi akun guru? Hubungi Admin Tata Usaha untuk reset sandi.
          </p>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const showPassword = ref(false);
const showError = ref(false);
const errorMessage = ref('');
const isLoading = ref(false);

const form = reactive({
  email: '',
  password: '',
  remember: false
});

const handleLogin = async () => {
  isLoading.value = true;
  showError.value = false;

  try {
    await axios.get('/sanctum/csrf-cookie');
    const response = await axios.post('/login', form);

    localStorage.setItem('user', JSON.stringify(response.data.user));

    if (response.data.user.role === 'admin') {
      router.push('/admin/dashboard');
    } else {
      router.push('/guru/dashboard');
    }
  } catch (error) {
    showError.value = true;
    errorMessage.value =
      error.response?.data?.errors?.email?.[0] ||
      error.response?.data?.message ||
      'Email atau kata sandi salah. Silakan coba lagi.';
  } finally {
    isLoading.value = false;
  }
};
</script>
