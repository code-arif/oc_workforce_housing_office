<?php

namespace App\Http\Controllers\Web\Backend;

use App\Models\Bed;
use App\Models\Room;
use App\Models\Lease;
use App\Models\Property;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;

class DashboardController extends Controller
{
    /**
     * Display the dashboard view.
     */
    public function index()
    {
        // Get all active properties with their stats
        $properties = Property::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($property) {
                return $this->getPropertyStats($property);
            });

        // Get pending applications (tenants with pending status)
        $pendingApplications = Tenant::with('profile')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get unsigned leases (leases pending tenant signature)
        $unsignedLeases = Lease::with(['tenant.profile', 'property'])
            ->where('status', 'PENDING_TENANT_SIGN')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get recent tenants (active tenants)
        $recentTenants = Tenant::with(['profile', 'leases.property', 'leases.assignments.bed.room.unit'])
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Calculate totals across all properties
        $totals = [
            'total_properties' => $properties->count(),
            'total_beds' => $properties->sum('total_beds'),
            'available_beds' => $properties->sum('available_beds'),
            'occupied_beds' => $properties->sum('occupied_beds'),
        ];

        // maintanance list
        // $maintanance = MaintenanceRequest::

        return view('backend.layouts.dashboard', compact(
            'properties',
            'pendingApplications',
            'unsignedLeases',
            'recentTenants',
            'totals'
        ));
    }

    /**
     * Get stats for a single property
     */
    private function getPropertyStats($property)
    {
        // Get all unit IDs for this property
        $unitIds = $property->units()->pluck('id');

        // Get all room IDs for these units
        $roomIds = Room::whereIn('unit_id', $unitIds)->pluck('id');

        // Get bed stats
        $totalBeds = Bed::whereIn('room_id', $roomIds)->count();
        $occupiedBeds = Bed::whereIn('room_id', $roomIds)->where('is_occupied', true)->count();
        $availableBeds = $totalBeds - $occupiedBeds;

        // Get unit and room counts
        $totalUnits = $unitIds->count();
        $totalRooms = $roomIds->count();

        // Get active leases count
        $activeLeases = Lease::where('property_id', $property->id)
            ->where('status', 'ACTIVE')
            ->get();

        // Calculate occupancy rate
        $occupancyRate = $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100, 1) : 0;
        // Calculate totals
        $totalRent = 0;
        $totalPaid = 0;
        $totalDue = 0;
        foreach ($activeLeases as $lease) {
            // Get all invoices for this lease (including soft deleted if needed)
            $invoices = $lease->invoices()
                ->whereNull('deleted_at') // Only non-deleted invoices
                ->get();

            foreach ($invoices as $invoice) {
                $totalRent += $invoice->total_amount;
                $totalPaid += $invoice->paid_amount ?? 0;
                $totalDue += ($invoice->total_amount - ($invoice->paid_amount ?? 0));

            }
        }

        return (object) [
            'id' => $property->id,
            'name' => $property->name,
            'address' => $property->address,
            'image_path' => $property->image_path,
            'total_units' => $totalUnits,
            'total_rooms' => $totalRooms,
            'total_beds' => $totalBeds,
            'occupied_beds' => $occupiedBeds,
            'available_beds' => $availableBeds,
            'active_leases' => $activeLeases->count(),
            'occupancy_rate' => $occupancyRate,
            'total_rent' => $totalRent ?? 0,
            'total_paid' => $totalPaid ?? 0,
            'total_due' => $totalDue ?? 0,
        ];
    }

    /**
     * Dashboard chart status
     */
    public function getDashboardData()
    {
        // Completed vs Incomplete Works
        $workCompletion = [
            'completed' => DB::table('works')->where('is_completed', true)->count(),
            'incomplete' => DB::table('works')->where('is_completed', false)->count(),
        ];

        // Top Teams by Work Assigned (last 30 days)
        $topTeams = DB::table('works')
            ->join('teams', 'works.team_id', '=', 'teams.id')
            ->selectRaw('teams.name, COUNT(works.id) as work_count')
            ->where('works.created_at', '>=', now()->subDays(30))
            ->whereNotNull('works.team_id')
            ->groupBy('teams.id', 'teams.name')
            ->orderByDesc('work_count')
            ->limit(5)
            ->get()
            ->mapWithKeys(fn($item) => [$item->name => $item->work_count]);

        return response()->json([
            'work_completion' => $workCompletion,
            'top_teams_by_work' => $topTeams,
        ]);
    }
}
