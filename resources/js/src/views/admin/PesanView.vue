<template>
  <div class="flex-1 flex flex-col min-w-0 h-full bg-surface-dim md:bg-transparent overflow-hidden relative">
    
    <!-- Mobile: Judul Halaman saat di daftar chat -->
    <div class="px-container-margin pt-4 pb-2 md:hidden" v-if="!activeChat">
      <h2 class="font-headline-lg-mobile text-headline-lg-mobile text-on-background">Madrasah Chat</h2>
    </div>

    <main class="w-full max-w-6xl mx-auto md:px-container-margin md:py-lg flex flex-1 h-full overflow-hidden">
      
      <!-- Kontainer Utama (Card untuk Desktop) -->
      <div class="flex w-full h-full md:bg-surface-container-lowest md:rounded-3xl md:shadow-md md:border border-outline-variant/30 overflow-hidden relative">
        
        <!-- KOLOM KIRI: Daftar Obrolan (Tampil jika desktop, atau jika mobile dan tidak ada chat aktif) -->
        <div 
          v-show="!activeChat || isDesktop" 
          class="w-full md:w-[350px] lg:w-[400px] flex flex-col h-full bg-surface md:bg-transparent border-r border-outline-variant/30 shrink-0"
        >
          <!-- Search Bar -->
          <div class="p-4 bg-surface md:bg-transparent sticky top-0 z-10 border-b border-outline-variant/30 md:border-none md:pb-2">
            <div class="relative">
              <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
              <input 
                type="text" 
                v-model="searchQuery"
                placeholder="Cari pesan atau guru..." 
                class="w-full h-12 pl-10 pr-4 bg-surface-container-lowest md:bg-surface-container rounded-xl border border-outline-variant/50 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-body-md transition-colors"
              >
            </div>
          </div>

          <!-- Chat List -->
          <div class="flex-1 overflow-y-auto bg-surface-container-lowest md:bg-transparent rounded-t-3xl md:rounded-none">
            <div 
              v-for="chat in filteredChatList" 
              :key="chat.id"
              @click="openChat(chat)"
              :class="[
                'flex items-center gap-3 p-4 cursor-pointer transition-colors border-b border-outline-variant/20 hover:bg-surface-container-low',
                activeChat && activeChat.id === chat.id ? 'bg-primary/5 md:bg-primary/10' : ''
              ]"
            >
              <!-- Avatar -->
              <div class="relative shrink-0">
                <div class="w-12 h-12 rounded-full bg-secondary-container text-on-secondary-container flex items-center justify-center overflow-hidden">
                  <span v-if="!chat.avatar" class="font-headline-sm">{{ getInitials(chat.name) }}</span>
                  <img v-else :src="chat.avatar" alt="Avatar" class="w-full h-full object-cover">
                </div>
                <!-- Online Indicator -->
                <div v-if="chat.online" class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-primary border-2 border-surface-container-lowest rounded-full"></div>
              </div>
              
              <!-- Content -->
              <div class="flex-1 min-w-0">
                <div class="flex justify-between items-center mb-0.5">
                  <h3 class="font-label-md text-[15px] font-bold text-on-surface truncate pr-2">{{ chat.name }}</h3>
                  <span :class="['font-label-sm text-[11px] shrink-0', chat.unread > 0 ? 'text-primary' : 'text-on-surface-variant']">{{ chat.lastTime }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <p class="font-body-sm text-[13px] text-on-surface-variant truncate pr-2">{{ chat.lastMessage }}</p>
                  <div v-if="chat.unread > 0" class="w-5 h-5 rounded-full bg-primary text-white flex items-center justify-center font-label-sm text-[10px] shrink-0">
                    {{ chat.unread }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- KOLOM KANAN: Ruang Obrolan (Tampil jika desktop, atau jika mobile dan ada chat aktif) -->
        <div 
          v-if="activeChat || isDesktop" 
          :class="[
            'flex-1 flex flex-col h-full bg-[#f6f7f2] relative',
            !activeChat && isDesktop ? 'items-center justify-center' : ''
          ]"
        >
          <!-- State Kosong (Hanya Desktop) -->
          <div v-if="!activeChat && isDesktop" class="text-center p-8">
            <div class="w-24 h-24 bg-surface-container rounded-full flex items-center justify-center mx-auto mb-4 text-primary/40">
              <span class="material-symbols-outlined text-[48px]">forum</span>
            </div>
            <h3 class="font-headline-md text-on-surface mb-2">Madrasah Chat</h3>
            <p class="text-on-surface-variant font-body-md">Pilih salah satu guru di sebelah kiri untuk mulai mengirim pesan.</p>
          </div>

          <!-- Ruang Obrolan Aktif -->
          <template v-else-if="activeChat">
            <!-- Chat Header -->
            <div class="h-16 px-4 bg-surface-container-lowest border-b border-outline-variant/30 flex items-center gap-3 shrink-0 z-10 shadow-sm">
              <button class="md:hidden p-2 -ml-2 text-on-surface-variant rounded-full hover:bg-surface-container" @click="closeChat">
                <span class="material-symbols-outlined">arrow_back</span>
              </button>
              
              <div class="w-10 h-10 rounded-full bg-secondary-container text-on-secondary-container flex items-center justify-center overflow-hidden shrink-0">
                <span v-if="!activeChat.avatar" class="font-label-md">{{ getInitials(activeChat.name) }}</span>
                <img v-else :src="activeChat.avatar" alt="Avatar" class="w-full h-full object-cover">
              </div>
              
              <div class="flex-1 min-w-0">
                <h3 class="font-label-md text-[16px] font-bold text-on-surface truncate">{{ activeChat.name }}</h3>
                <p class="font-label-sm text-[11px] text-on-surface-variant truncate">
                  Online
                </p>
              </div>
              
              <button class="p-2 text-on-surface-variant rounded-full hover:bg-surface-container" @click="fetchConversation(activeChat.id)">
                <span class="material-symbols-outlined" :class="{'animate-spin': isFetchingChat}">sync</span>
              </button>
            </div>

            <!-- Chat Messages Area -->
            <div class="flex-1 overflow-y-auto p-4 flex flex-col gap-3">
              <!-- Date Separator -->
              <div class="flex justify-center my-2">
                <span class="bg-surface-variant text-on-surface-variant px-3 py-1 rounded-full font-label-sm text-[11px]">Hari Ini</span>
              </div>
              
              <div class="flex flex-col gap-4">
                
                <!-- Dynamic Messages -->
                <div 
                  v-for="(msg, idx) in currentMessages" 
                  :key="msg.id || idx" 
                  :class="['flex', msg.isSender ? 'justify-end' : 'justify-start']"
                >
                  <div 
                    :class="[
                      'max-w-[85%] md:max-w-[70%] rounded-2xl p-3 shadow-sm relative',
                      msg.isSender 
                        ? 'bg-primary text-on-primary rounded-tr-sm' 
                        : 'bg-surface-container-high text-on-surface rounded-tl-sm'
                    ]"
                  >
                    <p class="font-body-md text-[14px] leading-snug">{{ msg.text }}</p>
                    <div 
                      :class="[
                        'text-[10px] text-right mt-1 font-medium flex items-center justify-end gap-1',
                        msg.isSender ? 'text-on-primary/80' : 'text-on-surface-variant'
                      ]"
                    >
                      {{ msg.time }}
                      <span v-if="msg.isSender" class="material-symbols-outlined text-[14px]">done</span>
                    </div>
                  </div>
                </div>

              </div>
              
              <!-- Anchor for scrolling to bottom -->
              <div ref="chatEndRef"></div>
            </div>

            <!-- Chat Input Footer -->
            <div class="p-3 bg-surface-container-lowest border-t border-outline-variant/30 shrink-0">
              <form @submit.prevent="sendMessage" class="flex items-center gap-2">
                <button type="button" class="w-10 h-10 rounded-full text-on-surface-variant hover:bg-surface-container flex items-center justify-center shrink-0 transition-colors">
                  <span class="material-symbols-outlined">add_circle</span>
                </button>
                
                <input 
                  v-model="newMessage" 
                  type="text" 
                  placeholder="Ketik pesan..." 
                  class="flex-1 h-12 px-4 bg-surface rounded-full border border-outline-variant/50 focus:outline-none focus:border-primary text-body-md transition-colors"
                >
                
                <button 
                  type="submit" 
                  :disabled="!newMessage.trim() || isSending"
                  :class="[
                    'w-12 h-12 rounded-full flex items-center justify-center shrink-0 transition-all',
                    newMessage.trim() && !isSending ? 'bg-primary text-on-primary hover:bg-primary/90 shadow-md' : 'bg-surface-container-high text-on-surface-variant'
                  ]"
                >
                  <span class="material-symbols-outlined text-[20px] ml-1">send</span>
                </button>
              </form>
            </div>
          </template>
        </div>

      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import axios from 'axios';

