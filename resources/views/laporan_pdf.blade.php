<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan Madrasah</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .total-row { font-weight: bold; background-color: #e6e6e6; }
        .summary { width: 50%; float: right; margin-top: 20px; }
        .clear { clear: both; }
        .footer { text-align: right; margin-top: 50px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>LAPORAN KEUANGAN INFAK MADRASAH</h1>
        <p>Bulan: {{ $bulan }} {{ $tahun }}</p>
    </div>

    <h3>1. Pemasukan dari Infak Siswa</h3>
    <table>
        <thead>
            <tr>
                <th width="10%">No</th>
                <th width="60%">Kelas</th>
                <th width="30%" class="text-right">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pemasukan_per_kelas as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item['kelas'] }}</td>
                <td class="text-right">{{ number_format($item['nominal'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" class="text-right">Total Pemasukan</td>
                <td class="text-right">{{ number_format($total_pemasukan, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <h3>2. Pengeluaran</h3>
    <table>
        <thead>
            <tr>
                <th width="10%">No</th>
                <th width="20%">Tanggal</th>
                <th width="40%">Keperluan</th>
                <th width="30%" class="text-right">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pengeluaran_list as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($item['tanggal'])->format('d/m/Y') }}</td>
                <td>{{ $item['keterangan'] }}</td>
                <td class="text-right">{{ number_format($item['jumlah'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
            @if(count($pengeluaran_list) == 0)
            <tr>
                <td colspan="4" style="text-align: center;">Tidak ada pengeluaran di bulan ini.</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-right">Total Pengeluaran</td>
                <td class="text-right">{{ number_format($total_pengeluaran, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="summary">
        <table>
            <tr>
                <th>Total Pemasukan</th>
                <td class="text-right">Rp {{ number_format($total_pemasukan, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Total Pengeluaran</th>
                <td class="text-right">Rp {{ number_format($total_pengeluaran, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <th>Saldo Akhir</th>
                <td class="text-right">Rp {{ number_format($total_pemasukan - $total_pengeluaran, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>
    
    <div class="clear"></div>

    <div class="footer">
        <p>............................, {{ date('d F Y') }}</p>
        <br><br><br>
        <p>( ........................................ )</p>
        <p>Ketua/Pengurus</p>
    </div>

</body>
</html>
