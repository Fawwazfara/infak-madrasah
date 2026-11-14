<template>
  <div class="space-y-4">
    <div class="ui-stagger ui-stagger-1">
      <span class="inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-[#A8551E] mb-1">
        <UiIcon name="forum" :size="14" />
        Komunikasi Pengurus
      </span>
      <h2 class="text-2xl sm:text-3xl font-extrabold text-[#1C1917] tracking-tight">Pesan ke Admin</h2>
    </div>

    <div class="ui-panel overflow-hidden flex flex-col h-[calc(100vh-200px)] min-h-[520px] ui-stagger ui-stagger-2">
      <!-- Chat Header -->
      <div class="px-5 py-4 bg-[#FDFCF9] border-b border-[#E7E2D8] flex items-center justify-between gap-3">
        <div class="flex items-center gap-3.5 min-w-0">
          <div class="relative shrink-0">
            <div class="w-11 h-11 rounded-full bg-[#292524] text-[#FDFCF9] flex items-center justify-center font-bold">
              AD
            </div>
            <span class="absolute bottom-0 right-0 w-3 h-3 bg-[#A8551E] border-2 border-[#FDFCF9] rounded-full"></span>
          </div>
          <div class="min-w-0">
            <h3 class="text-base font-bold text-[#1C1917] leading-tight truncate">
              {{ adminProfile ? adminProfile.name : 'Admin / Bendahara Madrasah' }}
            </h3>
            <p class="text-xs text-[#A8551E] font-medium flex items-center gap-1.5 mt-0.5">
              <span class="w-1.5 h-1.5 rounded-full bg-[#A8551E]"></span>
              Aktif • Saluran Koordinasi Infaq
            </p>
          </div>
        </div>

        <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-[#F7F4ED] text-[#57534E]">
          <UiIcon name="admin_panel_settings" :size="13" class="text-[#A8551E]" />
          Pengelola Utama
        </span>
      </div>

      <!-- Messages Feed -->
      <div ref="messagesContainer" class="flex-1 p-5 overflow-y-auto space-y-4 bg-[#F7F4ED]">
        <div v-if="isLoading" class="py-12 flex flex-col items-center gap-2 text-[#78716C]">
          <UiIcon name="progress_activity" :size="24" class="text-[#A8551E] animate-spin" />
          <span class="text-xs">Memuat percakapan...</span>
        </div>

        <div v-else-if="messages.length === 0" class="py-12 text-center">
          <div class="w-14 h-14 rounded-2xl bg-[#FDFCF9] text-[#A8551E] flex items-center justify-center mx-auto mb-3" style="box-shadow: var(--shadow-border);">
            <UiIcon name="mail" :size="24" />
          </div>
          <p class="text-sm font-bold text-[#1C1917]">Belum ada pesan</p>
          <p class="text-xs text-[#78716C] max-w-xs mx-auto mt-1 leading-relaxed">
            Kirim pesan kepada Admin/Bendahara terkait setoran infak kelas atau konfirmasi laporan bulanan.
          </p>
        </div>

        <div
          v-else
          v-for="msg in messages"
          :key="msg.id"
          class="flex flex-col"
          :class="msg.isSender ? 'items-end' : 'items-start'"
        >
          <div
            class="max-w-[82%] sm:max-w-[68%] px-4 py-2.5 text-sm leading-relaxed"
            :class="msg.isSender
              ? 'bg-[#292524] text-[#FDFCF9] rounded-2xl rounded-br-sm'
              : 'bg-[#FDFCF9] text-[#1C1917] rounded-2xl rounded-bl-sm'"
            :style="msg.isSender ? 'box-shadow: var(--shadow-btn-navy)' : 'box-shadow: var(--shadow-border)'"
          >
            <p class="whitespace-pre-line">{{ msg.text }}</p>
          </div>
          <span class="text-[10px] text-[#78716C] mt-1 px-1 tabular-nums">{{ msg.time }}</span>
        </div>
      </div>

      <!-- Message Composer -->
      <div class="p-4 bg-[#FDFCF9] border-t border-[#E7E2D8]">
        <form @submit.prevent="sendMessage" class="flex items-center gap-2.5">
          <input
            v-model="newMessage"
            type="text"
            placeholder="Ketik pesan ke admin madrasah..."
            class="ui-input flex-1 !min-h-[44px] bg-[#F7F4ED] border-transparent focus:bg-[#FDFCF9]"
            :disabled="!adminProfile"
          />
          <button
            type="submit"
            :disabled="!newMessage.trim() || !adminProfile || isSending"
            class="ui-btn-primary !w-11 !h-11 !p-0 shrink-0"
            aria-label="Kirim pesan"
          >
            <UiIcon v-if="isSending" name="progress_activity" :size="18" class="animate-spin" />
            <UiIcon v-else name="send" :size="18" />
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { notify } from '../../utils/toast';

const adminProfile = ref(null);
const messages = ref([]);
const newMessage = ref('');
const messagesContainer = ref(null);
const isLoading = ref(true);
const isSending = ref(false);
let pollInterval = null;

const fetchConversation = async (silent = false) => {
  if (!adminProfile.value) return;
  if (!silent) isLoading.value = true;

  try {
    const res = await axios.get(`/messages/conversation/${adminProfile.value.id}`);
    const prevCount = messages.value.length;
    messages.value = res.data;
    if (!silent || messages.value.length !== prevCount) {
      await scrollToBottom();
    }
  } catch (err) {
    console.error('Gagal mengambil pesan', err);
    if (!silent) notify('Gagal memuat pesan.', 'error');
  } finally {
    isLoading.value = false;
  }
};

const initChat = async () => {
  try {
    const res = await axios.get('/messages/admin-profile');
    adminProfile.value = res.data;
    if (adminProfile.value) {
      await fetchConversation();
    }
  } catch (err) {
    console.error('Gagal load admin profile', err);
    notify('Gagal menghubungkan ke saluran admin.', 'error');
  } finally {
    isLoading.value = false;
  }
};

const scrollToBottom = async () => {
  await nextTick();
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
};

const sendMessage = async () => {
  if (!newMessage.value.trim() || !adminProfile.value || isSending.value) return;

  const text = newMessage.value.trim();
  newMessage.value = '';
  isSending.value = true;

  try {
    const res = await axios.post('/messages/send', {
      receiver_id: adminProfile.value.id,
      message: text,
    });

    messages.value.push(res.data.data);
    await scrollToBottom();
  } catch (err) {
    console.error('Gagal kirim pesan', err);
    newMessage.value = text;
    notify('Gagal mengirim pesan ke admin.', 'error');
  } finally {
    isSending.value = false;
  }
};

onMounted(() => {
  initChat();
  pollInterval = setInterval(() => {
    fetchConversation(true);
  }, 10000);
});

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval);
});
</script>
