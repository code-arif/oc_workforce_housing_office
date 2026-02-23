<?php

namespace App\Services\Stripe;

use Exception;
use Stripe\Stripe;
use Stripe\Account;
use Stripe\AccountLink;
use App\Models\Property;
use Illuminate\Support\Facades\Log;

class StripeConnectService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a Stripe Express connected account for a property
     */
    public function createConnectedAccount(Property $property): array
    {
        try {
            // If account already exists, just get new onboarding link
            if ($property->stripe_account_id) {
                return $this->createOnboardingLink($property);
            }

            // Create new Express account
            $account = Account::create([
                'type' => 'express',
                'country' => 'US',
                'capabilities' => [
                    'card_payments' => ['requested' => true],
                    'transfers' => ['requested' => true],
                ],
                'business_type' => 'company',
                'metadata' => [
                    'property_id' => $property->id,
                    'property_name' => $property->name,
                ],
                'settings' => [
                    'payouts' => [
                        'schedule' => [
                            'interval' => 'daily', // Auto payout daily
                        ],
                    ],
                ],
            ]);

            // Save account ID immediately
            $property->update([
                'stripe_account_id' => $account->id,
                'stripe_account_status' => 'pending',
                'stripe_onboarding_completed' => false,
                'stripe_account_data' => [
                    'account_id' => $account->id,
                    'type' => $account->type,
                    'created_at' => now()->toIso8601String(),
                ],
            ]);

            Log::info('Stripe Express account created', [
                'property_id' => $property->id,
                'account_id' => $account->id,
            ]);

            // Create onboarding link
            return $this->createOnboardingLink($property);
        } catch (Exception $e) {
            Log::error('Failed to create Stripe connected account', [
                'property_id' => $property->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create Stripe account: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Create an onboarding link for the connected account
     */
    public function createOnboardingLink(Property $property): array
    {
        try {
            if (!$property->stripe_account_id) {
                return [
                    'success' => false,
                    'message' => 'No Stripe account found for this property.',
                ];
            }

            $accountLink = AccountLink::create([
                'account' => $property->stripe_account_id,
                'refresh_url' => route('property.stripe.connect.refresh', $property->id),
                'return_url' => route('property.stripe.connect.return', $property->id),
                'type' => 'account_onboarding',
            ]);

            return [
                'success' => true,
                'onboarding_url' => $accountLink->url,
            ];
        } catch (Exception $e) {
            Log::error('Failed to create onboarding link', [
                'property_id' => $property->id,
                'account_id' => $property->stripe_account_id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create onboarding link: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify and sync account status from Stripe
     */
    public function syncAccountStatus(Property $property): array
    {
        try {
            if (!$property->stripe_account_id) {
                return [
                    'success' => false,
                    'message' => 'No Stripe account linked.',
                ];
            }

            $account = Account::retrieve($property->stripe_account_id);

            $isOnboarded = $account->details_submitted
                && $account->charges_enabled
                && $account->payouts_enabled;

            $status = 'pending';
            if ($isOnboarded) {
                $status = 'active';
            } elseif ($account->requirements && count($account->requirements->eventually_due ?? []) > 0) {
                $status = 'restricted';
            }

            $property->update([
                'stripe_account_status' => $status,
                'stripe_onboarding_completed' => $isOnboarded,
                'stripe_connected_at' => $isOnboarded && !$property->stripe_connected_at
                    ? now()
                    : $property->stripe_connected_at,
                'stripe_account_data' => [
                    'account_id' => $account->id,
                    'type' => $account->type,
                    'charges_enabled' => $account->charges_enabled,
                    'payouts_enabled' => $account->payouts_enabled,
                    'details_submitted' => $account->details_submitted,
                    'country' => $account->country,
                    'default_currency' => $account->default_currency,
                    'requirements' => $account->requirements?->toArray() ?? [],
                    'synced_at' => now()->toIso8601String(),
                ],
            ]);

            return [
                'success' => true,
                'status' => $status,
                'is_active' => $isOnboarded,
                'charges_enabled' => $account->charges_enabled,
                'payouts_enabled' => $account->payouts_enabled,
                'details_submitted' => $account->details_submitted,
            ];
        } catch (Exception $e) {
            Log::error('Failed to sync Stripe account status', [
                'property_id' => $property->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to sync account: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Disconnect/delete a connected account
     */
    public function disconnectAccount(Property $property): array
    {
        try {
            if (!$property->stripe_account_id) {
                return ['success' => false, 'message' => 'No account to disconnect.'];
            }

            // Reject if there are any pending/unsettled payments
            // Optional: Add a check here if needed

            $account = Account::retrieve($property->stripe_account_id);
            $account->delete();

            $property->update([
                'stripe_account_id' => null,
                'stripe_account_status' => 'not_connected',
                'stripe_onboarding_completed' => false,
                'stripe_account_data' => null,
                'stripe_connected_at' => null,
            ]);

            Log::info('Stripe account disconnected', ['property_id' => $property->id]);

            return ['success' => true, 'message' => 'Stripe account disconnected successfully.'];
        } catch (Exception $e) {
            Log::error('Failed to disconnect Stripe account', [
                'property_id' => $property->id,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => 'Failed to disconnect: ' . $e->getMessage()];
        }
    }

    /**
     * Create a login link for the connected account dashboard
     */
    public function createLoginLink(Property $property): array
    {
        try {
            if (!$property->stripe_account_id) {
                return ['success' => false, 'message' => 'No Stripe account linked.'];
            }

            $loginLink = Account::createLoginLink($property->stripe_account_id);

            return [
                'success' => true,
                'login_url' => $loginLink->url,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create login link: ' . $e->getMessage(),
            ];
        }
    }
}
