<?php

namespace App;

class AcademicYear
{
    /**
     * Tahun ajaran yang sedang berjalan (dimulai Juli, berakhir Juni).
     * Contoh: 1 Jul 2026 - 30 Jun 2027 -> 2026
     *
     * Kolom `tahun` di tabel infaks disimpan dengan semantic ini,
     * sehingga satu tahun ajaran (Jul s/d Jun) memiliki nilai `tahun` yang sama.
     */
    public static function current(): int
    {
        $month = (int) date('n');
        $year = (int) date('Y');

        return $month >= 7 ? $year : $year - 1;
    }
}
