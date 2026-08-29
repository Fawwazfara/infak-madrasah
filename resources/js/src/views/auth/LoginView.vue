<template>
  <div class="bg-[#EEF3F0] text-on-surface font-body-md min-h-screen flex relative overflow-hidden">
    
    <!-- Left Column: Desktop Branding -->
    <div class="hidden md:flex md:w-1/2 bg-primary flex-col items-center justify-center p-12 text-center text-white relative">
      <!-- Optional subtle overlay/gradient -->
      <div class="absolute inset-0 bg-gradient-to-br from-primary to-surface-tint opacity-80"></div>
      
      <div class="w-32 h-32 rounded-[2.5rem] bg-white/20 backdrop-blur-sm border border-white/30 flex items-center justify-center mb-8 z-10 shadow-xl">
        <span class="material-symbols-outlined text-[80px] text-white">mosque</span>
      </div>
      <h1 class="text-4xl md:text-5xl font-bold mb-4 z-10 font-display text-white">Portal Madrasah</h1>
      <p class="text-lg md:text-xl text-primary-fixed z-10 max-w-md">Sistem informasi manajemen terpadu untuk Administrasi dan Akademik Madrasah.</p>
    </div>

    <!-- Right Column: Login Form -->
    <div class="w-full md:w-1/2 flex items-center justify-center p-container-margin md:p-12 relative">
      
      <!-- Toast Notification -->
      <transition 
        enter-active-class="transition duration-300 ease-out" 
        enter-from-class="transform -translate-y-4 opacity-0" 
        enter-to-class="transform translate-y-0 opacity-100" 
        leave-active-class="transition duration-200 ease-in" 
        leave-from-class="transform translate-y-0 opacity-100" 
        leave-to-class="transform -translate-y-4 opacity-0">
        <div v-if="showError" class="absolute top-md left-1/2 -translate-x-1/2 w-[calc(100%-40px)] max-w-md bg-[#991B1B] text-on-error rounded-lg shadow-lg px-md py-sm flex items-center gap-sm z-50">
          <span class="material-symbols-outlined">error</span>
          <span class="font-body-md text-body-md">{{ errorMessage }}</span>
        </div>
      </transition>

      <!-- Main Container -->
      <main class="w-full max-w-md">
        <!-- Central Card -->
        <div class="bg-surface-container-lowest shadow-lg rounded-[24px] px-8 py-10 flex flex-col gap-lg w-full">
          <!-- Logo & Header -->
          <div class="flex flex-col items-center gap-md text-center">
            <div class="md:hidden w-16 h-16 bg-primary rounded-2xl flex items-center justify-center text-white mb-2 shadow-sm">
              <span class="material-symbols-outlined text-[36px]">mosque</span>
            </div>
            <div>
              <h1 class="font-headline-lg-mobile text-headline-lg-mobile md:font-headline-lg md:text-headline-lg text-primary">Selamat Datang</h1>
              <p class="font-body-md text-body-md text-on-surface-variant mt-xs">Admin &amp; Guru Access Portal</p>
            </div>
          </div>
          
          <!-- Login Form -->
          <form class="flex flex-col gap-md" @submit.prevent="handleLogin">
            <!-- Email Input -->
            <div class="flex flex-col gap-xs">
              <label class="font-label-md text-label-md text-on-surface" for="email">Email Address</label>
              <input v-model="form.email" class="h-[48px] px-md rounded-lg border border-[#CBD5E1] bg-surface-container-lowest focus:border-2 focus:border-primary focus:ring-4 focus:ring-[#D1FAE5] outline-none transition-all font-body-md text-body-md text-on-surface" id="email" name="email" placeholder="Enter your email" required type="email">
            </div>
            
            <!-- Password Input -->
            <div class="flex flex-col gap-xs">
              <label class="font-label-md text-label-md text-on-surface" for="password">Password</label>
              <div class="relative w-full">
                <input v-model="form.password" :type="showPassword ? 'text' : 'password'" class="h-[48px] w-full pl-md pr-12 rounded-lg border border-[#CBD5E1] bg-surface-container-lowest focus:border-2 focus:border-primary focus:ring-4 focus:ring-[#D1FAE5] outline-none transition-all font-body-md text-body-md text-on-surface" id="password" name="password" placeholder="Enter your password" required>
                <button @click="showPassword = !showPassword" aria-label="Toggle password visibility" class="absolute right-sm top-1/2 -translate-y-1/2 h-10 w-10 flex items-center justify-center text-on-surface-variant hover:text-primary transition-colors focus:outline-none rounded-full" type="button">
                  <span class="material-symbols-outlined">{{ showPassword ? 'visibility' : 'visibility_off' }}</span>
                </button>
              </div>
            </div>
            
            <!-- Submit Button -->
            <div class="flex items-center gap-xs mt-xs">
              <input v-model="form.remember" type="checkbox" id="remember-me" name="remember-me" class="w-4 h-4 rounded border-[#CBD5E1] text-primary focus:ring-primary focus:ring-offset-0 transition-colors cursor-pointer">
              <label for="remember-me" class="font-label-sm text-label-sm text-on-surface-variant cursor-pointer select-none">Remember Me</label>
            </div>
            
            <button :disabled="isLoading" class="h-[48px] w-full bg-primary-container text-on-primary rounded-xl font-label-md text-label-md flex items-center justify-center hover:bg-surface-tint focus:ring-4 focus:ring-[#D1FAE5] transition-all mt-sm shadow-md disabled:opacity-70 disabled:cursor-not-allowed" type="submit">
              <span v-if="isLoading" class="material-symbols-outlined animate-spin mr-2">progress_activity</span>
              {{ isLoading ? 'Memproses...' : 'Login to Portal' }}
            </button>
          </form>
          
          <!-- Links -->
          <div class="flex flex-col items-center gap-sm mt-md">
            <a class="font-label-sm text-label-sm text-primary hover:underline hover:text-surface-tint transition-colors" href="#">Lupa Password?</a>
            <a class="font-label-sm text-label-sm text-on-surface-variant hover:text-primary transition-colors" href="#">Butuh Bantuan?</a>
          </div>
        </div>
      </main>
    </div>
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
    // 1. Dapatkan CSRF cookie dari Sanctum terlebih dahulu
    await axios.get('/sanctum/csrf-cookie');

    // 2. Lakukan login
    const response = await axios.post('/login', form);
    
    // Simpan informasi pengguna di localStorage
    localStorage.setItem('user', JSON.stringify(response.data.user));
    
    // Redirect berdasarkan role
    if (response.data.user.role === 'admin') {
      router.push('/admin/dashboard');
    } else {
      router.push('/guru/dashboard');
    }
  } catch (error) {
    showError.value = true;
    errorMessage.value = error.response?.data?.message || 'Email atau password salah. Silakan coba lagi.';
    setTimeout(() => {
      showError.value = false;
    }, 5000);
  } finally {
    isLoading.value = false;
  }
};
</script>
