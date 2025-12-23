<?php

namespace App\Http\Controllers\Web\Backend;

use App\Models\Team;
use App\Models\User;
use App\Models\Work;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Display the dashboard view.
     */
    public function index()
    {
        $user = auth()->user();

        // User Statistics
        $totalEmployees = User::where('role', 'employee')->count();

        // Work Statistics
        $totalWorks = Work::count();
        $rescheduledWorks = Work::where('is_rescheduled', true)->count();

        // Team Statistics
        $totalTeams = Team::count();

        return view('backend.layouts.dashboard', compact(
            'totalEmployees',
            'totalWorks',
            'rescheduledWorks',
            'totalTeams',
        ));
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
