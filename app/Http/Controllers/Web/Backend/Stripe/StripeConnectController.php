<?php

namespace App\Http\Controllers\Web\Backend\Stripe;

use App\Models\Property;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Stripe\StripeConnectService;

class StripeConnectController extends Controller
{
    public function __construct(private StripeConnectService $connectService)
    {
        $this->middleware('permission:property.edit');
    }

    /**
     * Initiate Stripe Connect onboarding for a property
     */
    public function connect(Request $request, string $propertyId)
    {
        $property = Property::findOrFail($propertyId);

        $result = $this->connectService->createConnectedAccount($property);

        if (!$result['success']) {
            return redirect()
                ->route('property.show', $propertyId)
                ->with('error', $result['message']);
        }

        // Redirect to Stripe onboarding
        return redirect($result['onboarding_url']);
    }

    /**
     * Handle return from Stripe onboarding (success path)
     */
    public function handleReturn(string $propertyId)
    {
        $property = Property::findOrFail($propertyId);

        // Sync latest status from Stripe
        $result = $this->connectService->syncAccountStatus($property);

        if (!$result['success']) {
            return redirect()
                ->route('property.show', $propertyId)
                ->with('warning', 'Stripe account setup may not be complete. Please check the status.');
        }

        if ($result['is_active']) {
            return redirect()
                ->route('property.show', $propertyId)
                ->with('success', '🎉 Stripe account connected successfully! Payments for this property will now go to this account.');
        }

        return redirect()
            ->route('property.show', $propertyId)
            ->with('warning', 'Stripe account setup is incomplete. Please complete all required information.');
    }

    /**
     * Handle refresh (when onboarding link expires)
     */
    public function handleRefresh(string $propertyId)
    {
        $property = Property::findOrFail($propertyId);

        $result = $this->connectService->createOnboardingLink($property);

        if (!$result['success']) {
            return redirect()
                ->route('property.show', $propertyId)
                ->with('error', $result['message']);
        }

        return redirect($result['onboarding_url']);
    }

    /**
     * Sync account status from Stripe
     */
    public function syncStatus(string $propertyId)
    {
        $property = Property::findOrFail($propertyId);

        $result = $this->connectService->syncAccountStatus($property);

        if (request()->ajax()) {
            return response()->json($result);
        }

        if ($result['success']) {
            return redirect()
                ->route('property.show', $propertyId)
                ->with('success', 'Stripe account status synced successfully.');
        }

        return redirect()
            ->route('property.show', $propertyId)
            ->with('error', $result['message']);
    }

    /**
     * Open Stripe Express dashboard for the connected account
     */
    public function dashboard(string $propertyId)
    {
        $property = Property::findOrFail($propertyId);

        $result = $this->connectService->createLoginLink($property);

        if (!$result['success']) {
            return redirect()
                ->route('property.show', $propertyId)
                ->with('error', $result['message']);
        }

        return redirect($result['login_url']);
    }

    /**
     * Disconnect Stripe account from a property
     */
    public function disconnect(Request $request, string $propertyId)
    {
        $property = Property::findOrFail($propertyId);

        $result = $this->connectService->disconnectAccount($property);

        return redirect()
            ->route('property.show', $propertyId)
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
