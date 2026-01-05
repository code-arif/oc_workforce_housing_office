<?php

namespace App\Http\Controllers\Api\Tenants;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TenantDashboardController extends Controller
{
    /**
     * Tenant Dashboard Stats and data
     */
    public function dashboard(Request $request)
    {
        $tenant = $request->user();

        $tenant->load('profile');

        return response()->json([
            'success' => true,
            'data' => [
                'tenant' => [
                    'id' => $tenant->id,
                    'email' => $tenant->email,
                    'status' => $tenant->status,
                    'move_in_date' => $tenant->move_in_date,
                    'profile' => $tenant->profile,
                ],
                'statistics' => [
                    'documents_count' => $tenant->documents()->count(),
                    'emergency_contacts_count' => $tenant->emergencyContacts()->count(),
                ]
            ]
        ]);
    }
}
