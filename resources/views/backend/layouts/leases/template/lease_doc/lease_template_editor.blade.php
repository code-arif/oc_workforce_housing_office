@extends('layouts.app')
@section('title', 'Edit Lease Template:' . $template->name)
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h2>Edit Lease Template: {{ $template->name }}</h2>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Toolbar -->
        <div class="col-md-12">
            <div class="card mb-3">
                <div class="card-body">
                    <h5>Add Elements</h5>
                    <div class="btn-toolbar" role="toolbar">
                        <div class="btn-group me-2" role="group">
                            <button type="button" class="btn btn-primary" id="addPlaceholderBtn">
                                <i class="fas fa-tag"></i> Add Placeholder
                            </button>
                            <button type="button" class="btn btn-success" id="addAdminSignBtn">
                                <i class="fas fa-signature"></i> Add Admin Signature
                            </button>
                            <button type="button" class="btn btn-info" id="addTenantSignBtn">
                                <i class="fas fa-signature"></i> Add Tenant Signature
                            </button>
                        </div>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-warning" id="saveTemplateBtn">
                                <i class="fas fa-save"></i> Save Template
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Editor Area -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-body" style="min-height: 800px;">
                    <div id="templateEditor" contenteditable="true" style="padding: 40px; border: 1px solid #ddd; min-height: 750px; background: white;">
                        {!! $template->content !!}
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5>Available Placeholders</h5>
                </div>
                <div class="card-body">
                    <div class="list-group" id="placeholderList">
                        @foreach($placeholderTypes as $key => $label)
                        <a href="#" class="list-group-item list-group-item-action placeholder-item" data-placeholder="{{ $key }}">
                            <strong>{{ $label }}</strong>
                            <br>
                            {{-- <small class="text-muted">{{'{{'}} {{ $key }} {{'}}'}}</small> --}}
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5>Instructions</h5>
                </div>
                <div class="card-body">
                    <ol>
                        <li>Click where you want to add a placeholder</li>
                        <li>Click "Add Placeholder" or select from the list</li>
                        <li>Choose the field type</li>
                        <li>Position signature fields where needed</li>
                        <li>Save your template</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Placeholder Modal -->
<div class="modal fade" id="placeholderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Insert Placeholder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Select Field</label>
                    <select class="form-control" id="placeholderSelect">
                        <option value="">-- Select Field --</option>
                        @foreach($placeholderTypes as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="insertPlaceholderBtn">Insert</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/lease-template-editor.js') }}"></script>
@endpush

@push('styles')
<style>
    .placeholder {
        background-color: #fff3cd;
        border: 2px dashed #ffc107;
        padding: 2px 8px;
        border-radius: 4px;
        display: inline-block;
        cursor: pointer;
        font-weight: 600;
        color: #856404;
    }

    .signature-box {
        border: 2px solid #007bff;
        padding: 60px 20px;
        margin: 20px 0;
        text-align: center;
        background-color: #f8f9fa;
        position: relative;
        min-height: 100px;
    }

    .signature-box.admin-signature {
        border-color: #28a745;
        background-color: #d4edda;
    }

    .signature-box.tenant-signature {
        border-color: #17a2b8;
        background-color: #d1ecf1;
    }

    .signature-label {
        font-weight: bold;
        color: #495057;
        font-size: 14px;
    }

    .signature-remove {
        position: absolute;
        top: 5px;
        right: 5px;
        cursor: pointer;
        color: #dc3545;
        font-weight: bold;
        background: white;
        border-radius: 50%;
        width: 25px;
        height: 25px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #templateEditor {
        font-family: Arial, sans-serif;
        font-size: 14px;
        line-height: 1.6;
    }

    .placeholder-item:hover {
        background-color: #f8f9fa;
    }
</style>
@endpush