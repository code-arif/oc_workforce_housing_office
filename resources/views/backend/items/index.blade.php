@extends('backend.app')

@section('title', 'Items')

@section('content')
<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">

            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Items Management</h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Items</li>
                    </ol>
                </div>
                <div class="ms-auto">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#itemModal">
                        <i class="fe fe-plus me-2"></i> Add Item
                    </button>
                </div>
            </div>

            <!-- Items Table -->
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">Items List</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="60">#</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th width="120">Status</th>
                                    <th width="180">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $key => $item)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->price }}</td>
                                        <td>
                                            <span class="badge {{ $item->status ? 'bg-success' : 'bg-danger' }}">
                                                {{ $item->status ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-info" onclick='editItem(@json($item))'>
                                                    <i class="fe fe-edit"></i>
                                                </button>
                                                <button class="btn btn-warning" onclick="changeStatus({{ $item->id }})">
                                                    <i class="fe fe-refresh-cw"></i>
                                                </button>
                                                <button class="btn btn-danger" onclick="deleteItem({{ $item->id }})">
                                                    <i class="fe fe-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No Item Found</td>
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

<!-- Item Modal -->
<div class="modal fade" id="itemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="itemForm">
            @csrf
            <input type="hidden" id="item_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" id="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price</label>
                        <input type="text" id="price" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // CREATE & UPDATE
    $('#itemForm').on('submit', function(e) {
        e.preventDefault();
        let id = $('#item_id').val();
        let url = id ? "{{ route('items.update', '') }}/" + id : "{{ route('items.store') }}";

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                name: $('#name').val(),
                price: $('#price').val()
            },
            success: function() { location.reload(); }
        });
    });

    // EDIT
    function editItem(item) {
        $('#item_id').val(item.id);
        $('#name').val(item.name);
        $('#price').val(item.price);
        $('#itemModal').modal('show');
    }

    // DELETE
    function deleteItem(id) {
        if(confirm('Are you sure you want to delete this item?')) {
            $.ajax({
                url: "{{ route('items.delete', '') }}/" + id,
                type: 'DELETE',
                data: { _token: "{{ csrf_token() }}" },
                success: function() { location.reload(); }
            });
        }
    }

    // STATUS TOGGLE
    function changeStatus(id) {
        $.ajax({
            url: "{{ route('items.status', '') }}/" + id,
            type: 'POST',
            data: { _token: "{{ csrf_token() }}" },
            success: function() { location.reload(); }
        });
    }
</script>
@endpush
