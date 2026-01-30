@extends('auth.app')

@section('title', 'Welcome')

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
        padding: 3rem 2rem;
        border-bottom: 1px solid #e9ecef;
    }
    
    .brand-logo {
        height: 4rem;
        max-width: 100%;
        object-fit: contain;
        margin-bottom: 1.5rem;
    }
    
    .welcome-title {
        font-size: 2rem;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 0.75rem;
    }
    
    .welcome-subtitle {
        color: #718096;
        font-size: 1rem;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #eac066 0%, #a27c4b 100%);
        border: none;
        padding: 0.875rem 2rem;
        font-weight: 600;
        font-size: 1.1rem;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
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
                <h1 class="welcome-title">Welcome Back!</h1>
                <p class="welcome-subtitle mb-0">Log in to access your dashboard</p>
            </div>
            
            <div class="text-center p-4 p-md-5">
                <a href="{{ route('login') }}" class="btn btn-primary w-100">
                    Login to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection



