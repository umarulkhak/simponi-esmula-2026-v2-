<?php

namespace App\Exports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithStyles,
    WithColumnWidths,
    WithEvents,
    WithMapping,
    WithTitle,
    WithMultipleSheets
};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\{
    Alignment,
    Fill,
    Border
};

/**
 * Export data siswa dengan sheet terpisah per kelas
 * oleh SMP Muhammadiyah Larangan.
 */
class SiswaExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        $sheets = [];
        $kelasList = Siswa::select('kelas')->distinct()->pluck('kelas');

        foreach ($kelasList as $kelas) {
            $sheets[] = new class($kelas) implements
                FromCollection,
                WithStyles,
                WithColumnWidths,
                WithEvents,
                WithMapping,
                WithTitle
            {
                private $rowIndex = 0;
                private $kelas;

                public function __construct($kelas)
                {
                    $this->kelas = $kelas;
                }

                public function title(): string
                {
                    return 'Kelas ' . $this->kelas;
                }

                public function collection()
                {
                    return Siswa::with(['wali', 'user'])
                        ->where('kelas', $this->kelas)
                        ->orderBy('nama', 'asc')
                        ->get();
                }

                public function map($siswa): array
                {
                    $this->rowIndex++;
                    return [
                        $this->rowIndex,
                        $siswa->nama,
                        $siswa->nisn ?? '-',
                        $siswa->kelas,
                        $siswa->angkatan,
                        $siswa->wali?->name ?? 'Belum ada wali murid',
                        $siswa->user?->name ?? 'Umar Ulihqak',
                        $siswa->wali_status === 'ok' ? 'Sudah Ditautkan' : 'Belum Ditautkan',
                        $siswa->foto ? 'Ada' : 'Tidak Ada',
                    ];
                }

                public function styles(Worksheet $sheet)
                {
                    $headerRow = 3;
                    $firstDataRow = $headerRow + 1;
                    $lastRow = $sheet->getHighestRow();

                    // Header style
                    $sheet->getStyle("A{$headerRow}:I{$headerRow}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '4472C4'],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                        ],
                    ]);

                    // Data style
                    if ($lastRow >= $firstDataRow) {
                        $sheet->getStyle("A{$firstDataRow}:I{$lastRow}")->applyFromArray([
                            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => 'D9D9D9'],
                                ],
                            ],
                        ]);

                        // Alignment spesifik
                        $sheet->getStyle("B{$firstDataRow}:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                        $sheet->getStyle("F{$firstDataRow}:G{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                        $sheet->getStyle("A{$firstDataRow}:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("C{$firstDataRow}:E{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("H{$firstDataRow}:I{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                }

                public function columnWidths(): array
                {
                    return [
                        'A' => 5, 'B' => 30, 'C' => 18, 'D' => 10, 'E' => 12,
                        'F' => 30, 'G' => 25, 'H' => 20, 'I' => 12,
                    ];
                }

                public function registerEvents(): array
                {
                    return [
                        AfterSheet::class => function (AfterSheet $event) {
                            $sheet = $event->sheet->getDelegate();

                            // Judul utama
                            $sheet->setCellValue('A1', 'SMP Muhammadiyah Larangan');
                            $sheet->mergeCells('A1:I1');
                            $sheet->getStyle('A1')->applyFromArray([
                                'font' => ['bold' => true, 'size' => 16],
                                'alignment' => [
                                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                                    'vertical' => Alignment::VERTICAL_CENTER,
                                ],
                            ]);

                            // Subjudul per kelas
                            $sheet->setCellValue('A2', 'Laporan Data Siswa Kelas ' . $this->kelas);
                            $sheet->mergeCells('A2:I2');
                            $sheet->getStyle('A2')->applyFromArray([
                                'font' => ['italic' => true, 'size' => 13],
                                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                            ]);

                            // Header kolom
                            $sheet->fromArray([
                                ['No', 'Nama Siswa', 'NISN', 'Kelas', 'Angkatan', 'Wali Murid', 'Dibuat Oleh', 'Status Wali', 'Foto']
                            ], null, 'A3');

                            // Freeze pane
                            $sheet->freezePane('A4');

                            // Tambahkan footer di bawah tabel
                            $lastRow = $sheet->getHighestRow() + 2;
                            $sheet->setCellValue("A{$lastRow}", 'Jumlah Siswa: ' . ($this->rowIndex));
                            $sheet->mergeCells("A{$lastRow}:I{$lastRow}");
                            $sheet->getStyle("A{$lastRow}")->applyFromArray([
                                'font' => ['italic' => true, 'size' => 11],
                                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                            ]);
                        },
                    ];
                }
            };
        }

        return $sheets;
    }
}
