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

class RentCollectionReportExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle, WithEvents
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
            $isVoid = (strtolower($row['review_status']) === 'void');
            $rows[] = [
                $row['payment_number'],
                $row['property_name'],
                $row['bed_label'],
                $row['tenant_name'],
                $row['payment_date'],
                $isVoid ? '$' . $row['amount'] . ' (VOID)' : '$' . $row['amount'],
                $row['payment_method'],
                $row['stripe_method'],
                $row['stripe_amount'],
                '$' . ($row['stripe_fees'] ?? '0.00'),
                $row['reference_number'],
                $row['invoice_number'],
                $row['review_status'],
                $row['reviewed_by'],
                $row['reviewed_at'],
            ];
        }

        // Empty row before summary
        $rows[] = array_fill(0, 15, '');

        // Summary section
        $rows[] = ['', '', '', '', 'COLLECTION SUMMARY', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', 'Total Payments:', $this->summary['total_payments'], '', '', '', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', 'Total Collected:', '$' . number_format($this->summary['total_collected'], 2), '', '', '', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', 'Total Stripe Fees:', '$' . number_format($this->summary['total_stripe_fees'] ?? 0, 2), '', '', '', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', 'Confirmed Amount:', '$' . number_format($this->summary['confirmed_total'], 2), '', '', '', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', 'Pending Review:', '$' . number_format($this->summary['pending_total'], 2), '', '', '', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', 'Disputed Amount:', '$' . number_format($this->summary['disputed_total'], 2), '', '', '', '', '', '', '', '', ''];

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Payment #',
            'Property Name',
            'Bed',
            'Tenant',
            'Payment Date',
            'Amount',
            'Method',
            'Stripe Method',
            'Stripe Amount ($)',
            'Stripe Fees ($)',
            'Reference #',
            'Invoice #',
            'Review Status',
            'Reviewed By',
            'Reviewed At',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,  // Payment #
            'B' => 22,  // Property Name
            'C' => 12,  // Bed
            'D' => 22,  // Tenant
            'E' => 14,  // Payment Date
            'F' => 14,  // Amount
            'G' => 14,  // Method
            'H' => 14,  // Stripe Method
            'I' => 16,  // Stripe Amount
            'J' => 14,  // Stripe Fees
            'K' => 15,  // Reference #
            'L' => 15,  // Invoice #
            'M' => 14,  // Review Status
            'N' => 16,  // Reviewed By
            'O' => 18,  // Reviewed At
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
                    'startColor' => ['rgb' => '1a5f7a'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Money column right-aligned
            'F' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]],
            'I' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]],
            'J' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]],
        ];
    }

    public function title(): string
    {
        return 'Rent Collection Report';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastDataRow = count($this->data) + 1;
                $summaryStartRow = $lastDataRow + 2;

                // Add borders to data rows
                $sheet->getStyle('A1:O' . $lastDataRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC'],
                        ],
                    ],
                ]);

                // Style alternating rows with light background
                for ($i = 2; $i <= $lastDataRow; $i++) {
                    if ($i % 2 == 0) {
                        $sheet->getStyle('A' . $i . ':O' . $i)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F0F8FF'],
                            ],
                        ]);
                    }

                    // Color code review status
                    $cell = $sheet->getCell('M' . $i);
                    $status = strtolower($cell->getValue());
                    
                    $statusColors = [
                        'confirmed' => '28a745',
                        'reviewed' => '17a2b8',
                        'pending' => 'ffc107',
                        'disputed' => 'dc3545',
                        'void' => 'dc3545',
                    ];
                    
                    if (isset($statusColors[$status])) {
                        $sheet->getStyle('M' . $i)->applyFromArray([
                            'font' => [
                                'color' => ['rgb' => $status === 'pending' ? '000000' : 'FFFFFF'],
                                'bold' => true,
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => $statusColors[$status]],
                            ],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                    }

                    if ($status === 'void') {
                        $sheet->getStyle('A' . $i . ':O' . $i)->getFont()->setStrikethrough(true);
                        $sheet->getStyle('A' . $i . ':O' . $i)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('dc3545'));
                    }
                }

                // Style summary section header - green background
                $sheet->getStyle('E' . $summaryStartRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '1a5f7a'],
                    ],
                ]);

                // Style summary labels and values
                for ($i = $summaryStartRow + 1; $i <= $summaryStartRow + 6; $i++) {
                    $sheet->getStyle('E' . $i)->applyFromArray([
                        'font' => ['bold' => true],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                    ]);
                    $sheet->getStyle('F' . $i)->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                    ]);
                }

                // Add summary borders
                $sheet->getStyle('E' . $summaryStartRow . ':F' . ($summaryStartRow + 6))->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '1a5f7a'],
                        ],
                    ],
                ]);

                // Freeze header row
                $sheet->freezePane('A2');
            },
        ];
    }
}