// Logika Responsif
const windowWidth = ref(window.innerWidth);

const updateWidth = () => {
  windowWidth.value = window.innerWidth;
};

onMounted(() => {
  window.addEventListener('resize', updateWidth);
});

onUnmounted(() => {
  window.removeEventListener('resize', updateWidth);
  stopPolling();
});

const isDesktop = computed(() => windowWidth.value >= 768);

// Data Asli Database
const chatList = ref([]);
const searchQuery = ref('');

const filteredChatList = computed(() => {
  if (!searchQuery.value) return chatList.value;
  return chatList.value.filter(c => c.name.toLowerCase().includes(searchQuery.value.toLowerCase()));
});

let pollingInterval = null;

const fetchChatList = async () => {
  try {
    const res = await axios.get('/messages/chat-list');
    chatList.value = res.data;
  } catch (error) {
    console.error("Gagal mengambil chat list", error);
  }
};

onMounted(() => {
  fetchChatList();
  startPolling();
});

const startPolling = () => {
  if (pollingInterval) clearInterval(pollingInterval);
  pollingInterval = setInterval(() => {
    fetchChatList();
    if (activeChat.value) {
      fetchConversation(activeChat.value.id, false);
    }
  }, 10000); // 10 seconds
};

const stopPolling = () => {
  if (pollingInterval) clearInterval(pollingInterval);
};

