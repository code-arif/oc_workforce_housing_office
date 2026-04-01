@extends('backend.app')

@section('title', 'FAQ')

@section('content')
<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">

            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">FAQ Management</h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item active">FAQ</li>
                    </ol>
                </div>
                <div class="ms-auto">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#faqModal">
                        <i class="fe fe-plus me-2"></i> Add FAQ
                    </button>
                </div>
            </div>

            <!-- FAQ Table -->
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">FAQ List</h3>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="60">#</th>
                                    <th>Question</th>
                                    <th width="120">Status</th>
                                    <th width="180">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($faqs as $key => $faq)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>

                                        <td>
                                            <strong>{{ $faq->question }}</strong>
                                            <p class="text-muted mb-0">
                                                {{ \Str::limit($faq->answer, 80) }}
                                            </p>
                                        </td>

                                        <td>
                                            <span class="badge {{ $faq->status ? 'bg-success' : 'bg-danger' }}">
                                                {{ $faq->status ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>

                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <!-- EDIT -->
                                                <button class="btn btn-info"
                                                    onclick='editFaq(@json($faq))'>
                                                    <i class="fe fe-edit"></i>
                                                </button>

                                                <!-- STATUS TOGGLE (ADMIN ONLY) -->
                                                <button class="btn btn-warning"
                                                    onclick="changeStatus({{ $faq->id }})"
                                                    title="Change Status">
                                                    <i class="fe fe-refresh-cw"></i>
                                                </button>

                                                <!-- DELETE -->
                                                <button class="btn btn-danger"
                                                    onclick="deleteFaq({{ $faq->id }})">
                                                    <i class="fe fe-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            No FAQ Found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- FAQ Modal -->
<div class="modal fade" id="faqModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="faqForm">
            @csrf
            <input type="hidden" id="faq_id">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">FAQ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Question</label>
                        <input type="text" id="question" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Answer</label>
                        <textarea id="answer" rows="5" class="form-control" required></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        Save
                    </button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        Cancel
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // CREATE & UPDATE
    $('#faqForm').on('submit', function(e) {
        e.preventDefault();

        let id = $('#faq_id').val();
        let url = id
            ? "{{ route('faq.update', '') }}/" + id
            : "{{ route('faq.store') }}";

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                question: $('#question').val(),
                answer: $('#answer').val()
            },
            success: function() {
                location.reload();
            }
        });
    });

    // EDIT
    function editFaq(faq) {
        $('#faq_id').val(faq.id);
        $('#question').val(faq.question);
        $('#answer').val(faq.answer);
        $('#faqModal').modal('show');
    }

    // DELETE
    function deleteFaq(id) {
        if (confirm('Are you sure you want to delete this FAQ?')) {
            $.ajax({
                url: "{{ route('faq.delete', '') }}/" + id,
                type: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function() {
                    location.reload();
                }
            });
        }
    }

    // STATUS CHANGE (ACTIVE / INACTIVE) — ADMIN ONLY
    function changeStatus(id) {
        $.ajax({
            url: "{{ route('faq.status', '') }}/" + id,
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function() {
                location.reload();
            }
        });
    }
</script>
@endpush
