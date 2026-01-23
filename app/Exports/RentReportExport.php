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

class RentReportExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle, WithEvents
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
                $row['due_date'],
                '$' . $row['outstanding_amount'],
                $row['notes'],
                $row['invoice_number'],
            ];
        }

        // Empty row before summary
        $rows[] = ['', '', '', '', '', '', ''];

        // Summary rows
        $rows[] = ['', '', '', 'SUMMARY', '', '', ''];
        $rows[] = ['', '', '', 'Total Invoices:', $this->summary['total_invoices'], '', ''];
        $rows[] = ['', '', '', 'Total Outstanding:', '$' . number_format($this->summary['total_outstanding'], 2), '', ''];

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Property Name',
            'Beds',
            'Tenant',
            'Due Date',
            'Outstanding Amount',
            'Note',
            'Invoice No.',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 15,
            'C' => 25,
            'D' => 15,
            'E' => 18,
            'F' => 30,
            'G' => 15,
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
            'E' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]],
        ];
    }

    public function title(): string
    {
        return 'Rent Report';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastDataRow = count($this->data) + 1;
                $summaryStartRow = $lastDataRow + 2;

                // Add borders to data rows
                $sheet->getStyle('A1:G' . $lastDataRow)->applyFromArray([
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
                        $sheet->getStyle('A' . $i . ':G' . $i)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F8F9FA'],
                            ],
                        ]);
                    }
                }

                // Style summary section header - gold/yellow background
                $sheet->getStyle('D' . $summaryStartRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9A600'],
                    ],
                ]);

                // Style summary labels
                for ($i = $summaryStartRow + 1; $i <= $summaryStartRow + 2; $i++) {
                    $sheet->getStyle('D' . $i)->applyFromArray([
                        'font' => ['bold' => true],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                    ]);
                }

                // Freeze header row
                $sheet->freezePane('A2');
            },
        ];
    }
}