const activeChat = ref(null);
const newMessage = ref('');
const currentMessages = ref([]);
const chatEndRef = ref(null);
const isSending = ref(false);
const isFetchingChat = ref(false);

const getInitials = (name) => {
  if (!name) return 'U';
  const parts = name.split(' ').filter(n => !n.includes('(') && !n.includes(')'));
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase();
  }
  return name.substring(0, 2).toUpperCase();
};

const openChat = async (chat) => {
  activeChat.value = chat;
  chat.unread = 0; // mark as read locally
  await fetchConversation(chat.id, true);
};

const closeChat = () => {
  activeChat.value = null;
};

const fetchConversation = async (userId, shouldScroll = false) => {
  isFetchingChat.value = true;
  try {
    const res = await axios.get(`/messages/conversation/${userId}`);
    currentMessages.value = res.data;
    if (shouldScroll) {
      scrollToBottom();
    }
  } catch (error) {
    console.error(error);
  } finally {
    isFetchingChat.value = false;
  }
};

const scrollToBottom = async () => {
  await nextTick();
  if (chatEndRef.value) {
    chatEndRef.value.scrollIntoView({ behavior: 'smooth' });
  }
};

const sendMessage = async () => {
  if (!newMessage.value.trim() || !activeChat.value) return;
  
  isSending.value = true;
  const msgText = newMessage.value.trim();
  newMessage.value = '';
  
  try {
    const res = await axios.post('/messages/send', {
      receiver_id: activeChat.value.id,
      message: msgText
    });
    
    currentMessages.value.push(res.data.data);
    
    // Update chat list last message locally
    const chatInList = chatList.value.find(c => c.id === activeChat.value.id);
    if (chatInList) {
      chatInList.lastMessage = res.data.data.text;
      chatInList.lastTime = res.data.data.time;
    }
    
    scrollToBottom();
  } catch (error) {
    console.error(error);
    alert('Gagal mengirim pesan');
    newMessage.value = msgText; // restore
  } finally {
    isSending.value = false;
  }
};
</script>
