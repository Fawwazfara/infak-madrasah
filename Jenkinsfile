pipeline {
    agent any

    environment {
        APP_NAME = 'Infak Madrasah'
    }

    stages {
        stage('CI - Check Project') {
            steps {
                echo "--- [CI] Memeriksa Struktur Laravel ${env.APP_NAME} ---"
                sh '''
                    if [ -f "artisan" ]; then
                        echo "[OK] Project Laravel valid."
                    else
                        echo "[ERROR] File artisan tidak ditemukan!"
                        exit 1
                    fi
                '''
            }
        }

        stage('CD - Prepare SSH Deploy') {
            steps {
                echo "--- [CD] Tahap Deploy Siap Diintegrasikan ke cPanel ---"
                sh 'echo "Mengeksekusi tahapan simulasi CD..."'
            }
        }
    }

    post {
        success {
            echo "--- [SUCCESS] Pipeline ${env.APP_NAME} Berhasil! ---"
        }
        failure {
            echo "--- [FAILURE] Pipeline Gagal. Periksa log! ---"
        }
    }
}