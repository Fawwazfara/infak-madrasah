<template>
  <div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-2 ui-stagger ui-stagger-1">
      <div>
        <span class="inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-[#A8551E] mb-1">
          <UiIcon name="forum" :size="14" />
          Koordinasi Pengurus &amp; Guru
        </span>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-[#1C1917] tracking-tight">Pesan Guru</h2>
      </div>
    </div>

    <div class="ui-panel overflow-hidden flex h-[calc(100vh-200px)] min-h-[540px] ui-stagger ui-stagger-2">
      <!-- Chat List Sidebar -->
      <aside
        class="w-full md:w-80 lg:w-96 border-r border-[#E7E2D8] flex flex-col bg-[#FDFCF9]"
        :class="{ 'hidden md:flex': selectedUser }"
      >
        <div class="p-4 border-b border-[#E7E2D8]">
          <div class="relative">
            <UiIcon name="search" :size="16" class="text-[#78716C] absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari guru atau pesan..."
              class="ui-input !min-h-[40px] !py-1.5 pl-10 text-sm bg-[#F7F4ED] border-transparent focus:bg-[#FDFCF9]"
            />
          </div>
        </div>

        <div class="flex-1 overflow-y-auto divide-y divide-[#ECE8DF]">
          <div v-if="isLoadingContacts" class="p-4 space-y-3">
            <div v-for="i in 4" :key="i" class="flex items-center gap-3 animate-pulse">
              <div class="w-11 h-11 rounded-full bg-[#ECE8DF]"></div>
              <div class="flex-1 space-y-2">
                <div class="h-3.5 bg-[#ECE8DF] rounded w-1/2"></div>
                <div class="h-3 bg-[#F7F4ED] rounded w-3/4"></div>
              </div>
            </div>
          </div>

          <button
            v-else
            v-for="contact in filteredContacts"
            :key="contact.id"
            type="button"
            @click="selectContact(contact)"
            class="w-full text-left flex items-center gap-3.5 px-4 py-3.5 cursor-pointer transition-colors"
            :class="selectedUser?.id === contact.id ? 'bg-[#F7F4ED]' : 'hover:bg-[#F7F4ED]/60'"
          >
            <div class="relative shrink-0">
              <div
                class="w-11 h-11 rounded-full flex items-center justify-center font-bold text-sm"
                :class="selectedUser?.id === contact.id ? 'bg-[#292524] text-[#FDFCF9]' : 'bg-[#ECE8DF] text-[#292524]'"
              >
                {{ contact.avatar }}
              </div>
              <span
                v-if="contact.online"
                class="absolute bottom-0 right-0 w-3 h-3 bg-[#A8551E] border-2 border-[#FDFCF9] rounded-full"
              ></span>
            </div>

            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between gap-2 mb-0.5">
                <h4 class="text-sm font-bold text-[#1C1917] truncate">{{ contact.name }}</h4>
                <span class="text-[11px] text-[#78716C] shrink-0 tabular-nums">{{ contact.lastTime }}</span>
              </div>
              <div class="flex items-center justify-between gap-2">
                <p class="text-xs text-[#57534E] truncate">{{ contact.lastMessage }}</p>
                <span
                  v-if="contact.unread > 0"
                  class="px-1.5 py-0.5 rounded-full bg-[#A8551E] text-white text-[10px] font-bold shrink-0 tabular-nums"
                >
                  {{ contact.unread }}
                </span>
              </div>
            </div>
          </button>

          <div v-if="!isLoadingContacts && filteredContacts.length === 0" class="p-8 text-center">
            <p class="text-sm font-semibold text-[#57534E]">Tidak ada kontak ditemukan</p>
            <p class="text-xs text-[#78716C] mt-1">Akun guru yang terdaftar akan tampil di sini.</p>
          </div>
        </div>
      </aside>

      <!-- Active Chat Area -->
      <section
        class="flex-1 flex flex-col bg-[#F7F4ED]"
        :class="{ 'hidden md:flex': !selectedUser }"
      >
        <template v-if="selectedUser">
          <!-- Chat Header -->
          <div class="px-5 py-3.5 bg-[#FDFCF9] border-b border-[#E7E2D8] flex items-center justify-between">
            <div class="flex items-center gap-3 min-w-0">
              <button
                type="button"
                @click="selectedUser = null"
                class="md:hidden w-9 h-9 rounded-lg flex items-center justify-center text-[#57534E] hover:bg-[#ECE8DF]"
                aria-label="Kembali ke daftar kontak"
              >
                <UiIcon name="arrow_back" :size="18" />
              </button>
              <div class="w-10 h-10 rounded-full bg-[#292524] text-[#FDFCF9] flex items-center justify-center font-bold text-sm shrink-0">
                {{ selectedUser.avatar }}
              </div>
              <div class="min-w-0">
                <h3 class="text-sm font-bold text-[#1C1917] leading-tight truncate">{{ selectedUser.name }}</h3>
                <p class="text-xs flex items-center gap-1.5 mt-0.5" :class="selectedUser.online ? 'text-[#A8551E] font-medium' : 'text-[#78716C]'">
                  <span v-if="selectedUser.online" class="w-1.5 h-1.5 rounded-full bg-[#A8551E]"></span>
                  {{ selectedUser.online ? 'Online' : 'Offline' }}
                </p>
              </div>
            </div>
          </div>

          <!-- Messages Feed -->
          <div ref="messagesContainer" class="flex-1 p-5 overflow-y-auto space-y-3.5">
            <div v-if="isLoadingMessages" class="py-10 flex justify-center">
              <UiIcon name="progress_activity" :size="22" class="text-[#A8551E] animate-spin" />
            </div>

            <div v-else-if="messages.length === 0" class="py-12 text-center">
              <div class="w-12 h-12 rounded-2xl bg-[#ECE8DF] text-[#78716C] flex items-center justify-center mx-auto mb-3">
                <UiIcon name="mail" :size="22" />
              </div>
              <p class="text-sm font-semibold text-[#292524]">Belum ada percakapan</p>
              <p class="text-xs text-[#78716C] mt-1">Mulai obrolan dengan {{ selectedUser.name }}.</p>
            </div>

            <div
              v-else
              v-for="msg in messages"
              :key="msg.id"
              class="flex flex-col"
              :class="msg.isSender ? 'items-end' : 'items-start'"
            >
              <div
                class="max-w-[78%] sm:max-w-[68%] px-4 py-2.5 text-sm leading-relaxed"
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

          <!-- Message Input -->
          <div class="p-4 bg-[#FDFCF9] border-t border-[#E7E2D8]">
            <form @submit.prevent="sendMessage" class="flex items-center gap-2.5">
              <input
                v-model="newMessage"
                type="text"
                placeholder="Ketik pesan untuk guru..."
                class="ui-input flex-1 !min-h-[44px] bg-[#F7F4ED] border-transparent focus:bg-[#FDFCF9]"
              />
              <button
                type="submit"
                :disabled="!newMessage.trim() || isSending"
                class="ui-btn-primary !w-11 !h-11 !p-0 shrink-0"
                aria-label="Kirim pesan"
              >
                <UiIcon v-if="isSending" name="progress_activity" :size="18" class="animate-spin" />
                <UiIcon v-else name="send" :size="18" />
              </button>
            </form>
          </div>
        </template>

        <!-- Empty State -->
        <div v-else class="flex-1 flex flex-col items-center justify-center p-8 text-center">
          <div class="w-16 h-16 rounded-2xl bg-[#FDFCF9] text-[#A8551E] flex items-center justify-center mb-4" style="box-shadow: var(--shadow-border);">
            <UiIcon name="forum" :size="28" />
          </div>
          <h3 class="text-lg font-bold text-[#1C1917]">Pusat Koordinasi Pesan</h3>
          <p class="text-sm text-[#78716C] max-w-xs mt-1.5 leading-relaxed">
            Pilih salah satu guru dari daftar kontak di sebelah kiri untuk membalas pertanyaan atau mengirim pemberitahuan.
          </p>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { notify } from '../../utils/toast';

const searchQuery = ref('');
const newMessage = ref('');
const messagesContainer = ref(null);

const contacts = ref([]);
const selectedUser = ref(null);
const messages = ref([]);

const isLoadingContacts = ref(true);
const isLoadingMessages = ref(false);
const isSending = ref(false);
let pollInterval = null;

const filteredContacts = computed(() => {
  const q = searchQuery.value.toLowerCase();
  if (!q) return contacts.value;
  return contacts.value.filter(
    (c) => c.name.toLowerCase().includes(q) || (c.lastMessage && c.lastMessage.toLowerCase().includes(q))
  );
});

const fetchContacts = async () => {
  try {
    const res = await axios.get('/messages/chat-list');
    contacts.value = res.data;
    if (!selectedUser.value && contacts.value.length > 0 && window.innerWidth >= 768) {
      selectContact(contacts.value[0]);
    }
  } catch (err) {
    console.error('Gagal memuat daftar kontak', err);
    notify('Gagal memuat daftar kontak guru.', 'error');
  } finally {
    isLoadingContacts.value = false;
  }
};

const fetchMessages = async (userId, silent = false) => {
  if (!silent) isLoadingMessages.value = true;
  try {
    const res = await axios.get(`/messages/conversation/${userId}`);
    const prevCount = messages.value.length;
    messages.value = res.data;

    if (!silent || messages.value.length !== prevCount) {
      await scrollToBottom();
    }
  } catch (err) {
    console.error('Gagal memuat percakapan', err);
    if (!silent) notify('Gagal memuat percakapan.', 'error');
  } finally {
    isLoadingMessages.value = false;
  }
};

const selectContact = async (contact) => {
  selectedUser.value = contact;
  contact.unread = 0;
  await fetchMessages(contact.id);
};

const scrollToBottom = async () => {
  await nextTick();
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
};

const sendMessage = async () => {
  if (!newMessage.value.trim() || !selectedUser.value || isSending.value) return;

  const text = newMessage.value.trim();
  newMessage.value = '';
  isSending.value = true;

  try {
    const res = await axios.post('/messages/send', {
      receiver_id: selectedUser.value.id,
      message: text,
    });

    const msgObj = res.data.data;
    messages.value.push(msgObj);
    selectedUser.value.lastMessage = text;
    selectedUser.value.lastTime = msgObj.time;

    await scrollToBottom();
  } catch (err) {
    console.error('Gagal kirim pesan', err);
    newMessage.value = text;
    notify('Gagal mengirim pesan.', 'error');
  } finally {
    isSending.value = false;
  }
};

const startPolling = () => {
  pollInterval = setInterval(() => {
    if (selectedUser.value) {
      fetchMessages(selectedUser.value.id, true);
    }
    axios
      .get('/messages/chat-list')
      .then((res) => {
        contacts.value = res.data;
        if (selectedUser.value) {
          const updated = contacts.value.find((c) => c.id === selectedUser.value.id);
          if (updated) updated.unread = 0;
        }
      })
      .catch(() => {});
  }, 10000);
};

onMounted(() => {
  fetchContacts();
  startPolling();
});

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval);
});
</script>
