{{-- Property Type Create/Edit Modal --}}
<div class="modal fade" id="bedModal" tabindex="-1" aria-labelledby="bedModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Bed</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="bedForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="bedId">
                <div class="modal-body">

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="room" class="form-label">Room <span
                                    class="text-danger">*</span></label>
                                @php
                                    $rooms = App\Models\Room::get();
                                @endphp
                            <select name="room_id" id="room_id" class="select3">
                                @foreach ($rooms as $room)
                                    <option value="{{ $room->id }}">{{ $room->room_number }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger error-text room_id_error"></span>
                        </div>

                        <!-- Name -->
                        {{-- <div class="col-12 mb-3">
                            <label for="bed_label" class="form-label">Bed Label <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="bed_label" name="bed_label" class="form-control"
                                placeholder="Enter Bed Label" required>
                            <span class="text-danger error-text bed_label_error"></span>
                        </div> --}}

                        <div class="col-12 mb-3">
                            <label for="bed_number" class="form-label">Bed Number <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="bed_number" name="bed_number" class="form-control"
                                placeholder="Enter Bed number" required>
                            <span class="text-danger error-text bed_number_error"></span>
                        </div>

                        <!-- Description -->
                        <div class="col-12 mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" 
                                placeholder="Enter property type description" rows="4"></textarea>
                            <span class="text-danger error-text description_error"></span>
                        </div>

                        <!-- Is Active -->
                        <div class="col-12 mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="isActive" name="is_active" value="1" checked>
                                <label class="form-check-label" for="isActive">
                                    Occupied
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>