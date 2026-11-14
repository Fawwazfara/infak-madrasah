<template>
  <div
    class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 z-50 flex flex-col gap-2.5 max-w-sm w-[calc(100vw-2rem)] pointer-events-none overscroll-contain"
    aria-live="polite"
    aria-atomic="true"
  >
    <TransitionGroup name="ui-toast" tag="div" class="flex flex-col gap-2.5">
      <div
        v-for="item in toastState.items"
        :key="item.id"
        class="pointer-events-auto flex items-start gap-3 px-4 py-3.5 rounded-[14px] border border-transparent text-sm font-medium bg-[#FDFCF9] text-[#1C1917] shadow-[var(--shadow-elevated)]"
      >
        <div
          class="w-7 h-7 rounded-[8px] flex items-center justify-center shrink-0 mt-0.5"
          :class="badgeStyle(item.type)"
        >
          <UiIcon :name="iconFor(item.type)" :size="16" :weight="600" />
        </div>
        <p class="flex-1 leading-snug pt-0.5">{{ item.message }}</p>
        <button
          @click="dismissToast(item.id)"
          class="min-w-[32px] min-h-[32px] -mr-1.5 -mt-0.5 flex items-center justify-center rounded-[8px] text-[#78716C] hover:text-[#1C1917] hover:bg-[#ECE8DF] transition-[background-color,color,transform] duration-150 ease-out enabled:active:scale-[0.96]"
          aria-label="Tutup notifikasi"
        >
          <UiIcon name="close" :size="15" :weight="500" />
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { toastState, dismissToast } from '@/utils/toast';

const badgeStyle = (type) => {
  if (type === 'error') return 'bg-[#FDF2F4] text-[#9F1239]';
  if (type === 'warning') return 'bg-[#FAF4E4] text-[#9A6B16]';
  if (type === 'info') return 'bg-[#F9EFE7] text-[#A8551E]';
  return 'bg-[#F2EFE8] text-[#292524]';
};

const iconFor = (type) => {
  if (type === 'error') return 'error';
  if (type === 'warning') return 'warning';
  if (type === 'info') return 'info';
  return 'check_circle';
};
</script>

