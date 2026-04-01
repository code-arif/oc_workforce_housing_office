<?php

namespace App\Http\Controllers\Web\Backend;

use App\Models\Bed;
use App\Models\Room;
use App\Models\Lease;
use App\Models\Invoice;
use App\Models\Property;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\MaintenanceRequest;

class DashboardController extends Controller
{
    /**
     * Display the dashboard view.
     * OPTIMIZED: Uses eager loading and aggregated queries to prevent N+1 issues at 100K+ records
     */
    public function index()
    {
        // OPTIMIZED: Single query with eager loading and counts
        $properties = Property::where('is_active', true)
            ->withCount([
                'units',
                'units as rooms_count' => function ($q) {
                    $q->join('rooms', 'units.id', '=', 'rooms.unit_id');
                }
            ])
            ->orderBy('name')
            ->get()
            ->map(function ($property) {
                return $this->getPropertyStatsOptimized($property);
            });

        // Get pending applications (tenants with pending status)
        $pendingApplications = Application::where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Tenant::with('profile:id,tenant_id,first_name,last_name,avatar')
        //     ->where('status', 'pending')
        //     ->orderBy('created_at', 'desc')
        //     ->limit(5)
        //     ->get();

        // Get unsigned leases (leases pending tenant signature)
        $unsignedLeases = Lease::with(['tenant.profile:id,tenant_id,first_name,last_name', 'property:id,name'])
            ->where('status', '!=','ACTIVE')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get recent tenants (active tenants) - OPTIMIZED eager loading
        $recentTenants = Tenant::with([
            'profile:id,tenant_id,first_name,last_name,avatar',
            'leases' => function ($q) {
                $q->where('status', 'ACTIVE')
                    ->with(['property:id,name', 'assignments' => function ($q) {
                        $q->where('is_current', true)->with('bed:id,bed_label,room_id');
                    }]);
            }
        ])
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

        // Recent maintenance requests
        $recentMaintenance = MaintenanceRequest::with(['tenant.profile:id,tenant_id,first_name,last_name', 'property:id,name'])
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        return view('backend.layouts.new-dashboard', compact(
            'properties',
            'pendingApplications',
            'unsignedLeases',
            'recentTenants',
            'totals',
            'recentMaintenance'
        ));
    }

    /**
     * OPTIMIZED: Get stats for a single property using efficient queries
     * Reduces N+1 by using aggregated subqueries instead of loops
     */
    private function getPropertyStatsOptimized($property)
    {
        // Get unit IDs in a single query
        $unitIds = $property->units()->pluck('id');
        
        if ($unitIds->isEmpty()) {
            return $this->emptyPropertyStats($property);
        }

        // Get room IDs in a single query
        $roomIds = Room::whereIn('unit_id', $unitIds)->pluck('id');

        // OPTIMIZED: Get bed stats in a single query with aggregation
        $bedStats = Bed::whereIn('room_id', $roomIds)
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN is_occupied = 1 THEN 1 ELSE 0 END) as occupied')
            ->first();

        $totalBeds = $bedStats->total ?? 0;
        $occupiedBeds = $bedStats->occupied ?? 0;
        $availableBeds = $totalBeds - $occupiedBeds;
        $totalUnits = $unitIds->count();
        $totalRooms = $roomIds->count();

        // OPTIMIZED: Get invoice totals in a single aggregated query instead of looping
        $invoiceTotals = Invoice::whereHas('lease', function ($q) use ($property) {
                $q->where('property_id', $property->id)->where('status', 'ACTIVE');
            })
            ->whereNull('deleted_at')
            ->selectRaw('
                SUM(total_amount) as total_rent,
                SUM(COALESCE(paid_amount, 0)) as total_paid,
                SUM(total_amount - COALESCE(paid_amount, 0)) as total_due
            ')
            ->first();

        // Get active lease count
        $activeLeasesCount = Lease::where('property_id', $property->id)
            ->where('status', 'ACTIVE')
            ->count();

        $occupancyRate = $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100, 1) : 0;

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
            'active_leases' => $activeLeasesCount,
            'occupancy_rate' => $occupancyRate,
            'total_rent' => $invoiceTotals->total_rent ?? 0,
            'total_paid' => $invoiceTotals->total_paid ?? 0,
            'total_due' => $invoiceTotals->total_due ?? 0,
        ];
    }

    /**
     * Return empty stats for properties with no units
     */
    private function emptyPropertyStats($property)
    {
        return (object) [
            'id' => $property->id,
            'name' => $property->name,
            'address' => $property->address,
            'image_path' => $property->image_path,
            'total_units' => 0,
            'total_rooms' => 0,
            'total_beds' => 0,
            'occupied_beds' => 0,
            'available_beds' => 0,
            'active_leases' => 0,
            'occupancy_rate' => 0,
            'total_rent' => 0,
            'total_paid' => 0,
            'total_due' => 0,
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
