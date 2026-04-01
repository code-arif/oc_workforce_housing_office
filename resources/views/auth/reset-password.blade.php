@extends('auth.app')

@section('title', 'Reset Password')

@section('content')
<style>
    .login-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #eac066 0%, #a27c4b 100%);
        padding: 2rem 1rem;
    }
    
    .login-card-wrapper {
        max-width: 460px;
        width: 100%;
    }
    
    .login-card {
        background: #ffffff;
        border-radius: 1.25rem;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        overflow: hidden;
    }
    
    .login-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        padding: 2.5rem 2rem 2rem;
        border-bottom: 1px solid #e9ecef;
    }
    
    .login-body {
        padding: 2rem 2rem 2.5rem;
        background: #ffffff;
    }
    
    .brand-logo {
        height: 4rem;
        max-width: 100%;
        object-fit: contain;
        margin-bottom: 1.5rem;
    }
    
    .login-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 0.5rem;
    }
    
    .login-subtitle {
        color: #718096;
        font-size: 0.95rem;
    }
    
    .form-label {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }
    
    .form-control {
        border: 2px solid #e2e8f0;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #a27c4b;
        box-shadow: 0 0 0 3px rgba(162, 124, 75, 0.1);
    }
    
    .input-group-text {
        background: #f7fafc;
        border: 2px solid #e2e8f0;
        border-right: none;
        color: #a27c4b;
        border-radius: 0.5rem 0 0 0.5rem;
    }
    
    .input-group .form-control {
        border-left: none;
        border-radius: 0 0.5rem 0.5rem 0;
    }
    
    .input-group:focus-within .input-group-text {
        border-color: #a27c4b;
        background: #fef7e0;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #eac066 0%, #a27c4b 100%);
        border: none;
        padding: 0.875rem 1.5rem;
        font-weight: 600;
        font-size: 1rem;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(234, 192, 102, 0.4);
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(234, 192, 102, 0.5);
    }
    
    .alert {
        border-radius: 0.5rem;
        border: none;
        font-size: 0.9rem;
    }
</style>

<div class="login-container">
    <div class="login-card-wrapper">
        <div class="login-card">
            <div class="login-header text-center">
                @php
                    $settings = App\Models\Setting::first();
                @endphp
                <a href="{{ url('/') }}" class="d-inline-block" aria-label="Home">
                    <img src="{{ asset(optional($settings)->logo ?: 'default/logo.png') }}" class="brand-logo" alt="{{ config('app.name') }}">
                </a>
                <h1 class="login-title">Reset Password</h1>
                <p class="login-subtitle mb-0">Enter your new password below</p>
            </div>
            
            <div class="login-body">
                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('password.store') }}" novalidate>
                    @csrf

                    <!-- Password Reset Token -->
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fe fe-mail" aria-hidden="true"></i></span>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email', $request->email) }}"
                                class="form-control @error('email') is-invalid @enderror"
                                placeholder="name@example.com"
                                required
                                autofocus
                                autocomplete="username"
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fe fe-lock" aria-hidden="true"></i></span>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="••••••••"
                                required
                                autocomplete="new-password"
                            >
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Please enter a password.</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fe fe-lock" aria-hidden="true"></i></span>
                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                class="form-control @error('password_confirmation') is-invalid @enderror"
                                placeholder="••••••••"
                                required
                                autocomplete="new-password"
                            >
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Please confirm your password.</div>
                            @enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        Reset Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection