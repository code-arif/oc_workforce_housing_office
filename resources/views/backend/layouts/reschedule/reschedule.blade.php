 <div class="modal fade" id="rescheduleModal" tabindex="-1" aria-labelledby="rescheduleModalLabel" aria-hidden="true">
     <div class="modal-dialog modal-xl">
         <div class="modal-content">
             <form id="rescheduleForm" enctype="multipart/form-data">
                 @csrf
                 <input type="hidden" name="id" id="rescheduleID">

                 <div class="modal-header">
                     <h5 class="modal-title" id="rescheduleModalLabel">Edit Reschedule</h5>
                     <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                 </div>

                 <div class="modal-body">
                     <div class="row">
                         {{-- Work Details (Read Only) --}}
                         <div class="col-md-12 mb-3">
                             <h6 class="fw-bold mb-3">Work Details</h6>
                             <ul class="list-group shadow-sm rounded-3">
                                 <li class="list-group-item d-flex justify-content-start align-items-center py-2">
                                     <strong style="margin-right: 10px">Title:</strong> <span id="work_title"
                                         class="text-success fw-semibold"></span>
                                 </li>
                                 <li class="list-group-item d-flex justify-content-start py-2">
                                     <strong style="margin-right: 10px">Description:</strong> <span
                                         id="work_description" class="text-muted"></span>
                                 </li>

                                 {{-- clickable location --}}
                                 <li class="list-group-item d-flex justify-content-start py-2">
                                     <strong style="margin-right: 10px" id="">Location:</strong>
                                     <a href="#" target="_blank" id="reschedule_location_link"
                                         class="text-primary fw-semibold text-decoration-underline">
                                         <span id="reschedule_location">---</span>
                                     </a>
                                 </li>

                                 <li class="list-group-item d-flex justify-content-start py-2">
                                     <strong style="margin-right: 10px">Time:</strong> <span id="time"
                                         class="badge bg-warning text-dark"></span>
                                 </li>
                                 <li class="list-group-item d-flex justify-content-start py-2">
                                     <strong style="margin-right: 10px">Work Date:</strong> <span id="work_date"
                                         class="badge bg-secondary"></span>
                                 </li>
                             </ul>
                         </div>

                         <hr class="my-3">

                         {{-- Suggested Date --}}
                         <div class="col-md-3">
                             <label class="form-label">Suggested Work Date</label>
                             <input type="date" class="form-control" name="work_date" id="work_date_input">
                             <span class="text-danger error-text work_date_error"></span>
                         </div>

                         {{-- Suggested Start Time --}}
                         <div class="col-md-3" id="start_time_wrapper">
                             <label class="form-label">Suggested Start Time</label>
                             <input type="text" class="form-control timepicker" name="start_time" id="start_time">
                             <span class="text-danger error-text start_time_error"></span>
                         </div>

                         {{-- Suggested End Time --}}
                         <div class="col-md-3" id="end_time_wrapper">
                             <label class="form-label">Suggested End Time</label>
                             <input type="text" class="form-control timepicker" name="end_time" id="end_time">
                             <span class="text-danger error-text end_time_error"></span>
                         </div>

                         {{-- All Day Checkbox --}}
                         <div class="col-md-3" style="margin-top: 2.4rem">
                             <div class="form-check">
                                 <input class="form-check-input custom-checkbox" type="checkbox" name="is_all_day"
                                     id="is_all_day" value="1">
                                 <label class="form-check-label" for="is_all_day">
                                     All Day Event
                                 </label>
                             </div>
                         </div>
                     </div>
                 </div>

                 <div class="modal-footer">
                     <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                     <button type="submit" class="btn btn-primary" id="rescheduleSubmitBtn">Save changes</button>
                 </div>
             </form>
         </div>
     </div>
 </div>

