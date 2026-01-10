@extends('backend.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Create New Lease Document</h2>
            <hr>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('lease-documents.store') }}" method="POST">
                        @csrf

                        <!-- Template Selection -->
                        <div class="form-group mb-3">
                            <label for="lease_template_id" class="form-label">Select Template *</label>
                            <select name="lease_template_id" id="lease_template_id" class="form-control @error('lease_template_id') is-invalid @enderror" required>
                                <option value="">-- Select Template --</option>
                                @foreach($templates as $template)
                                <option value="{{ $template->id }}">{{ $template->name }}</option>
                                @endforeach
                            </select>
                            @error('lease_template_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tenant Selection -->
                        <div class="form-group mb-3">
                            <label for="tenant_id" class="form-label">Select Tenant *</label>
                            <select name="tenant_id" id="tenant_id" class="form-control @error('tenant_id') is-invalid @enderror" required>
                                <option value="">-- Select Tenant --</option>
                                {{-- @foreach($tenants as $tenant) --}}
                                {{-- <option value="{{ $tenant->id }}" data-name="{{ $tenant->name }}" data-email="{{ $tenant->email }}" data-phone="{{ $tenant->phone }}"> --}}
                                <option value="1" data-name="Arif Hossen" data-email="arifhossen@me.com" data-phone="01601958560">
                                    Arif Hossen
                                </option>
                                {{-- @endforeach --}}
                            </select>
                            @error('tenant_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>
                        <h5 class="mb-3">Fill Lease Details</h5>

                        <!-- Tenant Information -->
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="text-muted">Tenant Information</h6>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="tenant_name" class="form-label">Tenant Name</label>
                                    <input type="text" name="tenant_name" id="tenant_name" class="form-control" value="{{ old('tenant_name') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="tenant_email" class="form-label">Tenant Email</label>
                                    <input type="email" name="tenant_email" id="tenant_email" class="form-control" value="{{ old('tenant_email') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="tenant_phone" class="form-label">Tenant Phone</label>
                                    <input type="text" name="tenant_phone" id="tenant_phone" class="form-control" value="{{ old('tenant_phone') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Property Information -->
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="text-muted">Property Information</h6>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="property_address" class="form-label">Property Address</label>
                                    <textarea name="property_address" id="property_address" class="form-control" rows="2">{{ old('property_address') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Lease Terms -->
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="text-muted">Lease Terms</h6>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="lease_start_date" class="form-label">Start Date</label>
                                    <input type="date" name="lease_start_date" id="lease_start_date" class="form-control" value="{{ old('lease_start_date') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="lease_end_date" class="form-label">End Date</label>
                                    <input type="date" name="lease_end_date" id="lease_end_date" class="form-control" value="{{ old('lease_end_date') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="lease_term" class="form-label">Lease Term</label>
                                    <input type="text" name="lease_term" id="lease_term" class="form-control" placeholder="e.g., 12 months" value="{{ old('lease_term') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Financial Information -->
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="text-muted">Financial Information</h6>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="monthly_rent" class="form-label">Monthly Rent ($)</label>
                                    <input type="number" step="0.01" name="monthly_rent" id="monthly_rent" class="form-control" value="{{ old('monthly_rent') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="security_deposit" class="form-label">Security Deposit ($)</label>
                                    <input type="number" step="0.01" name="security_deposit" id="security_deposit" class="form-control" value="{{ old('security_deposit') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Landlord Information -->
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="text-muted">Landlord Information</h6>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="admin_name" class="form-label">Landlord Name</label>
                                    <input type="text" name="admin_name" id="admin_name" class="form-control" value="{{ old('admin_name', auth()->user()->name ?? '') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="admin_email" class="form-label">Landlord Email</label>
                                    <input type="email" name="admin_email" id="admin_email" class="form-control" value="{{ old('admin_email', auth()->user()->email ?? '') }}">
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-file-alt"></i> Create Lease Document
                            </button>
                            <a href="{{ route('lease-documents.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-fill tenant information when tenant is selected
    $('#tenant_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const tenantName = selectedOption.data('name');
        const tenantEmail = selectedOption.data('email');
        const tenantPhone = selectedOption.data('phone');

        $('#tenant_name').val(tenantName || '');
        $('#tenant_email').val(tenantEmail || '');
        $('#tenant_phone').val(tenantPhone || '');
    });

    // Auto-calculate lease term when dates are selected
    $('#lease_start_date, #lease_end_date').on('change', function() {
        const startDate = $('#lease_start_date').val();
        const endDate = $('#lease_end_date').val();

        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            const months = Math.round(diffDays / 30);

            $('#lease_term').val(months + ' months');
        }
    });
});
</script>
@endsection