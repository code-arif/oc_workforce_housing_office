<?php

namespace App\Exports;

use App\Models\ActivityLog;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ActivityLogsExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = ActivityLog::with('user:id,name')->orderBy('created_at', 'desc');

        if (!empty($this->filters['user_id'])) {
            $query->where('user_id', $this->filters['user_id']);
        }
        if (!empty($this->filters['module'])) {
            $query->where('module', $this->filters['module']);
        }
        if (!empty($this->filters['action'])) {
            $query->where('action', $this->filters['action']);
        }
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'User',
            'Role',
            'Action',
            'Module',
            'Route',
            'Method',
            'IP Address',
            'Timestamp',
        ];
    }

    public function map($log): array
    {
        return [
            $log->id,
            $log->user ? $log->user->name : 'Guest',
            $log->role,
            $log->action,
            $log->module,
            $log->route,
            $log->method,
            $log->ip_address,
            $log->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
