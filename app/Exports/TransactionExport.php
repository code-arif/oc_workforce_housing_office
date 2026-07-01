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
use PhpOffice\PhpSpreadsheet\Style\Color;

class TransactionExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $data;
    protected $summary;

    public function __construct(array $data, array $summary)
    {
        $this->data = $data;
        $this->summary = $summary;
    }

    public function array(): array
    {
        $rows = [];

        // Data rows
        foreach ($this->data as $row) {
            $rows[] = [
                $row['payment_number'],
                $row['payment_date'],
                $row['tenant_name'],
                $row['tenant_email'],
                $row['property_name'],
                $row['amount'],
                $row['payment_method'],
                $row['payment_type'],
                $row['invoice_number'],
                $row['paid_by'],
                $row['reference_number'],
                $row['gateway_transaction_id'],
                $row['processing_fee'],
                $row['total_charged'],
                $row['review_status'],
                $row['status'],
                $row['note'] ?? '',
            ];
        }

        // Empty row separator
        if (!empty($this->data)) {
            $rows[] = array_fill(0, 17, '');
        }

        // Summary section
        $rows[] = ['', '', '', '', '', 'TRANSACTION SUMMARY', '', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', 'Total Active Payments:', $this->summary['total_active_payments'] ?? 0, '', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', 'Total Collected Amount:', '$' . number_format($this->summary['total_active_amount'] ?? 0, 2), '', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', 'Total Processing Fees:', '$' . number_format($this->summary['total_processing_fees'] ?? 0, 2), '', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', 'Net Collected:', '$' . number_format(max(0, ($this->summary['total_active_amount'] ?? 0) - ($this->summary['total_processing_fees'] ?? 0)), 2), '', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', 'Pending Review:', $this->summary['pending_review'] ?? 0, ' ($' . number_format($this->summary['pending_amount'] ?? 0, 2) . ')', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', 'Confirmed:', $this->summary['confirmed'] ?? 0, ' ($' . number_format($this->summary['confirmed_amount'] ?? 0, 2) . ')', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', 'Voided:', $this->summary['total_voided'] ?? 0, ' ($' . number_format($this->summary['total_voided_amount'] ?? 0, 2) . ')', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', 'Disputed:', $this->summary['disputed'] ?? 0, ' ($' . number_format($this->summary['disputed_amount'] ?? 0, 2) . ')', '', '', '', '', '', '', '', '', ''];

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Payment #',
            'Payment Date',
            'Tenant',
            'Tenant Email',
            'Property',
            'Amount ($)',
            'Method',
            'Type',
            'Invoice #',
            'Paid By',
            'Reference #',
            'Gateway ID',
            'Processing Fee ($)',
            'Total Charged ($)',
            'Review Status',
            'Record Status',
            'Notes',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 16,  // Payment #
            'B' => 14,  // Payment Date
            'C' => 22,  // Tenant
            'D' => 26,  // Tenant Email
            'E' => 20,  // Property
            'F' => 14,  // Amount
            'G' => 14,  // Method
            'H' => 12,  // Type
            'I' => 16,  // Invoice #
            'J' => 10,  // Paid By
            'K' => 16,  // Reference #
            'L' => 24,  // Gateway ID
            'M' => 16,  // Processing Fee
            'N' => 16,  // Total Charged
            'O' => 16,  // Review Status
            'P' => 14,  // Record Status
            'Q' => 24,  // Notes
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Header row
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
            // Money columns right-aligned
            'F' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]],
            'M' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]],
            'N' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]],
        ];
    }

    public function title(): string
    {
        return 'Transaction Report';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastDataRow = count($this->data) + 1; // +1 for header row
                $summaryStartRow = $lastDataRow + 2;

                // Add borders to data rows
                if ($lastDataRow > 1) {
                    $sheet->getStyle('A1:Q' . $lastDataRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'CCCCCC'],
                            ],
                        ],
                    ]);

                    // Alternating row colors & conditional formatting
                    for ($i = 2; $i <= $lastDataRow; $i++) {
                        if ($i % 2 == 0) {
                            $sheet->getStyle('A' . $i . ':Q' . $i)->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'F0F8FF'],
                                ],
                            ]);
                        }

                        // Color-code the status column
                        $reviewStatus = strtolower($sheet->getCell('O' . $i)->getValue());
                        $recordStatus = strtolower($sheet->getCell('P' . $i)->getValue());

                        // If voided, strikethrough the whole row in red
                        if ($recordStatus === 'voided') {
                            $sheet->getStyle('A' . $i . ':Q' . $i)->getFont()->setStrikethrough(true);
                            $sheet->getStyle('A' . $i . ':Q' . $i)->getFont()->setColor(new Color('dc3545'));
                        }

                        // Color the review status badge
                        $statusColors = [
                            'confirmed' => '28a745',
                            'reviewed' => '17a2b8',
                            'pending' => 'ffc107',
                            'disputed' => 'dc3545',
                            'voided' => 'dc3545',
                        ];

                        $s = $recordStatus === 'voided' ? 'voided' : $reviewStatus;
                        if (isset($statusColors[$s])) {
                            $sheet->getStyle('O' . $i)->applyFromArray([
                                'font' => [
                                    'color' => ['rgb' => $s === 'pending' ? '000000' : 'FFFFFF'],
                                    'bold' => true,
                                ],
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => $statusColors[$s]],
                                ],
                                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                            ]);
                        }
                    }
                }

                // Style summary section
                $sheet->getStyle('F' . $summaryStartRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '1a5f7a'],
                    ],
                ]);

                for ($i = $summaryStartRow + 1; $i <= $summaryStartRow + 10; $i++) {
                    $sheet->getStyle('F' . $i)->applyFromArray([
                        'font' => ['bold' => true],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                    ]);
                    $sheet->getStyle('G' . $i)->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                    ]);
                }

                // Summary border
                $sheet->getStyle('F' . $summaryStartRow . ':H' . ($summaryStartRow + 10))->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '1a5f7a'],
                        ],
                    ],
                ]);

                // Freeze header
                $sheet->freezePane('A2');
            },
        ];
    }
}
