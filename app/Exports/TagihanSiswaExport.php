<?php

namespace App\Exports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class TagihanSiswaExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        $sheets = [];
        $kelasList = ['VII', 'VIII', 'IX'];

        foreach ($kelasList as $kelas) {
            $siswas = Siswa::with(['tagihan.tagihanDetails', 'tagihan.pembayaran'])
                ->where('kelas', $kelas)
                ->orderBy('nama', 'asc')
                ->get();

            $sheets[] = new KelasSheet($kelas, $siswas);
        }

        return $sheets;
    }
}

class KelasSheet implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    protected $kelas;
    protected $siswas;

    public function __construct($kelas, $siswas)
    {
        $this->kelas = $kelas;
        $this->siswas = $siswas;
    }

    public function collection()
    {
        $data = [];
        $no = 1;

        foreach ($this->siswas as $siswa) {
            // Baris utama siswa
            $data[] = [
                'No' => $no++,
                'Nama Siswa' => $siswa->nama,
                'NISN' => $siswa->nisn,
                'Kelas' => $siswa->kelas,
                'Angkatan' => $siswa->angkatan,
                'Status Tagihan' => '',
                'Tanggal Tagihan' => '',
                'Jenis Biaya' => '',
                'Jumlah Biaya' => '',
                'Denda' => '',
                'Total Bayar' => '',
                'Status Pembayaran' => '',
                'Tanggal Bayar' => '',
                'Metode Pembayaran' => '',
                'Catatan' => ''
            ];

            // Jika tidak ada tagihan
            if ($siswa->tagihan->isEmpty()) {
                $data[] = [
                    'No' => '',
                    'Nama Siswa' => '',
                    'NISN' => '',
                    'Kelas' => '',
                    'Angkatan' => '',
                    'Status Tagihan' => 'Tidak ada tagihan',
                    'Tanggal Tagihan' => '',
                    'Jenis Biaya' => '',
                    'Jumlah Biaya' => '',
                    'Denda' => '',
                    'Total Bayar' => '',
                    'Status Pembayaran' => '',
                    'Tanggal Bayar' => '',
                    'Metode Pembayaran' => '',
                    'Catatan' => ''
                ];
            } else {
                foreach ($siswa->tagihan as $tagihan) {
                    $totalBayar = 0;
                    $tanggalBayar = '';
                    $metodePembayaran = '';
                    $catatan = $tagihan->keterangan ?? '';

                    if ($tagihan->status == 'lunas') {
                        $pembayaran = $tagihan->pembayaran->first();
                        if ($pembayaran) {
                            $totalBayar = $pembayaran->jumlah_dibayar;
                            $tanggalBayar = $pembayaran->tanggal_bayar
                                ? \Carbon\Carbon::parse($pembayaran->tanggal_bayar)->format('d/m/Y H:i')
                                : '';
                            $metodePembayaran = $pembayaran->metode_pembayaran ?? '';
                        }
                    }

                    foreach ($tagihan->tagihanDetails as $detail) {
                        $data[] = [
                            'No' => '',
                            'Nama Siswa' => '',
                            'NISN' => '',
                            'Kelas' => '',
                            'Angkatan' => '',
                            'Status Tagihan' => $tagihan->status,
                            'Tanggal Tagihan' => $tagihan->tanggal_tagihan
                                ? \Carbon\Carbon::parse($tagihan->tanggal_tagihan)->format('d/m/Y')
                                : '',
                            'Jenis Biaya' => $detail->nama_biaya,
                            'Jumlah Biaya' => $detail->jumlah_biaya,
                            'Denda' => $tagihan->denda ?? 0,
                            'Total Bayar' => $totalBayar,
                            'Status Pembayaran' => $tagihan->status,
                            'Tanggal Bayar' => $tanggalBayar,
                            'Metode Pembayaran' => $metodePembayaran,
                            'Catatan' => $catatan
                        ];
                    }
                }
            }

            // Baris pemisah antar siswa
            $data[] = array_fill_keys([
                'No', 'Nama Siswa', 'NISN', 'Kelas', 'Angkatan', 'Status Tagihan',
                'Tanggal Tagihan', 'Jenis Biaya', 'Jumlah Biaya', 'Denda',
                'Total Bayar', 'Status Pembayaran', 'Tanggal Bayar', 'Metode Pembayaran', 'Catatan'
            ], '');
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'No', 'Nama Siswa', 'NISN', 'Kelas', 'Angkatan', 'Status Tagihan',
            'Tgl. Tagihan', 'Jenis Biaya', 'Jumlah Biaya (Rp)', 'Denda (Rp)',
            'Total Bayar (Rp)', 'Status Pembayaran', 'Tgl. Bayar', 'Metode Pembayaran', 'Catatan'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header kolom
        $sheet->getStyle('A5:O5')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Style isi data
        $data = $this->collection();
        $rowNumber = 6;

        foreach ($data as $row) {
            if ($row['Nama Siswa'] !== '') {
                $sheet->getStyle("A{$rowNumber}:O{$rowNumber}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EAF1FB']],
                ]);
            }

            $sheet->getStyle("A{$rowNumber}:O{$rowNumber}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_HAIR,
                        'color' => ['rgb' => 'AAAAAA'],
                    ],
                ],
            ]);

            $rowNumber++;
        }

        // Ukuran font keseluruhan
        $sheet->getStyle('A1:O' . $rowNumber)->getFont()->setSize(10);

        // Format angka dan tanggal
        $sheet->getStyle("I6:K{$rowNumber}")
            ->getNumberFormat()
            ->setFormatCode('#,##0;[Red]-#,##0');
        $sheet->getStyle("G6:G{$rowNumber}")
            ->getNumberFormat()
            ->setFormatCode('dd/mm/yyyy');
        $sheet->getStyle("M6:M{$rowNumber}")
            ->getNumberFormat()
            ->setFormatCode('dd/mm/yyyy hh:mm');
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5, 'B' => 25, 'C' => 15, 'D' => 8, 'E' => 10,
            'F' => 15, 'G' => 12, 'H' => 25, 'I' => 15, 'J' => 12,
            'K' => 15, 'L' => 18, 'M' => 18, 'N' => 20, 'O' => 25,
        ];
    }

    public function registerEvents(): array
{
    return [
        AfterSheet::class => function (AfterSheet $event) {
            $sheet = $event->sheet->getDelegate();
            $sheet->setTitle($this->kelas);

            // === HEADER SEKOLAH & JUDUL LAPORAN ===
            $sheet->insertNewRowBefore(1, 6); // Sisipkan 6 baris di atas data

            $sheet->mergeCells('A1:O1');
            $sheet->setCellValue('A1', 'SMP MUHAMMADIYAH LARANGAN');
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1F4E78']],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            $sheet->mergeCells('A2:O2');
            $sheet->setCellValue('A2', 'Jl. Raya Larangan No. 23, Brebes, Jawa Tengah | Telp. (0283) 456789');
            $sheet->getStyle('A2')->applyFromArray([
                'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '555555']],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            $sheet->mergeCells('A3:O3');
            $sheet->setCellValue('A3', 'LAPORAN TAGIHAN SISWA - KELAS ' . strtoupper($this->kelas));
            $sheet->getStyle('A3')->applyFromArray([
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '305496'],
                ],
            ]);

            // === HEADER TABEL (sekarang di baris 7) ===
            $sheet->getStyle('A7:O7')->applyFromArray([
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);
            $sheet->getRowDimension(7)->setRowHeight(25);

            // === FOOTER ===
            $lastRow = $sheet->getHighestRow() + 2;
            $sheet->mergeCells("A{$lastRow}:O{$lastRow}");
            $sheet->setCellValue("A{$lastRow}", 'Simponi SMP Muhammadiyah Larangan');
            $sheet->getStyle("A{$lastRow}")->applyFromArray([
                'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '888888']],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
        },
    ];
}


}
