<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PropertyReportExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $data;
    protected $summary;
    protected $filters;

    public function __construct(array $data, array $summary, array $filters = [])
    {
        $this->data = $data;
        $this->summary = $summary;
        $this->filters = $filters;
    }

    public function array(): array
    {
        $rows = [];

        // Data rows
        foreach ($this->data as $row) {
            $rows[] = [
                $row['property_name'],
                $row['bed_label'],
                $row['tenant_name'],
                '$' . $row['total_due'],
                '$' . $row['total_paid'],
                '$' . $row['balance_owed'],
            ];
        }

        // Empty row before summary
        $rows[] = ['', '', '', '', '', ''];

        // Summary rows
        $rows[] = ['', '', 'SUMMARY', '', '', ''];
        $rows[] = ['', '', 'Total Leases:', $this->summary['total_leases'], '', ''];
        $rows[] = ['', '', 'Total Due:', '$' . number_format($this->summary['total_due'], 2), '', ''];
        $rows[] = ['', '', 'Total Paid:', '$' . number_format($this->summary['total_paid'], 2), '', ''];
        $rows[] = ['', '', 'Balance Owed:', '$' . number_format($this->summary['total_balance'], 2), '', ''];

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Property Name',
            'Bed Label',
            'Tenant Name',
            'Total Due',
            'Total Paid',
            'Balance Owed',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 15,
            'C' => 25,
            'D' => 15,
            'E' => 15,
            'F' => 15,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Header row styling - dark blue background with white text
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2c3e50'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Money columns right-aligned
            'D' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]],
            'E' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]],
            'F' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]],
        ];
    }

    public function title(): string
    {
        return 'Property Report';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastDataRow = count($this->data) + 1;
                $summaryStartRow = $lastDataRow + 2;

                // Add borders to data rows
                $sheet->getStyle('A1:F' . $lastDataRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC'],
                        ],
                    ],
                ]);

                // Style alternating rows with light gray background
                for ($i = 2; $i <= $lastDataRow; $i++) {
                    if ($i % 2 == 0) {
                        $sheet->getStyle('A' . $i . ':F' . $i)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F8F9FA'],
                            ],
                        ]);
                    }
                }

                // Style summary section header - gold/yellow background
                $sheet->getStyle('C' . $summaryStartRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9A600'],
                    ],
                ]);

                // Bold summary labels and values
                for ($i = $summaryStartRow + 1; $i <= $summaryStartRow + 4; $i++) {
                    $sheet->getStyle('C' . $i)->applyFromArray([
                        'font' => ['bold' => true],
                    ]);
                    $sheet->getStyle('D' . $i)->applyFromArray([
                        'font' => ['bold' => true],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                    ]);
                }

                // Freeze the header row so it stays visible when scrolling
                $sheet->freezePane('A2');

                // Set row height for header
                $sheet->getRowDimension(1)->setRowHeight(25);
            },
        ];
    }
}
