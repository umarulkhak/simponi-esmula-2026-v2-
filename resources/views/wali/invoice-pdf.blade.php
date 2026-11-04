<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $siswa->nama }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
        }
        .watermark-logo {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.05;
            z-index: 0;
            pointer-events: none;
        }
        .watermark-lunas {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            z-index: 1;
            opacity: 0.08;
            pointer-events: none;
            user-select: none;
        }
        .watermark-lunas-text {
            font-size: 120px;
            font-weight: bold;
            color: #2e7d32;
            font-family: Arial, sans-serif;
            letter-spacing: 6px;
            text-transform: uppercase;
        }
        .content {
            position: relative;
            z-index: 2;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 6px;
            vertical-align: top;
        }
        th {
            background-color: #bbdefb;
            color: #0d47a1;
            font-weight: bold;
            text-align: center;
            white-space: nowrap;
        }
        .header-table td {
            border: none;
            padding: 0;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .bg-light { background-color: #e3f2fd; }
        .bg-yellow { background-color: #fff9c4; }
        .logo-box {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f5f5;
            border: 1px solid #ddd;
            font-size: 10px;
            color: #999;
        }
        .invoice-title {
            background: #1565c0;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-weight: bold;
            display: inline-block;
            margin-top: 8px;
        }
        .footer-note {
            font-size: 9px;
            color: #555;
            margin-top: 15px;
            text-align: center;
        }
        .legal-warning {
            margin-top: 12px;
            font-size: 8.5px;
            color: #000;
            text-align: justify;
            line-height: 1.3;
        }
        .signature {
            margin-top: 30px;
            text-align: right;
        }
        .signature-box {
            display: inline-block;
            text-align: center;
            min-width: 200px;
        }
        .signature-line {
            margin-top: 30px;
            border-top: 1px solid #000;
            padding-top: 4px;
            font-weight: bold;
        }
        .info-qr-table {
            width: 100%;
            border: none;
            margin-top: 15px;
        }
        .info-qr-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }
        .info-box {
            padding: 10px;
            background-color: #e3f2fd;
            border-radius: 4px;
        }
        .qr-box {
            text-align: center;
            padding: 10px 0;
        }
        .qr-box img {
            width: 100px;
            height: auto;
        }
        .qr-label {
            font-size: 9px;
            color: #555;
            margin-top: 4px;
        }
        .header-line {
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Watermark Logo --}}
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" class="watermark-logo" width="360">
        @endif

        {{-- Watermark LUNAS --}}
        @if ($semuaDikonfirmasi)
            <div class="watermark-lunas">
                <div class="watermark-lunas-text">LUNAS</div>
            </div>
        @endif

        <div class="content">
            {{-- Header --}}
            <div class="header-line">
                <table class="header-table">
                    <tr>
                        <td style="width: 20%;">
                            @if($logoBase64)
                                <img src="{{ $logoBase64 }}" alt="Logo" style="width:80px; height:auto; border:none;">
                            @else
                                <div class="logo-box">LOGO</div>
                            @endif
                        </td>
                        <td style="padding-left: 15px;">
                            <h2 style="margin: 0; color: #0d47a1; font-size: 16px;">SMP MUHAMMADIYAH LARANGAN</h2>
                            <p style="margin: 4px 0; font-size: 10px;">
                                Karangancol, Larangan, Kab. Brebes, Jawa Tengah 52262<br>
                                Telp: (0283) 6183947 | Email: smpmuhammadiyahtarangan@gmail.com
                            </p>
                        </td>
                        <td style="text-align: right; width: 30%;">
                            <div class="invoice-title">INVOICE</div>
                            <p style="margin-top: 8px; font-size: 11px;">
                                <strong>No. Dokumen:</strong><br>
                                <span style="font-family: monospace;">{{ $invNumber }}</span><br>
                                <strong>Tanggal:</strong><br>
                                {{ now()->translatedFormat('l, d F Y') }}
                            </p>
                        </td>
                    </tr>
                </table>
            </div>

            {{-- Info Siswa + QR Code --}}
            <table class="info-qr-table">
                <tr>
                    <td style="width: 70%;">
                        <div class="info-box">
                            <h4 style="margin: 0 0 8px 0; color: #0d47a1; font-size: 13px; border-bottom: 2px solid #fbc02d; padding-bottom: 3px;">
                                Informasi Siswa
                            </h4>
                            <table style="border: none; font-size: 11px; width: 100%;">
                                <tr><td style="width: 35%; font-weight: bold;">Nama Lengkap</td><td>: {{ $siswa->nama }}</td></tr>
                                <tr><td style="font-weight: bold;">NISN</td><td>: {{ $siswa->nisn ?? '–' }}</td></tr>
                                <tr><td style="font-weight: bold;">Kelas</td><td>: {{ $siswa->kelas ?? '–' }}</td></tr>
                                <tr><td style="font-weight: bold;">Angkatan</td><td>: {{ $siswa->angkatan ?? '–' }}</td></tr>
                            </table>
                        </div>
                    </td>
                    <td style="width: 30%; padding-left: 15px;">
                        <div class="qr-box">
                            <img src="data:image/png;base64,{{ $qrCodeBase64 }}" alt="QR Verifikasi">
                            <div class="qr-label">* Dokumen ini diautentikasi secara digital</div>
                        </div>
                    </td>
                </tr>
            </table>

            {{-- Rincian Tagihan --}}
            <h4 style="margin: 20px 0 8px 0; color: #0d47a1; font-size: 13px; border-left: 4px solid #fbc02d; padding-left: 8px;">
                Rincian Tagihan Pembayaran
            </h4>
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 12%;">Tgl Tagihan</th>
                        <th>Rincian</th>
                        <th style="width: 12%;">Jumlah (Rp)</th>
                        <th style="width: 12%;">Tgl Bayar</th>
                        <th style="width: 15%;">Metode</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tagihanList as $tagihan)
                        @php
                            $subtotal = $tagihan->tagihanDetails->sum('jumlah_biaya');
                            $pembayaran = $tagihan->pembayaran->first();
                        @endphp
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($tagihan->tanggal_tagihan)->format('d M Y') }}</td>
                            <td>
                                @foreach ($tagihan->tagihanDetails as $detail)
                                    {{ $detail->nama_biaya }}<br>
                                @endforeach
                            </td>
                            <td class="text-right bold">{{ number_format($subtotal, 0, ',', '.') }}</td>
                            <td class="text-center">
                                @if ($pembayaran && $pembayaran->tanggal_bayar)
                                    {{ \Carbon\Carbon::parse($pembayaran->tanggal_bayar)->format('d M Y') }}
                                @else
                                    –
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($pembayaran && $pembayaran->metode_pembayaran)
                                    {{ ucfirst(strtolower($pembayaran->metode_pembayaran)) }}
                                @else
                                    Manual
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    <tr class="bg-yellow">
                        <td colspan="3" class="text-right bold">TOTAL PEMBAYARAN</td>
                        <td class="text-right bold">{{ number_format($grandTotal, 0, ',', '.') }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tbody>
            </table>

            {{-- Rekening Sekolah --}}
            <div style="background: #f5f9ff; padding: 10px; border: 1px solid #bbdefb; border-radius: 4px; margin: 10px 0; font-size: 11px;">
                <strong>Rekening Resmi Sekolah:</strong><br>
                @foreach ($banksekolah as $bank)
                    • {{ $bank->nama_bank }} a/n {{ $bank->nama_rekening }} – {{ $bank->nomor_rekening }}<br>
                @endforeach
            </div>

            {{-- Catatan --}}
            <ol style="font-size: 10px; margin-left: 20px; line-height: 1.4;">
                <li>Pembayaran yang telah dikonfirmasi oleh admin dianggap sah dan tidak dapat dikembalikan.</li>
                <li>Invoice ini berlaku sebagai bukti pembayaran resmi dari SMP Muhammadiyah Larangan.</li>
                <li>Untuk pertanyaan lebih lanjut, silakan hubungi bagian keuangan sekolah.</li>
            </ol>

            {{-- Tanda Tangan --}}
            <div class="signature">
                <div class="signature-box">
                    <p style="margin: 0; font-size: 11px;">Hormat Kami,</p>
                    <div class="signature-line">Bendahara Sekolah</div>
                </div>
            </div>

            {{-- PERINGATAN HUKUM --}}
            <div class="legal-warning">
                <strong>PERINGATAN :</strong> Dokumen ini diterbitkan oleh SMP Muhammadiyah Larangan. Setiap bentuk pemalsuan, perubahan, atau penyalahgunaan invoice ini dilarang dan merupakan tindak pidana sesuai Pasal 263 &amp; 264 KUHP serta Pasal 35 jo. Pasal 51 UU ITE.
            </div>

            {{-- Footer --}}
            <div class="footer-note">
                <p style="margin: 4px 0;">Sistem Pembayaran Online (Simponi) – SMP Muhammadiyah Larangan</p>
                <p style="margin: 2px 0 0;">© {{ date('Y') }} – Semua hak dilindungi</p>
            </div>
        </div>
    </div>
</body>
</html>
