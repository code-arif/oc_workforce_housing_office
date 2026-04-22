@extends('backend.app')

@section('title', 'Mail Settings')

@section('content')
<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Mail Settings</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Settings</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Mail Settings</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
                    <div class="card box-shadow-0">
                        <div class="card-body">
                            <form class="form-horizontal" method="post" action="{{ route('setting.mail.update') }}">
                                @csrf
                                @method('PATCH')

                                <div class="row mb-4">
                                    <div class="form-group col-md-6">
                                        <label for="mail_mailer" class="form-label">Mailer</label>
                                        <select class="form-control @error('mail_mailer') is-invalid @enderror" name="mail_mailer" id="mail_mailer">
                                            <option value="smtp" {{ old('mail_mailer', $setting->mail_mailer ?? 'smtp') === 'smtp' ? 'selected' : '' }}>SMTP</option>
                                            <option value="sendmail" {{ old('mail_mailer', $setting->mail_mailer) === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                            <option value="log" {{ old('mail_mailer', $setting->mail_mailer) === 'log' ? 'selected' : '' }}>Log</option>
                                        </select>
                                        @error('mail_mailer')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="mail_host" class="form-label">Host</label>
                                        <input type="text" class="form-control @error('mail_host') is-invalid @enderror" name="mail_host" id="mail_host" placeholder="smtp.mailtrap.io" value="{{ old('mail_host', $setting->mail_host ?? '') }}">
                                        @error('mail_host')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="mail_port" class="form-label">Port</label>
                                        <input type="number" class="form-control @error('mail_port') is-invalid @enderror" name="mail_port" id="mail_port" placeholder="587" value="{{ old('mail_port', $setting->mail_port ?? '') }}">
                                        @error('mail_port')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="mail_encryption" class="form-label">Encryption</label>
                                        <select class="form-control @error('mail_encryption') is-invalid @enderror" name="mail_encryption" id="mail_encryption">
                                            <option value="" {{ old('mail_encryption', $setting->mail_encryption ?? '') === '' ? 'selected' : '' }}>None</option>
                                            <option value="tls" {{ old('mail_encryption', $setting->mail_encryption ?? '') === 'tls' ? 'selected' : '' }}>TLS</option>
                                            <option value="ssl" {{ old('mail_encryption', $setting->mail_encryption ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                        </select>
                                        @error('mail_encryption')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="mail_username" class="form-label">Username</label>
                                        <input type="text" class="form-control @error('mail_username') is-invalid @enderror" name="mail_username" id="mail_username" placeholder="SMTP username" value="{{ old('mail_username', $setting->mail_username ?? '') }}">
                                        @error('mail_username')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="mail_password" class="form-label">Password</label>
                                        <input type="password" class="form-control @error('mail_password') is-invalid @enderror" name="mail_password" id="mail_password" placeholder="Leave blank to keep current password">
                                        @error('mail_password')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="mail_from_address" class="form-label">From Address</label>
                                        <input type="email" class="form-control @error('mail_from_address') is-invalid @enderror" name="mail_from_address" id="mail_from_address" placeholder="no-reply@example.com" value="{{ old('mail_from_address', $setting->mail_from_address ?? '') }}">
                                        @error('mail_from_address')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="mail_from_name" class="form-label">From Name</label>
                                        <input type="text" class="form-control @error('mail_from_name') is-invalid @enderror" name="mail_from_name" id="mail_from_name" placeholder="Company Name" value="{{ old('mail_from_name', $setting->mail_from_name ?? '') }}">
                                        @error('mail_from_name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <button class="btn btn-primary" type="submit">Update Mail Settings</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
