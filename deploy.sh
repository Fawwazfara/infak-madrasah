#!/bin/bash
# ===================================================
# AUTOMATED DEPLOYMENT SCRIPT TO cPANEL (Via SCP/SSH)
# ===================================================

# Konfigurasi Server cPanel Anda
REMOTE_USER="u1zh8984"
REMOTE_HOST="assajjad.web.id"
TARGET_DIR="/home/u1zh8984/public_html" # Sesuaikan jika path folder public_html berbeda

echo "🚀 [1/4] Membangun asset frontend (Vite production)..."
npm run build || { echo "❌ Build frontend gagal!"; exit 1; }

echo "📦 [2/4] Membuat arsip deploy.zip yang bersih..."
rm -f deploy.zip
zip -qr deploy.zip . -x "*.git*" -x "node_modules/*" -x "tests/*" -x ".env" -x "*.zip" \
  -x "cookie*.txt" -x "response.json" -x "*.log" -x "composer.phar" -x "composer-2.2.phar" \
  -x "database/*.sqlite" -x ".phpunit.result.cache" -x ".phpunit.cache/*" -x ".github/*" \
  -x "public/hot" -x "storage/logs/*" -x "storage/framework/sessions/*" -x "storage/framework/cache/data/*"

echo "📤 [3/4] Mengirim file deploy.zip ke cPanel..."
scp deploy.zip ${REMOTE_USER}@${REMOTE_HOST}:${TARGET_DIR}/ || { echo "❌ Upload SCP gagal!"; exit 1; }

echo "⚙️ [4/4] Menjalankan unarchive, migrasi & optimasi di cPanel..."
ssh ${REMOTE_USER}@${REMOTE_HOST} "cd ${TARGET_DIR} && unzip -qo deploy.zip && rm deploy.zip && php artisan migrate --force && php artisan optimize:clear && php artisan config:cache && php artisan view:cache" || { echo "❌ Perintah SSH di cPanel gagal!"; exit 1; }

# Hapus zip lokal
rm -f deploy.zip

echo "✨ ========================================"
echo "🎉 DEPLOYMENT BERHASIL SELESAI & SEMPURNA!"
echo "🌐 Kunjungi: https://${REMOTE_HOST}"
echo "========================================"
