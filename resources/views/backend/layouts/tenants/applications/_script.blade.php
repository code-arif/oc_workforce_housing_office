    <script>
        let singleEmailTable, reservationTable;

        $(document).ready(function() {
            // Initialize Single Email Table
            singleEmailTable = $('#singleEmailTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('tenants.applications.get.data') }}',
                    data: function(d) {
                        d.type = 'single';
                        d.status = $('#singleStatusFilter').val();
                        d.search = $('#singleSearchFilter').val();
                        d.date_from = $('#singleDateFrom').val();
                        d.date_to = $('#singleDateTo').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'applicant',
                        name: 'email',
                        orderable: true
                    },
                    {
                        data: 'contact',
                        name: 'contact',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        orderable: true,
                        className: 'text-center'
                    },
                    {
                        data: 'submitted_at',
                        name: 'created_at',
                        orderable: true
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                order: [
                    [0, 'desc']
                ],
                pageLength: 25,
                language: {
                    search: "",
                    searchPlaceholder: "Search...",
                }
            });

            // Initialize Reservation Table
            reservationTable = $('#reservationTable').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                scrollX: false,
                ajax: {
                    url: '{{ route('tenants.applications.get.reservation.data') }}',
                    data: function(d) {
                        d.type = 'reservation';
                        d.status = $('#reservationStatusFilter').val();
                        d.search = $('#reservationSearchFilter').val();
                        d.date_from = $('#reservationDateFrom').val();
                        d.date_to = $('#reservationDateTo').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '50px'
                    },
                    {
                        data: 'applicant',
                        name: 'company_name',
                        orderable: true
                    },
                    {
                        data: 'contact',
                        name: 'email',
                        orderable: true
                    },
                    {
                        data: 'details',
                        name: 'details',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        orderable: true,
                        className: 'text-center',
                        width: '120px'
                    },
                    {
                        data: 'submitted_at',
                        name: 'created_at',
                        orderable: true,
                        width: '150px'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        width: '120px'
                    }
                ],
                order: [
                    [0, 'desc']
                ],
                pageLength: 25,
                language: {
                    search: "",
                    searchPlaceholder: "Search...",
                }
            });



            // Single Email Filters
            $('#singleStatusFilter').on('change', function() {
                singleEmailTable.ajax.reload();
            });

            $('#singleSearchFilter').on('keyup', debounce(function() {
                singleEmailTable.ajax.reload();
            }, 500));

            // Reservation Filters
            $('#reservationStatusFilter').on('change', function() {
                reservationTable.ajax.reload();
            });

            $('#reservationSearchFilter').on('keyup', debounce(function() {
                reservationTable.ajax.reload();
            }, 500));

            $('#single').on('shown.bs.tab', function() {
                singleEmailTable.columns.adjust().responsive.recalc();
            });

            $('#reservation-tab').on('shown.bs.tab', function() {
                reservationTable.columns.adjust().draw(false);
            });


        });

        // Debounce function
        function debounce(func, wait) {
            let timeout;
            return function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, arguments), wait);
            };
        }

        // Reset Filters
        function resetSingleFilters() {
            $('#singleStatusFilter').val('');
            $('#singleSearchFilter').val('');
            $('#singleDateFrom').val('');
            $('#singleDateTo').val('');
            singleEmailTable.ajax.reload();
        }

        function resetReservationFilters() {
            $('#reservationStatusFilter').val('');
            $('#reservationSearchFilter').val('');
            $('#reservationDateFrom').val('');
            $('#reservationDateTo').val('');
            reservationTable.ajax.reload();
        }

        // Approve Single Email
        function approveSingleEmail(id) {
            Swal.fire({
                title: 'Approve Application?',
                text: "This will approve the application and ready for processing.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Approve!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loader
                    Swal.fire({
                        title: 'Processing...',
                        html: 'Approving application and sending email.<br><small class="text-muted">This may take a moment.</small>',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: `/admin/applications/${id}/approve-single`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            const fallbackRedirectUrl = `{{ route('leases.create') }}?tenant_id=${response.tenant_id}`;
                            const redirectUrl = response.redirect_url || fallbackRedirectUrl;

                            Swal.fire({
                                icon: 'success',
                                title: 'Approved!',
                                html: `${response.message}<br><small class="text-muted">Redirecting to lease creation...</small>`,
                                confirmButtonColor: '#28a745',
                                timer: 1200,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = redirectUrl;
                            });
                            singleEmailTable.ajax.reload();
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed!',
                                text: xhr.responseJSON?.message || 'An error occurred',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    });
                }
            });
        }

        // Approve Reservation
        function approveReservation(id) {
            Swal.fire({
                title: 'Approve Reservation?',
                text: "This will create a tenant account and send them a form link.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Approve!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/admin/applications/${id}/approve-reservation`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire('Approved!', response.message, 'success');
                            reservationTable.ajax.reload();
                        },
                        error: function(xhr) {
                            const message = xhr.responseJSON?.message || 'An error occurred';
                            Swal.fire('Error!', message, 'error');
                        }
                    });
                }
            });
        }

        // View Single Email Application Details
        function viewSingleEmailDetails(id) {
            // Show modal
            $('#singleEmailModal').modal('show');

            // Reset modal content to loading state
            $('#singleEmailDetails').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `);

            // Load application details
            $.ajax({
                url: `/admin/applications/${id}`,
                type: 'GET',
                success: function(response) {
                    displaySingleEmailDetails(response);
                },
                error: function(xhr) {
                    $('#singleEmailDetails').html(`
                        <div class="alert alert-danger">
                            <i class="fe fe-alert-triangle me-2"></i>
                            Failed to load application details. Please try again.
                        </div>
                    `);
                }
            });
        }

        // Display Single Email Application Details in Modal
        function displaySingleEmailDetails(application) {
            // Format dates
            const formatDate = (dateStr) => {
                if (!dateStr) return 'N/A';
                return new Date(dateStr).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            };

            // Build document section if documents exist
            let documentsHtml = '';
            const hasDocuments = application.passport_copy_url || application.visa_document_url ||
                application.front_id_document_url || application.back_id_document_url;

            if (hasDocuments) {
                documentsHtml = `
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fe fe-file-text text-info me-2"></i>Documents
                        </h6>
                        <div class="row">
                            ${application.passport_copy_url ? `
                                                <div class="col-md-3 mb-3">
                                                    <small class="text-muted d-block pb-2">Passport Copy</small>
                                                    <a href="${application.passport_copy_url}" target="_blank"
                                                        class="btn btn-sm btn-outline-info d-inline-flex align-items-center">
                                                        <i class="fe fe-eye me-1"></i>
                                                        View
                                                    </a>
                                                </div>
                                            ` : ''}
                            ${application.visa_document_url ? `
                                                <div class="col-md-3 mb-3">
                                                    <small class="text-muted d-block pb-2">Visa Document</small>
                                                    <a href="${application.visa_document_url}" target="_blank"
                                                        class="btn btn-sm btn-outline-info d-inline-flex align-items-center">
                                                        <i class="fe fe-eye me-1"></i>
                                                        View
                                                    </a>
                                                </div>
                                            ` : ''}
                            ${application.front_id_document_url ? `
                                                <div class="col-md-3 mb-3">
                                                    <small class="text-muted d-block pb-2">Front ID</small>
                                                    <a href="${application.front_id_document_url}" target="_blank"
                                                        class="btn btn-sm btn-outline-info d-inline-flex align-items-center">
                                                        <i class="fe fe-eye me-1"></i>
                                                        View
                                                    </a>
                                                </div>
                                            ` : ''}
                            ${application.back_id_document_url ? `
                                                <div class="col-md-3 mb-3">
                                                    <small class="text-muted d-block pb-2">Back ID</small>
                                                    <a href="${application.back_id_document_url}" target="_blank"
                                                        class="btn btn-sm btn-outline-info d-inline-flex align-items-center">
                                                        <i class="fe fe-eye me-1"></i>
                                                        View
                                                    </a>
                                                </div>
                                            ` : ''}
                        </div>
                    </div>
                `;
            }

            // Build employer info section if exists
            let employerHtml = '';
            if (application.employer_info && Object.keys(application.employer_info).length > 0) {
                const employer = application.employer_info;
                console.log(employer);
                employer.forEach(data => {
                    employerinfo = `
                        <div class="row">
                            ${data.company_name ? `
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted d-block">Company Name</small>
                                                    <strong>${data.company_name}</strong>
                                                </div>
                                            ` : 'N/A    '}
                             ${data.job_title ? `
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted d-block">Job Title</small>
                                                    <strong>${data.job_title}</strong>
                                                </div>
                                            ` : 'N/A    '}
                            ${data.employer_contact_person_name ? `
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted d-block">Contact Person</small>
                                                    <strong>${data.employer_contact_person_name}</strong>
                                                </div>
                                            ` : ''}
                            ${data.employer_contact_person_phone ? `
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted d-block">Phone</small>
                                                    <strong>${data.employer_contact_person_phone}</strong>
                                                </div>
                                            ` : ''}
                            ${data.employer_contact_person_email ? `
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted d-block">Email</small>
                                                    <strong>${data.employer_contact_person_email}</strong>
                                                </div>
                                            ` : ''}
                            ${data.company_address ? `
                                                <div class="col-md-12 mb-3">
                                                    <small class="text-muted d-block">Address</small>
                                                    <strong>${data.company_address}</strong>
                                                </div>
                                            ` : ''}
                        </div>
                    `;
                });
                employerHtml = `
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fe fe-briefcase text-info me-2"></i>Summer Employer Information
                        </h6>
                        ${employerinfo}
                    </div>
                `;
            }

            // Build sponsor info section if exists
            let sponsorHtml = '';
            if (application.sponsor_name) {
                sponsorHtml = `
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fe fe-users text-info me-2"></i>Sponsor Information
                        </h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Sponsor Name</small>
                                <strong>${application.sponsor_name || 'N/A'}</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Phone</small>
                                <strong>${application.sponsor_phone || 'N/A'}</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Email</small>
                                <strong>${application.sponsor_email || 'N/A'}</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Address</small>
                                <strong>${[
                                    application.sponsor_city,
                                    application.sponsor_state,
                                    application.sponsor_zipcode,
                                    application.sponsor_country
                                ].filter(Boolean).join(', ') || 'N/A'}</strong>
                            </div>
                            ${application.is_j1_sponsor ? `
                                                <div class="col-md-12 mb-3">
                                                    <small class="text-muted d-block">J-1 Sponsor</small>
                                                    <span class="badge bg-info">${application.is_j1_sponsor}</span>
                                                </div>
                                            ` : ''}
                        </div>
                    </div>
                `;
            }

            const html = `
                <div class="application-details">
                    <!-- Personal Information -->
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fe fe-user text-info me-2"></i>Personal Information
                        </h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Full Name</small>
                                <strong>${[application.first_name, application.middle_name, application.last_name].filter(Boolean).join(' ') || 'N/A'}</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Email</small>
                                <strong>${application.email || 'N/A'}</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Phone</small>
                                <strong>${application.phone || 'N/A'}</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Date of Birth</small>
                                <strong>${formatDate(application.date_of_birth)}</strong>
                            </div>

                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Gender</small>
                                <strong>${application.gender || 'N/A'}</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Country of Origin</small>
                                <strong>${application.country_of_origin || 'N/A'}</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Interested in Property</small>
                                <strong class="badge bg-info p-3">${application.property.name || 'N/A'}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Travel Information -->
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fe fe-calendar text-info me-2"></i>Travel Information
                        </h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Estimated Arrival Date</small>
                                <strong>${formatDate(application.arrival_date)}</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Estimated Departure Date</small>
                                <strong>${formatDate(application.departure_date)}</strong>
                            </div>
                        </div>
                    </div>

                    ${documentsHtml}
                    ${employerHtml}
                    ${sponsorHtml}

                    <!-- Additional Notes -->
                    ${application.notes ? `
                                        <div class="mb-4">
                                            <h6 class="border-bottom pb-2 mb-3">
                                                <i class="fe fe-message-square text-info me-2"></i>Additional Notes
                                            </h6>
                                            <div class="alert alert-light border">
                                                ${application.notes}
                                            </div>
                                        </div>
                                    ` : ''}

                    <!-- Application Status -->
                    <div class="mb-3">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fe fe-info text-info me-2"></i>Application Status
                        </h6>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <small class="text-muted d-block">Status</small>
                                <span class="badge p-2 bg-${application.status === 'pending' ? 'warning' : application.status === 'approved' ? 'success' : 'danger'}">
                                    ${application.status.charAt(0).toUpperCase() + application.status.slice(1).replace('_', ' ')}
                                </span>
                            </div>
                            ${application.application_type ? `
                                                <div class="col-md-4 mb-2">
                                                    <small class="text-muted d-block">Application Type</small>
                                                    <strong>${application.application_type}</strong>
                                                </div>
                                            ` : ''}
                            ${application.application_number ? `
                                                <div class="col-md-4 mb-2">
                                                    <small class="text-muted d-block">Application Number</small>
                                                    <strong>${application.application_number}</strong>
                                                </div>
                                            ` : ''}
                            <div class="col-md-4 mb-2">
                                <small class="text-muted d-block">Submitted At</small>
                                <strong>${new Date(application.created_at).toLocaleString()}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $('#singleEmailDetails').html(html);

            // Show/hide action buttons based on status
            if (application.status === 'pending') {
                $('#approveFromModalBtn').removeClass('d-none').off('click').on('click', function() {
                    $('#singleEmailModal').modal('hide');
                    approveSingleEmail(application.id);
                });
                $('#rejectFromModalBtn').removeClass('d-none').off('click').on('click', function() {
                    $('#singleEmailModal').modal('hide');
                    rejectApplication(application.id);
                });
            } else {
                $('#approveFromModalBtn').addClass('d-none');
                $('#rejectFromModalBtn').addClass('d-none');
            }
        }

        let currentReservationId = null;

        // View Reservation Details
        function seeReservation(id) {
            currentReservationId = id;

            // Show modal with loading state
            $('#reservationModal').modal('show');
            $('#reservationDetails').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `);

            // Load reservation details from dedicated endpoint
            $.ajax({
                url: `/admin/reservation-requests/${id}`,
                type: 'GET',
                success: function(response) {
                    displayReservationDetails(response);
                },
                error: function(xhr) {
                    $('#reservationDetails').html(`
                        <div class="alert alert-danger">
                            <i class="fe fe-alert-triangle me-2"></i>
                            Failed to load reservation details. Please try again.
                        </div>
                    `);
                }
            });
        }

        // Display Reservation Details in Modal
        function displayReservationDetails(application) {
            const reservationItems = application.reservation_item ? JSON.parse(application.reservation_item) : [];
            // const reservationItems = application.reservation_item ?? [];


            let reservationItemsHtml = '';
            if (reservationItems.length > 0) {
                reservationItemsHtml = reservationItems.map((item, index) => `
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6 class="text-primary mb-3">Reservation Item #${index + 1}</h6>
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <small class="text-muted d-block">Property Name</small>
                                    <strong>${item.property_name || 'N/A'}</strong>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <small class="text-muted d-block">Interested Type 1</small>
                                    <strong>${item.interested1 || 'N/A'}</strong>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <small class="text-muted d-block">Interested Type 2</small>
                                    <strong>${item.interested2 || 'N/A'}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                reservationItemsHtml = '<p class="text-muted">No reservation items available.</p>';
            }

            const html = `
                <div class="reservation-details">
                    <!-- Company Information -->
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fe fe-briefcase text-primary me-2"></i>Company Information
                        </h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Company Name</small>
                                <strong>${application.company_name || 'N/A'}</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Industry</small>
                                <strong>${application.industry || 'N/A'}</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Employee Count</small>
                                <strong>${application.employee_count || 'N/A'}</strong>
                            </div>
                            <div class="col-md-12 mb-3">
                                <small class="text-muted d-block">Company Address</small>
                                <strong>${application.company_address || 'N/A'}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Person Information -->
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fe fe-user text-primary me-2"></i>Contact Person
                        </h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Full Name</small>
                                <strong>${application.first_name || ''} ${application.middle_name || ''} ${application.last_name || ''}</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Job Title</small>
                                <strong>${application.job_title || 'N/A'}</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Email</small>
                                <strong>${application.email || 'N/A'}</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block">Phone</small>
                                <strong>${application.phone || 'N/A'}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Reservation Items -->
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fe fe-list text-primary me-2"></i>Reservation Items
                        </h6>
                        ${reservationItemsHtml}
                    </div>

                    <!-- Additional Notes -->
                    ${application.notes ? `
                                        <div class="mb-3">
                                            <h6 class="border-bottom pb-2 mb-3">
                                                <i class="fe fe-message-square text-primary me-2"></i>Additional Notes
                                            </h6>
                                            <div class="alert alert-info">
                                                ${application.notes}
                                            </div>
                                        </div>
                                    ` : ''}

                    <!-- Application Info -->
                    <div class="mb-3">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fe fe-info text-primary me-2"></i>Application Status
                        </h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <small class="text-muted d-block">Status</small>
                                <span class="badge bg-${application.status === 'pending' ? 'warning' : application.status === 'approved' ? 'success' : 'danger'}">
                                    ${application.status.charAt(0).toUpperCase() + application.status.slice(1)}
                                </span>
                            </div>
                            <div class="col-md-6 mb-2">
                                <small class="text-muted d-block">Submitted At</small>
                                <strong>${new Date(application.created_at).toLocaleString()}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $('#reservationDetails').html(html);

            // Update contact button
            $('#contactApplicantBtn').off('click').on('click', function() {
                window.location.href = `mailto:${application.email}`;
            });
        }

        // Update Reservation Status
        function updateReservationStatus(id, status) {
            $.ajax({
                url: `/admin/reservation-requests/${id}/status`,
                type: 'POST',
                data: {
                    status: status,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Status Updated!',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    reservationTable.ajax.reload(false);
                    // Refresh modal if still open
                    if ($('#reservationModal').hasClass('show') && currentReservationId === id) {
                        seeReservation(id);
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to update status.'
                    });
                }
            });
        }

        // Reject Application
        function rejectApplication(id) {
            Swal.fire({
                title: 'Reject Application?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Reject!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Processing...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: `/admin/applications/${id}/reject`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Rejected!',
                                text: response.message
                            });
                            singleEmailTable.ajax.reload();
                            reservationTable.ajax.reload();
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: xhr.responseJSON?.message || 'An error occurred'
                            });
                        }
                    });
                }
            });
        }

        // Show Invitation Modal
        function showInviteModal() {
            $('#inviteEmail').val('').removeClass('is-invalid');
            $('#inviteEmailError').text('');
            $('#inviteModal').modal('show');
        }

        // Send Invitation
        function sendInvitation() {
            const email = $('#inviteEmail').val().trim();

            // Basic validation
            $('#inviteEmail').removeClass('is-invalid');
            if (!email) {
                $('#inviteEmail').addClass('is-invalid');
                $('#inviteEmailError').text('Email is required.');
                return;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                $('#inviteEmail').addClass('is-invalid');
                $('#inviteEmailError').text('Please enter a valid email address.');
                return;
            }

            // Show loading
            const btn = $('#inviteSubmitBtn');
            btn.prop('disabled', true);
            btn.find('.btn-text').addClass('d-none');
            btn.find('.spinner-border').removeClass('d-none');

            $.ajax({
                url: "{{ route('tenants.applications.invite') }}",
                type: 'POST',
                data: {
                    email: email,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    btn.prop('disabled', false);
                    btn.find('.btn-text').removeClass('d-none');
                    btn.find('.spinner-border').addClass('d-none');

                    if (response.success) {
                        $('#inviteModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Invitation Sent!',
                            html: `Form link has been sent to <strong>${email}</strong>`,
                            confirmButtonColor: '#28a745'
                        });
                        singleEmailTable.ajax.reload();
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false);
                    btn.find('.btn-text').removeClass('d-none');
                    btn.find('.spinner-border').addClass('d-none');

                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        const firstError = Object.values(errors)[0][0];
                        $('#inviteEmail').addClass('is-invalid');
                        $('#inviteEmailError').text(firstError);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed!',
                            text: xhr.responseJSON?.message || 'An error occurred'
                        });
                    }
                }
            });
        }

        function showInvitationListModal() {
            $('#invitationListModal').modal('show');

            // Load invitations via AJAX
            $.ajax({
                url: "{{ route('tenants.applications.invitations') }}",
                type: 'GET',
                success: function(response) {
                    const invitations = response.data || [];

                    let html = '';

                    if (invitations.length > 0) {
                        console.log(invitations);
                        html = `
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        List of sent invitations:
                                    </div>
                                </li>
                                ${invitations.map(invite => `
                                                    <li class="list-group-item d-flex justify-content-between align-items-center bottom-border">
                                                        <div>
                                                            <i class="fe fe-mail text-info me-2"></i>
                                                            ${invite.email}
                                                        </div>
                                                        <div class="text-muted small">
                                                            Created At: ${formatDate(invite.created_at)} <br>
                                                            Expire At: <span class="text-danger"> ${formatDate(invite.expires_at)}</span>
                                                        </div>
                                                    </li>
                                                `).join('')}
                            </ul>
                        `;
                    } else {
                        html = '<p>No invitations found.</p>';
                    }

                    $('#invitationListContent').html(html);
                },
                error: function(xhr) {
                    $('#invitationListContent').html(`
                        <div class="alert alert-danger">
                            <i class="fe fe-alert-triangle me-2"></i>
                            Failed to load invitations. Please try again.
                        </div>
                    `);
                }
            });
        }

        function formatDate(dateStr) {
            return new Date(dateStr).toLocaleString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
        }
    </script>
