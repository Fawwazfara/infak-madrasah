<!DOCTYPE html>
<html>
<head>
    <title>Form Setoran Infak - {{ $kelas->nama_kelas }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; margin-bottom: 10px; }
        th, td { border: 1px solid #000; padding: 5px; }
        th { background-color: #f2f2f2; text-align: center; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .header-box { border: 2px solid #000; padding: 6px; margin-bottom: 10px; }
        .header-box h2 { margin: 0; text-align: center; font-size: 15px; margin-bottom: 6px; text-transform: uppercase; }
        .info-table { width: 100%; border: none; margin: 0; }
        .info-table th, .info-table td { border: none; padding: 2px; text-align: left; }
        .signature-box { width: 100%; margin-top: 15px; }
        .signature-col { width: 50%; float: left; text-align: center; }
        .clear { clear: both; }
        .h-15 { height: 15px; }
    </style>
</head>
<body>

    <div class="header-box">
        <h2>Formulir Setoran Infak Bulanan</h2>
        <table class="info-table">
            <tr>
                <td width="15%"><strong>Kelas</strong></td>
                <td width="35%">: {{ $kelas->nama_kelas }}</td>
                <td width="15%"><strong>Bulan</strong></td>
                <td width="35%">: ...........................................</td>
            </tr>
            <tr>
                <td><strong>Wali Kelas</strong></td>
                <td>: ...........................................</td>
                <td><strong>Tahun</strong></td>
                <td>: ...........................................</td>
            </tr>
        </table>
    </div>

    <strong>A. PEMASUKAN INFAK</strong>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="30%">Nama Siswa</th>
                <th width="15%">Jml Bulan</th>
                <th width="30%">Keterangan Bulan (Bln apa saja)</th>
                <th width="20%">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($siswas as $index => $siswa)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $siswa->nama_lengkap }}</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @endforeach
            
            <tr><td class="text-center h-15">{{ count($siswas) + 1 }}</td><td></td><td></td><td></td><td></td></tr>
            <tr><td class="text-center h-15">{{ count($siswas) + 2 }}</td><td></td><td></td><td></td><td></td></tr>
            
            <tr>
                <td colspan="4" class="text-right font-bold" style="font-weight: bold;">TOTAL PEMASUKAN</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <strong>B. PENGELUARAN KELAS (Jika Ada)</strong>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="75%">Keterangan Pengeluaran</th>
                <th width="20%">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr><td class="text-center h-15">1</td><td></td><td></td></tr>
            <tr><td class="text-center h-15">2</td><td></td><td></td></tr>
            <tr><td class="text-center h-15">3</td><td></td><td></td></tr>
            <tr><td class="text-center h-15">4</td><td></td><td></td></tr>
            <tr>
                <td colspan="2" class="text-right font-bold" style="font-weight: bold;">TOTAL PENGELUARAN</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div style="border: 2px solid #000; padding: 6px; margin-bottom: 10px;">
        <strong style="font-size: 12px;">C. TOTAL SETORAN BERSIH (A - B) : Rp. ....................................................</strong>
    </div>

    <div class="signature-box">
        <div class="signature-col">
            <p>Wali Kelas</p>
            <br><br>
            <p>(................................................)</p>
        </div>
        <div class="signature-col">
            <p>Admin Penerima</p>
            <br><br>
            <p>(................................................)</p>
        </div>
        <div class="clear"></div>
    </div>

</body>
</html>
