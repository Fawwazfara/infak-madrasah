<template>
  <div class="flex items-center justify-center min-h-screen bg-[#F5F3EE] p-6">
    <div class="ui-panel-elevated p-8 max-w-md w-full text-center space-y-4">
      <div class="w-12 h-12 mx-auto rounded-[12px] bg-[#292524] text-[#FDFCF9] flex items-center justify-center">
        <UiIcon name="school" :size="22" :weight="600" />
      </div>
      <h1 class="text-2xl font-bold text-[#1C1917]">Infak Madrasah Assajjad</h1>
      <p class="text-sm text-[#57534E]">Portal administrasi infak dan laporan keuangan madrasah.</p>
      <router-link to="/login" class="ui-btn-navy w-full">Masuk ke Portal</router-link>
    </div>
  </div>
</template>

<script setup>
</script>
