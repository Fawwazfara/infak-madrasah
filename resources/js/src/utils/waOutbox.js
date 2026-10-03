import axios from 'axios';

let draining = false;

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

/**
 * Kirim antrean WhatsApp (wa_outbox) satu per satu dengan jeda dari server
 * (FONNTE_DELAY detik, default 60) supaya nomor tidak keblokir.
 *
 * Loop berjalan di background sampai antrean habis. Jika browser ditutup,
 * sisa pesan tetap tersimpan di antrean dan akan dikirim pada trigger berikutnya.
 *
 * @param {(progress: {sent: number, total: number, remaining: number}) => void} onProgress
 * @param {{signalAbort?: {aborted: boolean}}} options
 */
export async function drainOutbox(onProgress, options = {}) {
  if (draining) return;
  draining = true;

  let total = null;

  try {
    while (!options.signalAbort?.aborted) {
      let res;
      try {
        res = await axios.post('/wa/process', { limit: 1 });
      } catch (error) {
        // Sesi habis / error jaringan: hentikan, antrean tetap aman di server
        console.error('Gagal memproses antrean WA:', error);
        break;
      }

      const { sent, remaining, retry_after } = res.data;
      if (total === null) total = remaining + sent;

      onProgress?.({ sent: total - remaining, total, remaining });

      if (remaining <= 0) break;

      // Tunggu jeda yang diminta server (minimal FONNTE_DELAY detik)
      await sleep(Math.max(1000, (retry_after || 0) * 1000));
    }
  } finally {
    draining = false;
  }
}

export function isDraining() {
  return draining;
}
