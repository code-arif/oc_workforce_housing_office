<div class="card mb-3">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0">
            <i class="bi bi-stripe me-2" style="color: #635BFF;"></i>Stripe Account
        </h6>
        {!! $property->stripe_status_badge !!}
    </div>
    <div class="card-body">
        @if ($property->stripe_account_status === 'active' && $property->stripe_onboarding_completed)
            {{-- CONNECTED STATE --}}
            <div class="text-center mb-3">
                <div class="rounded-circle bg-success-subtle d-inline-flex align-items-center justify-content-center mb-2"
                    style="width: 56px; height: 56px;">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 1.8rem;"></i>
                </div>
                <p class="mb-0 fw-semibold text-success">Connected</p>
                <small class="text-muted">
                    Account: <code>{{ substr($property->stripe_account_id, 0, 12) }}...</code>
                </small>
                @if ($property->stripe_connected_at)
                    <br><small class="text-muted">Since {{ $property->stripe_connected_at->format('M d, Y') }}</small>
                @endif
            </div>

            <div class="d-grid gap-2">
                <a href="{{ route('property.stripe.connect.dashboard', $property->id) }}"
                    class="btn btn-sm btn-outline-primary" target="_blank">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Open Stripe Dashboard
                </a>
                <a href="{{ route('property.stripe.connect.sync', $property->id) }}"
                    class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise me-1"></i> Sync Status
                </a>
                @if (auth()->user()->can('property.edit'))
                    <form action="{{ route('property.stripe.connect.disconnect', $property->id) }}" method="POST"
                        onsubmit="return confirm('Are you sure? This will disconnect the Stripe account from this property.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                            <i class="bi bi-x-circle me-1"></i> Disconnect
                        </button>
                    </form>
                @endif
            </div>
        @elseif($property->stripe_account_status === 'pending')
            {{-- PENDING / INCOMPLETE STATE --}}
            <div class="text-center mb-3">
                <div class="rounded-circle bg-warning-subtle d-inline-flex align-items-center justify-content-center mb-2"
                    style="width: 56px; height: 56px;">
                    <i class="bi bi-hourglass-split text-warning" style="font-size: 1.5rem;"></i>
                </div>
                <p class="mb-1 fw-semibold text-warning">Setup Incomplete</p>
                <small class="text-muted">Please complete Stripe onboarding to accept payments.</small>
            </div>
            <div class="d-grid gap-2">
                @if (auth()->user()->can('property.edit'))
                    <a href="{{ route('property.stripe.connect.connect', $property->id) }}"
                        class="btn btn-sm btn-warning">
                        <i class="bi bi-arrow-right-circle me-1"></i> Continue Onboarding
                    </a>
                @endif
                <a href="{{ route('property.stripe.connect.sync', $property->id) }}"
                    class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise me-1"></i> Check Status
                </a>
            </div>
        @else
            {{-- NOT CONNECTED STATE --}}
            <div class="text-center mb-3">
                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-2"
                    style="width: 56px; height: 56px;">
                    <i class="bi bi-credit-card text-muted" style="font-size: 1.5rem;"></i>
                </div>
                <p class="mb-1 fw-semibold">No Stripe Account</p>
                <small class="text-muted">Connect a Stripe account to accept payments for this property
                    directly.</small>
            </div>
            @if (auth()->user()->can('property.edit'))
                <div class="d-grid">
                    <a href="{{ route('property.stripe.connect.connect', $property->id) }}" class="btn btn-sm"
                        style="background-color: #635BFF; color: white; border: none;">
                        <i class="bi bi-stripe me-1"></i> Connect Stripe Account
                    </a>
                </div>
            @endif
        @endif
    </div>
</div>
