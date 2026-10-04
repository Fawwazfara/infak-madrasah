// Declarative Pipeline — deploy Infak Madrasah (Laravel) ke cPanel via SSH
// Prasyarat Jenkins: plugin "SSH Agent" + credential ID persis "cpanel-ssh-key"
// (Credentials > Add > "SSH Username with private key"), job bertipe
// "Pipeline script from SCM" agar `checkout scm` berfungsi.
pipeline {
    agent any

    options {
        timeout(time: 30, unit: 'MINUTES')
        disableConcurrentBuilds()
    }

    environment {
        APP_NAME    = 'Infak Madrasah'
        SSH_USER    = 'u123h8984'
        SSH_HOST    = '194.233.65.45'
        SSH_PORT    = '22'
        DEPLOY_PATH = '/home/u123h8984/public_html/assajjad.web.id'
        GIT_BRANCH  = 'main'
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Build Frontend') {
            steps {
                sh 'npm ci'
                sh 'npm run build'
            }
        }

        stage('CI - Test') {
            steps {
                sh 'composer install --no-interaction --no-progress --prefer-dist'
                sh 'cp -n .env.example .env || true'
                sh 'php artisan key:generate --force'
                sh 'php artisan test'
            }
        }

        

        stage('Deploy to cPanel') {
            steps {
                sshagent(credentials: ['cpanel-ssh-key']) {
                    sh '''
                        set -e
                        SSH_OPTS="-o BatchMode=yes -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -o ConnectTimeout=15"

                        echo "--- [CD] Upload hasil build frontend (public/build) ---"
                        scp $SSH_OPTS -P "${SSH_PORT}" -r public/build \
                            "${SSH_USER}@${SSH_HOST}:${DEPLOY_PATH}/public/"

                        echo "--- [CD] Deploy kode, migrasi, dan cache di server ---"
                        ssh $SSH_OPTS -p "${SSH_PORT}" "${SSH_USER}@${SSH_HOST}" "
                            set -e
                            cd ${DEPLOY_PATH}

                            php artisan down || true

                            git pull origin ${GIT_BRANCH}
                            composer install --no-dev --optimize-autoloader --no-interaction --no-progress

                            php artisan migrate --force

                            php artisan optimize:clear
                            php artisan config:cache && php artisan view:cache

                            php artisan up || true
                        "
                    '''
                }
            }
        }
    }

    post {
        success {
            echo "--- [SUCCESS] Pipeline ${env.APP_NAME} selesai. ---"
        }
        failure {
            echo "--- [FAILURE] Deploy gagal — site dikembalikan dari mode maintenance ---"
            sshagent(credentials: ['cpanel-ssh-key']) {
                sh '''
                    SSH_OPTS="-o BatchMode=yes -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -o ConnectTimeout=15"
                    ssh $SSH_OPTS -p "${SSH_PORT}" "${SSH_USER}@${SSH_HOST}" \
                        "cd ${DEPLOY_PATH} && php artisan up" || true
                '''
            }
        }
    }
}
