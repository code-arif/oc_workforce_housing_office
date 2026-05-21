@extends('backend.app')

@section('title', 'Stripe Settings')

@section('content')
<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            {{-- PAGE-HEADER --}}
            <div class="page-header">
                <div>
                    <h1 class="page-title">Stripe Settings</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Settings</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Stripe Settings</li>
                    </ol>
                </div>
            </div>
            {{-- PAGE-HEADER --}}


            <div class="row">
                <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
                    <form class="form-horizontal" method="post" action="{{ route('setting.stripe.update') }}">
                        @csrf
                        @method('PATCH')

                        <div class="card box-shadow-0 mb-4">
                            <div class="card-header">
                                <h4 class="card-title">Stripe API Secrets</h4>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="stripe_key" class="form-label">Stripe Public Key:</label>
                                    <input type="text" class="form-control @error('stripe_key') is-invalid @enderror"
                                        name="stripe_key" placeholder="pk_test_..." id="stripe_key"
                                        value="{{ old('stripe_key', $setting->stripe_key ?? env('STRIPE_KEY')) }}">
                                    @error('stripe_key')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="stripe_secret" class="form-label">Stripe Secret Key:</label>
                                    <input type="text" class="form-control @error('stripe_secret') is-invalid @enderror"
                                        name="stripe_secret" placeholder="sk_test_..." id="stripe_secret"
                                        value="{{ old('stripe_secret', $setting->stripe_secret ?? env('STRIPE_SECRET')) }}">
                                    @error('stripe_secret')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="stripe_webhook_secret" class="form-label">Stripe Webhook Secret:</label>
                                    <input type="text" class="form-control @error('stripe_webhook_secret') is-invalid @enderror"
                                        name="stripe_webhook_secret" placeholder="whsec_..." id="stripe_webhook_secret"
                                        value="{{ old('stripe_webhook_secret', $setting->stripe_webhook_secret ?? env('STRIPE_WEBHOOK_SECRET')) }}">
                                    @error('stripe_webhook_secret')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="card box-shadow-0">
                            <div class="card-header">
                                <h4 class="card-title">Processing Fee Control</h4>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="stripe_ach_fee" class="form-label">ACH Flat Fee ($):</label>
                                    <input type="number" step="0.01" class="form-control @error('stripe_ach_fee') is-invalid @enderror"
                                        name="stripe_ach_fee" placeholder="5.00" id="stripe_ach_fee"
                                        value="{{ old('stripe_ach_fee', $setting->stripe_ach_fee ?? env('STRIPE_ACH_FEE', 5.00)) }}">
                                    <small class="text-muted">Flat fee charged for US Bank Account (ACH) payments.</small>
                                    @error('stripe_ach_fee')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="stripe_card_fee_percentage" class="form-label">Card Fee Percentage (%):</label>
                                    <input type="number" step="0.01" class="form-control @error('stripe_card_fee_percentage') is-invalid @enderror"
                                        name="stripe_card_fee_percentage" placeholder="2.9" id="stripe_card_fee_percentage"
                                        value="{{ old('stripe_card_fee_percentage', $setting->stripe_card_fee_percentage ?? env('STRIPE_CARD_FEE_PERCENTAGE', 2.9)) }}">
                                    <small class="text-muted">Percentage fee for credit card payments (e.g. 2.9 for 2.9%).</small>
                                    @error('stripe_card_fee_percentage')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="stripe_card_fee_fixed" class="form-label">Card Fixed Fee ($):</label>
                                    <input type="number" step="0.01" class="form-control @error('stripe_card_fee_fixed') is-invalid @enderror"
                                        name="stripe_card_fee_fixed" placeholder="0.30" id="stripe_card_fee_fixed"
                                        value="{{ old('stripe_card_fee_fixed', $setting->stripe_card_fee_fixed ?? env('STRIPE_CARD_FEE_FIXED', 0.30)) }}">
                                    <small class="text-muted">Fixed amount added to card payments (e.g. 0.30 for 30 cents).</small>
                                    @error('stripe_card_fee_fixed')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group mt-4">
                                    <button class="btn btn-primary" type="submit">Update Stripe Settings</button>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
<!-- CONTAINER CLOSED -->
@endsection
