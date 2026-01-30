@extends('auth.app')

@section('title', 'Forgot Password')

@section('content')
<style>
    .login-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 0.875rem 1.5rem;
        font-weight: 600;
        font-size: 1rem;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
    }
    
    .btn-back {
        background: #f7fafc;
        border: 2px solid #e2e8f0;
        color: #4a5568;
        padding: 0.5rem 1rem;
        font-weight: 500;
        font-size: 0.875rem;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-back:hover {
        background: #edf2f7;
        border-color: #cbd5e0;
        color: #2d3748;
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
                <h1 class="login-title">Forgot Password?</h1>
                <p class="login-subtitle mb-0">Enter your email address and we'll send you a reset link</p>
            </div>
            
            <div class="login-body">
                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" novalidate>
                    @csrf

                    <div class="mb-4">
                        <label for="email" class="form-label">Email Address</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="form-control @error('email') is-invalid @enderror"
                            placeholder="name@example.com"
                            required
                            autofocus
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        Send Reset Link
                    </button>
                    
                    <div class="text-center">
                        <a href="{{ route('login') }}" class="btn-back">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                            </svg>
                            Back to Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection