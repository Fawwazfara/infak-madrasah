<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Infak;
use App\Models\Pengeluaran;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Admin
        User::firstOrCreate(
            ['email' => 'fawwazfara11@assajjad.com'],
            [
                'name' => 'Fawwaz Fara',
                'password' => Hash::make('fwaz000165'),
                'role' => 'admin',
            ]
        );

        // 2. Create Guru (Ibu Erawati)
        $erawati = User::firstOrCreate(
            ['email' => 'erawati@assajjad.com'],
            [
                'name' => 'Ibu Erawati',
                'password' => Hash::make('era123'),
                'role' => 'guru',
            ]
        );

        // 3. Create List of Classes
        $kelasNames = [
            'Bambim', 'TK', 'Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4A', 'Kelas 4B', 
            'Kelas 5', 'Kelas 6', 'Kelas 7', 'Kelas 8', 'Kelas 9', 'Kelas Ulya'
        ];

        $kelasList = [];
        foreach ($kelasNames as $nama) {
            // Erawati handles Kelas 1 and Kelas 2
            $guruId = null;
            if (in_array($nama, ['Kelas 1', 'Kelas 2'])) {
                $guruId = $erawati->id;
            }
            $kelasList[$nama] = Kelas::firstOrCreate(
                ['nama_kelas' => $nama],
                ['guru_id' => $guruId]
            );
        }

        // 4. Create Siswa and their Infak
        $bulanSekarang = date('F');
        $tahunSekarang = date('Y');

        // We only seed Siswa for regular classes (not Bambim) for simulation
        $regularClasses = array_filter($kelasList, function($k, $nama) {
            return $nama !== 'Bambim';
        }, ARRAY_FILTER_USE_BOTH);

        foreach ($regularClasses as $nama => $kelas) {
            // Give each class 3 dummy students
            for ($i = 1; $i <= 3; $i++) {
                $siswa = Siswa::create([
                    'nama_lengkap' => "Siswa {$nama} - {$i}",
                    'nis' => rand(1000, 9999),
                    'kelas_id' => $kelas->id,
                    'jenis_kelamin' => (rand(0, 1) == 1) ? 'L' : 'P'
                ]);

                // Simulasi pembayaran: 70% lunas
                if (rand(1, 100) <= 70) {
                    $nominalOptions = [25000, 25000, 25000, 30000, 50000];
                    $jumlah = $nominalOptions[array_rand($nominalOptions)];
                    
                    Infak::create([
                        'siswa_id' => $siswa->id,
                        'kelas_id' => $kelas->id,
                        'jumlah' => $jumlah,
                        'bulan' => $bulanSekarang,
                        'tahun' => $tahunSekarang,
                        'tanggal_bayar' => now()->subDays(rand(1, 15)),
                        'keterangan' => 'Lunas via Seeder'
                    ]);
                }
            }
        }

        // Add dummy Bambim Infak (bulk payment)
        Infak::create([
            'siswa_id' => null, // No specific student
            'kelas_id' => $kelasList['Bambim']->id,
            'jumlah' => 450000, // bulk amount
            'bulan' => $bulanSekarang,
            'tahun' => $tahunSekarang,
            'tanggal_bayar' => now()->subDays(1),
            'keterangan' => 'Setoran Akhir Bulan Bambim'
        ]);

        // 5. Create Pengeluaran
        Pengeluaran::create([
            'keterangan' => 'Beli Alat Kebersihan',
            'jumlah' => 150000,
            'tanggal' => now()->subDays(2),
            'kategori' => 'Operasional'
        ]);

        Pengeluaran::create([
            'keterangan' => 'Fotokopi Ujian',
            'jumlah' => 300000,
            'tanggal' => now()->subDays(5),
            'kategori' => 'Kegiatan'
        ]);
    }
}
