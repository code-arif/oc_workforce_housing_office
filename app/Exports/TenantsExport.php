<?php

namespace App\Exports;

use App\Models\Tenant;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TenantsExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $query = Tenant::query()
            ->with([
                'profile:id,tenant_id,first_name,middle_name,last_name,phone',
                'leases' => function ($q) {
                    $q->select('id', 'tenant_id', 'property_id', 'status', 'start_date', 'end_date', 'rent_amount')
                        ->whereNull('deleted_at')
                        ->with([
                            'property:id,name',
                            'assignments' => function ($a) {
                                $a->select('id', 'lease_id', 'bed_id', 'is_current')
                                    ->where('is_current', true)
                                    ->whereNull('deleted_at')
                                    ->with('bed:id,bed_label')
                                    ->limit(1);
                            }
                        ]);
                }
            ])
            ->orderBy('id', 'desc');

        // Apply same filters as getData()
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['account_status'])) {
            if ($this->filters['account_status'] === 'active') {
                $query->whereHas('leases', fn($q) => $q->where('status', 'ACTIVE')->whereNull('deleted_at'));
            } else {
                $query->whereDoesntHave('leases', fn($q) => $q->where('status', 'ACTIVE')->whereNull('deleted_at'));
            }
        }

        if (!empty($this->filters['property_id'])) {
            $pid = $this->filters['property_id'];
            $query->whereHas('leases', fn($q) => $q->where('property_id', $pid)->whereNull('deleted_at'));
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        if (!empty($this->filters['tenant'])) {
            $keyword = $this->filters['tenant'];
            $query->whereHas('profile', function ($q) use ($keyword) {
                $q->where(\DB::raw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', COALESCE(last_name, ''))"), 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        // Apply tab filter (under_review)
        if (!empty($this->filters['tab']) && $this->filters['tab'] === 'under_review') {
            $query->whereIn('status', ['pending', 'processing', 'under_review']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            '#',
            'Full Name',
            'Email',
            'Phone',
            'Property',
            'Bed',
            'Monthly Rent ($)',
            'Account Status',
            'Tenant Status',
            'Application Source',
            'Lease Start',
            'Lease End',
            'Joined Date',
        ];
    }

    public function map($tenant): array
    {
        static $index = 0;
        $index++;

        $profile    = $tenant->profile;
        $activeLease = $tenant->leases->where('status', 'ACTIVE')->first();

        $fullName = $profile
            ? trim($profile->first_name . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''))
            : 'N/A';

        $hasActiveLease = $tenant->leases->where('status', 'ACTIVE')->isNotEmpty();

        return [
            $index,
            $fullName,
            $tenant->email,
            $profile->phone ?? 'N/A',
            $activeLease?->property?->name ?? 'No Active Lease',
            $activeLease?->assignments?->first()?->bed?->bed_label ?? 'N/A',
            $activeLease ? number_format($activeLease->rent_amount, 2) : '0.00',
            $hasActiveLease ? 'Active' : 'Inactive',
            ucfirst($tenant->status),
            ucfirst($tenant->application_source ?? 'N/A'),
            $activeLease ? date('M d, Y', strtotime($activeLease->start_date)) : 'N/A',
            $activeLease ? date('M d, Y', strtotime($activeLease->end_date)) : 'N/A',
            $tenant->created_at->format('M d, Y'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();
        $lastCol = $sheet->getHighestColumn();

        // Header row style
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 11,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'], // Indigo
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Header row height
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Data rows — zebra striping
        for ($row = 2; $row <= $lastRow; $row++) {
            $color = ($row % 2 === 0) ? 'F3F4F6' : 'FFFFFF';
            $sheet->getStyle("A{$row}:M{$row}")->applyFromArray([
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $color],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
        }

        // Border for entire table
        $sheet->getStyle("A1:M{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        // Center specific columns
        $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // #
        $sheet->getStyle("G2:G{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);  // Rent
        $sheet->getStyle("H2:H{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Account status
        $sheet->getStyle("I2:I{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Tenant status

        return [];
    }

    public function title(): string
    {
        return 'Tenants';
    }
}
