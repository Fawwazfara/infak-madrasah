<template>
  <div class="flex-1 flex flex-col min-w-0 h-full bg-surface-dim md:bg-transparent overflow-hidden relative">
    
    <!-- Mobile: Judul Halaman -->
    <div class="px-container-margin pt-4 pb-2 md:hidden">
      <h2 class="font-headline-lg-mobile text-headline-lg-mobile text-on-background">Madrasah Chat</h2>
    </div>

    <main class="w-full max-w-4xl mx-auto md:px-container-margin md:py-lg flex flex-1 h-full overflow-hidden">
      
      <!-- Kontainer Utama: Langsung Ruang Obrolan dengan Admin -->
      <div class="flex w-full h-full md:bg-surface-container-lowest md:rounded-3xl md:shadow-md md:border border-outline-variant/30 overflow-hidden relative flex-col">
        
        <!-- Chat Header (Admin) -->
        <div class="h-16 px-4 bg-surface-container-lowest border-b border-outline-variant/30 flex items-center gap-3 shrink-0 z-10 shadow-sm" v-if="adminProfile">
          <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center overflow-hidden shrink-0">
            <span class="material-symbols-outlined text-[20px]">admin_panel_settings</span>
          </div>
          
          <div class="flex-1 min-w-0">
            <h3 class="font-label-md text-[16px] font-bold text-on-surface truncate">{{ adminProfile.name }} (Admin)</h3>
            <p class="font-label-sm text-[11px] text-[#16A34A] truncate">
              Online
            </p>
          </div>

          <button class="p-2 text-on-surface-variant rounded-full hover:bg-surface-container" @click="fetchConversation(adminProfile.id, false)">
            <span class="material-symbols-outlined" :class="{'animate-spin': isFetchingChat}">sync</span>
          </button>
        </div>

        <div v-else class="h-16 flex items-center px-4 bg-surface-container-lowest border-b border-outline-variant/30 text-on-surface-variant">
          Memuat profil Admin...
        </div>

        <!-- Chat Messages Area -->
        <div class="flex-1 overflow-y-auto p-4 flex flex-col gap-3 bg-[#f6f7f2]">
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

            <div v-if="currentMessages.length === 0 && adminProfile && !isFetchingChat" class="text-center p-8 text-on-surface-variant">
              Belum ada percakapan dengan Admin.
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
              placeholder="Ketik pesan untuk Admin..." 
              :disabled="!adminProfile"
              class="flex-1 h-12 px-4 bg-surface rounded-full border border-outline-variant/50 focus:outline-none focus:border-primary text-body-md transition-colors"
            >
            
            <button 
              type="submit" 
              :disabled="!newMessage.trim() || !adminProfile || isSending"
              :class="[
                'w-12 h-12 rounded-full flex items-center justify-center shrink-0 transition-all',
                newMessage.trim() && !isSending ? 'bg-primary text-on-primary hover:bg-primary/90 shadow-md' : 'bg-surface-container-high text-on-surface-variant'
              ]"
            >
              <span class="material-symbols-outlined text-[20px] ml-1">send</span>
            </button>
          </form>
        </div>

      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import axios from 'axios';

const adminProfile = ref(null);
const newMessage = ref('');
const currentMessages = ref([]);
const chatEndRef = ref(null);
const isSending = ref(false);
const isFetchingChat = ref(false);
let pollingInterval = null;

onMounted(async () => {
  await fetchAdminProfile();
  if (adminProfile.value) {
    await fetchConversation(adminProfile.value.id, true);
    startPolling();
  }
});

onUnmounted(() => {
  stopPolling();
});

const fetchAdminProfile = async () => {
  try {
    const res = await axios.get('/messages/admin-profile');
    adminProfile.value = res.data;
  } catch (error) {
    console.error("Gagal mendapatkan profile admin", error);
  }
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

const startPolling = () => {
  if (pollingInterval) clearInterval(pollingInterval);
  pollingInterval = setInterval(() => {
    if (adminProfile.value) {
      fetchConversation(adminProfile.value.id, false);
    }
  }, 10000);
};

const stopPolling = () => {
  if (pollingInterval) clearInterval(pollingInterval);
};

const scrollToBottom = async () => {
  await nextTick();
  if (chatEndRef.value) {
    chatEndRef.value.scrollIntoView({ behavior: 'smooth' });
  }
};

const sendMessage = async () => {
  if (!newMessage.value.trim() || !adminProfile.value) return;
  
  isSending.value = true;
  const msgText = newMessage.value.trim();
  newMessage.value = '';
  
  try {
    const res = await axios.post('/messages/send', {
      receiver_id: adminProfile.value.id,
      message: msgText
    });
    
    currentMessages.value.push(res.data.data);
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
