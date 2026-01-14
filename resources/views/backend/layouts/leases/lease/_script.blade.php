
<script>
    $('.datepicker2').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true,
        width: 300
    });

        let currentStep = 1;
        const totalSteps = 5;
        let leaseData = {
            property_id: null,
            unit_id: null,
            room_id: null,
            bed_id: null,
            lease_term_id: null,
            lease_type: 'fixed',
            start_date: null,
            end_date: null
        };

        $(document).ready(function() {
            updateNavigationButtons();
            initializeSelect2();
            setupPropertyHierarchy();
            setupLeaseTermHandler();
            setupCustomPaymentHandler();

            // Form field changes
            $('#start_date, #end_date').on('change', function() {
                leaseData.start_date = $('#start_date').val();
                leaseData.end_date = $('#end_date').val();
                updateRentalSummary();
                validateStep1();
            });
            $('#rent_amount').on('input', updateRentalSummary);
            $('#deposit_amount').on('input', updateRentalSummary);
        });

        function initializeSelect2() {
            if ($('.select3').length && typeof $.fn.select2 !== 'undefined') {
                $('.select3').select2({
                    placeholder: 'Select Property',
                    allowClear: true,
                    width: '100%'
                });
            }
        }

        function setupPropertyHierarchy() {
            // Property selection - Load units
            $('#property_id').on('change', function() {
                const propertyId = $(this).val();
                leaseData.property_id = propertyId;
                
                // Reset dependent fields
                $('#unit_id').html('<option value="">Loading units...</option>').prop('disabled', true);
                $('#room_id').html('<option value="">Select Unit First</option>').prop('disabled', true);
                $('#bed_id').html('<option value="">Select Room First</option>').prop('disabled', true);
                $('#selectedPropertyInfo').hide();
                validateStep1();

                if (propertyId) {
                    // Fetch units - using backend route pattern
                    $.ajax({
                        url: `/admin/properties/${propertyId}/units`,
                        method: 'GET',
                        success: function(response) {
                            let options = '<option value="">Select Unit</option>';
                            if (response.data && response.data.length > 0) {
                                response.data.forEach(unit => {
                                    options += `<option value="${unit.id}">${unit.name || unit.unit_number || 'Unit ' + unit.id}</option>`;
                                });
                                $('#unit_id').html(options).prop('disabled', false);
                            } else {
                                $('#unit_id').html('<option value="">No units available</option>');
                            }
                        },
                        error: function() {
                            $('#unit_id').html('<option value="">Error loading units</option>');
                            console.error('Failed to load units');
                        }
                    });
                }
            });

            // Unit selection - Load rooms
            $('#unit_id').on('change', function() {
                const unitId = $(this).val();
                leaseData.unit_id = unitId;
                
                $('#room_id').html('<option value="">Loading rooms...</option>').prop('disabled', true);
                $('#bed_id').html('<option value="">Select Room First</option>').prop('disabled', true);
                $('#selectedPropertyInfo').hide();
                validateStep1();

                if (unitId) {
                    $.ajax({
                        url: `/admin/units/${unitId}/rooms`,
                        method: 'GET',
                        success: function(response) {
                            let options = '<option value="">Select Room</option>';
                            if (response.data && response.data.length > 0) {
                                response.data.forEach(room => {
                                    options += `<option value="${room.id}">${room.name || room.room_number || 'Room ' + room.id}</option>`;
                                });
                                $('#room_id').html(options).prop('disabled', false);
                            } else {
                                $('#room_id').html('<option value="">No rooms available</option>');
                            }
                        },
                        error: function() {
                            $('#room_id').html('<option value="">Error loading rooms</option>');
                            console.error('Failed to load rooms');
                        }
                    });
                }
            });

            // Room selection - Load beds
            $('#room_id').on('change', function() {
                const roomId = $(this).val();
                leaseData.room_id = roomId;
                
                $('#bed_id').html('<option value="">Loading beds...</option>').prop('disabled', true);
                $('#selectedPropertyInfo').hide();
                validateStep1();

                if (roomId) {
                    $.ajax({
                        url: `/admin/rooms/${roomId}/beds`,
                        method: 'GET',
                        success: function(response) {
                            let options = '<option value="">Select Bed</option>';
                            if (response.data && response.data.length > 0) {
                                response.data.forEach(bed => {
                                    options += `<option value="${bed.id}">${bed.bed_label || bed.bed_number || 'Bed ' + bed.id}</option>`;
                                });
                                $('#bed_id').html(options).prop('disabled', false);
                            } else {
                                $('#bed_id').html('<option value="">No beds available</option>');
                            }
                        },
                        error: function() {
                            $('#bed_id').html('<option value="">Error loading beds</option>');
                            console.error('Failed to load beds');
                        }
                    });
                }
            });

            // Bed selection - Update display
            $('#bed_id').on('change', function() {
                const bedId = $(this).val();
                leaseData.bed_id = bedId;
                
                if (bedId) {
                    const propertyName = $('#property_id option:selected').text();
                    const unitName = $('#unit_id option:selected').text();
                    const roomName = $('#room_id option:selected').text();
                    const bedName = $('#bed_id option:selected').text();
                    
                    $('#fullPropertyPath').text(`${propertyName} → ${bedName}`);
                    // $('#fullPropertyPath').text(`${propertyName} → ${unitName} → ${roomName} → ${bedName}`);
                    $('#selectedPropertyInfo').show();
                    $('#summaryUnit').text(`${bedName}`);
                }
                validateStep1();
            });
        }

        // Custom Payment Schedule Handler
        let customPayments = [];
        let customPaymentCounter = 0;

        function setupCustomPaymentHandler() {
            // Toggle custom payment section based on payment frequency
            $('#payment_frequency').on('change', function() {
                const frequency = $(this).val();
                
                if (frequency === 'CUSTOM') {
                    $('#customPaymentSection').slideDown();
                    $('#standardDueDayContainer').closest('.col-md-6').hide();
                    $('#standardFirstInvoiceContainer').hide();
                    $('#due_day').prop('required', false);
                    $('#first_invoice_date').prop('required', false);
                } else {
                    $('#customPaymentSection').slideUp();
                    $('#standardDueDayContainer').closest('.col-md-6').show();
                    $('#standardFirstInvoiceContainer').show();
                    $('#due_day').prop('required', true);
                    $('#first_invoice_date').prop('required', true);
                }
                
                updateRentalSummary();
            });

            // Add custom payment button
            $('#addCustomPaymentBtn').on('click', function() {
                addCustomPaymentEntry();
            });
        }

        function addCustomPaymentEntry(dueDate = '', amount = '', description = '') {
            customPaymentCounter++;
            const rentAmount = parseFloat($('#rent_amount').val()) || 0;
            const defaultAmount = amount || rentAmount;
            
            const paymentHtml = `
                <div class="custom-payment-entry mb-2" data-payment-id="${customPaymentCounter}">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fe fe-calendar"></i></span>
                                <input type="date" class="form-control custom-payment-date" 
                                    value="${dueDate}" placeholder="Due Date" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control custom-payment-amount" 
                                    value="${defaultAmount}" min="0" step="0.01" placeholder="Amount" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control form-control-sm custom-payment-description" 
                                value="${description}" placeholder="Description (optional)">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeCustomPaymentEntry(${customPaymentCounter})">
                                <i class="fe fe-trash-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            $('#customPaymentsList').append(paymentHtml);
            $('#noCustomPaymentsAlert').hide();
            $('#customPaymentsSummary').show();
            
            updateCustomPaymentsSummary();
            
            // Bind change events
            $(`[data-payment-id="${customPaymentCounter}"]`).find('.custom-payment-amount').on('input', function() {
                updateCustomPaymentsSummary();
            });
        }

        function removeCustomPaymentEntry(paymentId) {
            $(`[data-payment-id="${paymentId}"]`).remove();
            
            if ($('#customPaymentsList .custom-payment-entry').length === 0) {
                $('#noCustomPaymentsAlert').show();
                $('#customPaymentsSummary').hide();
            }
            
            updateCustomPaymentsSummary();
        }

        function updateCustomPaymentsSummary() {
            let total = 0;
            let count = 0;
            
            $('#customPaymentsList .custom-payment-entry').each(function() {
                const amount = parseFloat($(this).find('.custom-payment-amount').val()) || 0;
                total += amount;
                count++;
            });
            
            $('#customPaymentsCount').text(count);
            $('#customPaymentsTotal').text(total.toFixed(2));
        }

        function getCustomPayments() {
            const payments = [];
            
            $('#customPaymentsList .custom-payment-entry').each(function() {
                const dueDate = $(this).find('.custom-payment-date').val();
                const amount = parseFloat($(this).find('.custom-payment-amount').val()) || 0;
                const description = $(this).find('.custom-payment-description').val();
                
                if (dueDate && amount > 0) {
                    payments.push({
                        due_date: dueDate,
                        amount: amount,
                        description: description || 'Custom Payment'
                    });
                }
            });
            
            return payments;
        }

        function setupLeaseTermHandler() {
            $('#lease_term_type').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const termId = $(this).val();
                const termType = selectedOption.data('type') || 'fixed';
                
                leaseData.lease_term_id = termId;
                leaseData.lease_type = termType;
                leaseData.start_date = selectedOption.data('start') || null;
                leaseData.end_date = selectedOption.data('end') || null;
                // console.log(leaseData);
                

                if (!termId) {
                    $('#leaseTypeDisplay').hide();
                    $('#dateSelectionSection').hide();
                    $('#switchToMonthSection').hide();
                    validateStep1();
                    return;
                }

                // Show lease type card
                $('#leaseTypeDisplay').show();
                $('#fixedTermCard').hide();
                $('#monthToMonthCard').hide();

                if (termType === 'fixed') {
                    $('#fixedTermCard').show().addClass('active');
                    handleFixedTermSelection();
                } else {
                    $('#monthToMonthCard').show().addClass('active');
                    // handleMonthToMonthSelection();
                }

                $('#dateSelectionSection').show();
                validateStep1();
            });
        }

        function handleFixedTermSelection() {
            // Auto-select dates for fixed term
            const today = new Date();
            // console.log(leaseData);
            
            const startDate = leaseData.start_date ? new Date(leaseData.start_date) : new Date(today);
            
            
            const endDate = leaseData.end_date ? new Date(leaseData.end_date) : new Date(startDate);
            // endDate.setFullYear(startDate.getFullYear() + 1); // 1 year lease
            
            $('#start_date').val(formatDateForInput(startDate));
            $('#end_date').val(formatDateForInput(endDate)).prop('readonly', false);
            
            $('#endDateField').show();
            $('#endDateRequired').show();
            $('#end_date').prop('required', true);
            $('#switchToMonthSection').show();
            
            $('#startDateHint').text('Lease start date (auto-selected 7 days from today, can be modified)');
            $('#endDateHint').text('Lease end date (auto-selected for 1 year, can be modified)');
            
            leaseData.start_date = formatDateForInput(startDate);
            leaseData.end_date = formatDateForInput(endDate);
            leaseData.lease_type = 'fixed';
            
            updateRentalSummary();
            validateStep1();
        }

        function handleMonthToMonthSelection() {
            // Manual date selection for month-to-month
            const today = new Date();
            const startDate = new Date(today);
            startDate.setDate(today.getDate() + 7);
            
            $('#start_date').val(formatDateForInput(startDate)).prop('readonly', false);
            $('#end_date').val('').prop('readonly', true).prop('required', false);
            
            $('#endDateField').show();
            $('#endDateRequired').hide();
            $('#switchToMonthSection').hide();
            
            $('#startDateHint').text('Lease start date (auto-selected 7 days from today, can be modified)');
            $('#endDateHint').text('No end date - lease will automatically renew monthly');
            
            leaseData.start_date = formatDateForInput(startDate);
            leaseData.end_date = null;
            leaseData.lease_type = 'month_to_month';
            
            updateRentalSummary();
            validateStep1();
        }

        function formatDateForInput(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function validateStep1() {
            const isValid = leaseData.property_id && 
                          leaseData.unit_id && 
                          leaseData.room_id && 
                          leaseData.bed_id && 
                          leaseData.lease_term_id && 
                          leaseData.start_date && 
                          (leaseData.lease_type === 'month_to_month' || leaseData.end_date);
            
            $('#step1NextBtn').prop('disabled', !isValid);
            return isValid;
        }

        function validateAndNextStep(step) {
            if (currentStep === 1) {
                if (!validateStep1()) {
                    Swal.fire({
                        title: "Form Incomplete",
                        text: 'Please complete all required fields before proceeding.',
                        icon: "question"
                    });
                    // alert('Please complete all required fields before proceeding.');
                    return;
                }
            }
            nextStep(step);
        }

        function handleLeaseTypeChange(type) {
            // Kept for compatibility
            if (type === 'month') {
                $('#endDateField').show();
                $('#end_date').val('').prop('readonly', true).prop('required', false);
                $('#switchToMonthSection').hide();
                $('#summaryEndDateContainer').hide();
                $('#leaseDuration').text('for Month-to-Month lease');
                $('#endDateHint').text('No end date - lease will renew monthly');
            } else {
                $('#endDateField').show();
                $('#end_date').prop('readonly', false).prop('required', true);
                $('#switchToMonthSection').show();
                $('#summaryEndDateContainer').show();
                $('#endDateHint').text('Lease end date');
                updateRentalSummary();
            }
        }

        function updateRentalSummary() {
            const startDate = $('#start_date').val();
            const endDate = $('#end_date').val();
            const rentAmount = parseFloat($('#rent_amount').val()) || 0;
            const depositAmount = parseFloat($('#deposit_amount').val()) || 0;

            // Update summary
            $('#summaryStartDate').text(startDate ? formatDate(startDate) : 'N/A');
            
            if (leaseData.lease_type === 'month_to_month') {
                $('#summaryEndDate').text('No End Date (Month-to-Month)');
                $('#summaryEndDateContainer').show();
                $('#summaryLeaseType').text('Month-to-Month');
                $('#leaseDuration').text('for Month-to-Month lease');
            } else {
                $('#summaryEndDate').text(endDate ? formatDate(endDate) : 'N/A');
                $('#summaryEndDateContainer').show();
                $('#summaryLeaseType').text('Fixed Term');
            }

            if (currentStep >= 2) {
                $('#summaryRent').text('$' + rentAmount.toFixed(2) + '/month');
                $('#summaryRentContainer').show();
                $('#summaryDeposit').text('$' + depositAmount.toFixed(2));
                $('#summaryDepositContainer').show();
                $('#summaryInvoices').show();
            }

            // Calculate duration for fixed term
            if (startDate && endDate && leaseData.lease_type === 'fixed') {
                const start = new Date(startDate);
                const end = new Date(endDate);
                const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1; // +1 for inclusive
                
                // Calculate actual calendar months difference
                const months = calculateCalendarMonths(start, end);
                $('#leaseDuration').text(`for ${months} month${months > 1 ? 's' : ''} (${days} days)`);

                // Calculate total rent
                const totalRent = rentAmount * months + depositAmount;
                $('.summary-amount h2').text('$' + totalRent.toFixed(2));
            } else if (leaseData.lease_type === 'month_to_month') {
                $('.summary-amount h2').text('$' + (rentAmount + depositAmount).toFixed(2));
            }
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const options = { year: 'numeric', month: 'short', day: '2-digit' };
            return date.toLocaleDateString('en-US', options);
        }

        function calculateCalendarMonths(startDate, endDate) {
            // Calculate actual calendar months between two dates
            // April 1 to July 31 = 4 months (April, May, June, July)
            let months = (endDate.getFullYear() - startDate.getFullYear()) * 12;
            months += endDate.getMonth() - startDate.getMonth();
            
            // Add 1 for inclusive month counting (covers both start and end months)
            months += 1;
            
            return Math.max(1, months); // At least 1 month
        }

        function nextStep(step) {
            if (step) {
                currentStep = step;
            } else {
                if (currentStep < totalSteps) {
                    currentStep++;
                }
            }

            // Update step-specific data
            if (currentStep === 2) {
                populateStep2Data();
            } else if (currentStep === 3) {
                populateStep3Data();
            } else if (currentStep === 4) {
                populateStep4Data();
            }

            showStep(currentStep);
            updateNavigationButtons();
            updateRentalSummary();
        }

        function populateStep2Data() {
            // Auto-populate deposit due date (same as lease start date)
            if (leaseData.start_date) {
                $('#deposit_due_date').val(leaseData.start_date);
            }

            // Auto-populate first invoice date
            if (leaseData.start_date) {
                const startDate = new Date(leaseData.start_date);
                const dueDay = parseInt($('#due_day').val());
                
                // Set first invoice to the first occurrence of due day after start date
                let firstInvoiceDate = new Date(startDate);
                firstInvoiceDate.setDate(dueDay);
                
                // If due day is before start date, move to next month
                if (firstInvoiceDate < startDate) {
                    firstInvoiceDate.setMonth(firstInvoiceDate.getMonth() + 1);
                }
                
                $('#first_invoice_date').val(formatDateForInput(firstInvoiceDate));
            }

            // Update property info banner
            if (leaseData.bed_id) {
                const propertyName = $('#property_id option:selected').text();
                const unitName = $('#unit_id option:selected').text();
                const roomName = $('#room_id option:selected').text();
                const bedName = $('#bed_id option:selected').text();
                $('#step2PropertyInfo').html(`<strong>${propertyName}</strong> → ${bedName}`);
            }
        }

        function populateStep3Data() {
            // Update property info for step 3
            if (leaseData.bed_id) {
                const propertyName = $('#property_id option:selected').text();
                const unitName = $('#unit_id option:selected').text();
                const roomName = $('#room_id option:selected').text();
                const bedName = $('#bed_id option:selected').text();
                $('#unitNameInfo').html(`<strong>${propertyName}</strong> →  ${bedName}`);
                // $('#unitNameInfo').text(`${unitName} - ${roomName} - ${bedName}`);
            }

            // Update rent and deposit display
            const rentAmount = parseFloat($('#rent_amount').val()) || 0;
            const depositAmount = parseFloat($('#deposit_amount').val()) || 0;
            $('#rentDepositDisplay').text(`$${rentAmount.toFixed(2)} Rent/$${depositAmount.toFixed(2)} Deposit`);

            $('#partialPayment').off('change').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#partialPaymentBadge').removeClass('bg-danger').addClass('bg-success').text('On');
                } else {
                    $('#partialPaymentBadge').removeClass('bg-success').addClass('bg-danger').text('Off');
                }
            });

            // Load active tenants into dropdown
            loadActiveTenants();

            // Setup tenant management events
            setupTenantManagement();
        }

        function populateStep4Data() {
            // Property Summary
            const propertyName = $('#property_id option:selected').text();
            const unitName = $('#unit_id option:selected').text();
            const roomName = $('#room_id option:selected').text();
            const bedName = $('#bed_id option:selected').text();

            $('#finalPropertyName').text(propertyName || 'N/A');
            $('#finalUnitInfo').text(`${bedName}`);
            // $('#finalUnitInfo').text(`${unitName} - ${roomName} - ${bedName}`);
            // Lease Type & Period
            const leaseTermText = $('#lease_term_type option:selected').text();
            const leaseTypeText = leaseData.lease_type === 'month_to_month' ? 'Month-to-Month' : 'Fixed Term';
            $('#finalLeaseType').text(`${leaseTermText} (${leaseTypeText})`);

            // Format dates
            const startDate = leaseData.start_date ? formatDate(leaseData.start_date) : 'N/A';
            const endDate = leaseData.lease_type === 'month_to_month' ? 'No End Date' : (leaseData.end_date ? formatDate(leaseData.end_date) : 'N/A');
            $('#finalLeasePeriod').text(`${startDate} - ${endDate}`);

            // Tenant Summary
            $('#finalTenantCount').text(selectedTenants.length);
            const tenantListHtml = selectedTenants.length > 0 
                ? selectedTenants.map(t => `<span class="tenant-chip"><i class="fe fe-user"></i>${t.first_name} ${t.last_name}</span>`).join('')
                : '<span class="text-muted">No tenants added</span>';
            $('#finalTenantsList').html(tenantListHtml);

            // Rent Summary
            const rentAmount = parseFloat($('#rent_amount').val()) || 0;
            const depositAmount = parseFloat($('#deposit_amount').val()) || 0;
            $('#finalRentAmount').text(`$${rentAmount.toFixed(2)}/month`);
            $('#finalDepositAmount').text(`$${depositAmount.toFixed(2)}`);

            // Payment Details
            const paymentFrequency = $('#payment_frequency option:selected').text() || 'Monthly';
            const dueDay = $('#due_day').val() || '1';
            const firstInvoice = $('#first_invoice_date').val() ? formatDate($('#first_invoice_date').val()) : '-';
            const partialPayment = $('#partialPayment').is(':checked') ? 'Allowed' : 'Not Allowed';

            $('#finalPaymentFrequency').text(paymentFrequency);
            $('#finalDueDay').text(dueDay + getSuffix(parseInt(dueDay)) + ' of month');
            $('#finalFirstInvoice').text(firstInvoice);
            $('#finalPartialPayment').text(partialPayment);
        }

        function getSuffix(day) {
            if (day >= 11 && day <= 13) return 'th';
            switch (day % 10) {
                case 1: return 'st';
                case 2: return 'nd';
                case 3: return 'rd';
                default: return 'th';
            }
        }

        // Tenant Management Functions
        let selectedTenants = [];

        function setupTenantManagement() {
            // Add existing tenant button
            $('#addExistingTenantBtn').off('click').on('click', function() {
                const tenantId = $('#existingTenantSelect').val();
                if (!tenantId) {
                    Swal.fire({
                        title: "Tenant Not Selected",
                        text: "Please select a tenant before adding.",
                        icon: "warning"
                    });
                    return;
                }

                // Check if already added
                if (selectedTenants.find(t => t.id == tenantId)) {
                    Swal.fire({
                        title: "Already Added",
                        text: "This tenant has already been added to this lease.",
                        icon: "warning"
                    });
                    return;
                }

                // Get tenant data from dropdown
                const selectedOption = $('#existingTenantSelect option:selected');
                const tenantData = {
                    id: tenantId,
                    first_name: selectedOption.data('firstname'),
                    last_name: selectedOption.data('lastname'),
                    email: selectedOption.data('email'),
                    phone: selectedOption.data('phone'),
                    status: selectedOption.data('status')
                };

                addTenantToList(tenantData);
            });

            // Create new tenant button
            $('#createNewTenantBtn').off('click').on('click', function() {
                const firstName = $('#newTenantFirstName').val().trim();
                const lastName = $('#newTenantLastName').val().trim();
                const email = $('#newTenantEmail').val().trim();
                const phone = $('#newTenantPhone').val().trim();

                // Validation
                if (!firstName || !lastName || !email || !phone) {
                    Swal.fire({
                        title: "Incomplete Information",
                        text: "Please fill in all required fields.",
                        icon: "warning"
                    });
                    return;
                }

                // Email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    Swal.fire({
                        title: "Invalid Email",
                        text: "Please enter a valid email address.",
                        icon: "warning"
                    });
                    return;
                }

                // Create new tenant via AJAX
                const btn = $(this);
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

                $.ajax({
                    url: '/admin/tenants/quick-create',
                    method: 'POST',
                    data: {
                        first_name: firstName,
                        last_name: lastName,
                        email: email,
                        phone: phone,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            // Add to selected tenants
                            addTenantToList(response.data);

                            // Clear form
                            $('#newTenantFirstName, #newTenantLastName, #newTenantEmail, #newTenantPhone').val('');

                            // Reload tenant dropdown
                            loadActiveTenants();
                            Swal.fire({
                                title: "Tenant Created",
                                text: "The tenant has been successfully created.",
                                icon: "success"
                            });
                        } else {
                            Swal.fire({
                                title: "Creation Failed",
                                text: "Failed to create tenant." + (response.message ? ' ' + response.message : ''),
                                icon: "error"
                            });
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Failed to create tenant';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                                title: "Error",
                                text: errorMsg,
                                icon: "error"
                            });
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="fe fe-save me-1"></i> Save & Add Tenant');
                    }
                });
            });
        }

        function loadActiveTenants() {
            $.ajax({
                url: '/admin/tenants/0/active',
                method: 'GET',
                success: function(response) {
                    let options = '<option value="">Select a tenant...</option>';
                    if (response.success && response.data && response.data.length > 0) {
                        response.data.forEach(tenant => {
                            options += `<option value="${tenant.id}" 
                                data-firstname="${tenant.first_name || ''}" 
                                data-lastname="${tenant.last_name || ''}" 
                                data-email="${tenant.email || ''}" 
                                data-phone="${tenant.phone || ''}"
                                data-status="${tenant.status || 'active'}">
                                ${tenant.first_name} ${tenant.last_name} 
                            </option>`;
                        });
                    }
                    $('#existingTenantSelect').html(options);

                },
                error: function() {
                    console.error('Failed to load active tenants');
                }
            });
        }

        function addTenantToList(tenantData) {
            // Add to array
            selectedTenants.push(tenantData);

            // Update count
            $('#tenantCount').text(selectedTenants.length);

            // Hide no tenants alert, show list
            $('#noTenantsAlert').hide();
            $('#selectedTenantsList').show();

            // Create tenant card
            const card = `
                <div class="col-md-6 mb-3" data-tenant-id="${tenantData.id}">
                    <div class="card tenant-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">
                                        <i class="fe fe-user me-2 text-primary"></i>
                                        ${tenantData.first_name} ${tenantData.last_name}
                                    </h6>
                                    <p class="mb-1 small text-muted">
                                        <i class="fe fe-mail me-1"></i> ${tenantData.email}
                                    </p>
                                    <p class="mb-1 small text-muted">
                                        <i class="fe fe-phone me-1"></i> ${tenantData.phone || 'N/A'}
                                    </p>
                                    <span class="badge bg-success">${tenantData.status || 'Active'}</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-danger" onclick="removeTenantFromList(${tenantData.id})">
                                    <i class="fe fe-trash-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $('#selectedTenantsList').append(card);
        }

        function removeTenantFromList(tenantId) {
            // Remove from array
            selectedTenants = selectedTenants.filter(t => t.id != tenantId);

            // Remove card
            $(`[data-tenant-id="${tenantId}"]`).remove();

            // Update count
            $('#tenantCount').text(selectedTenants.length);

            // Show/hide alerts
            if (selectedTenants.length === 0) {
                $('#noTenantsAlert').show();
                $('#selectedTenantsList').hide();
            }
        }

        function previousStep(step) {
            if (step) {
                currentStep = step;
            } else {
                if (currentStep > 1) {
                    currentStep--;
                }
            }

            showStep(currentStep);
            updateNavigationButtons();
        }

        function showStep(step) {
            // Hide all steps
            $('.step-content').removeClass('active');
            $('.step-item').removeClass('active completed');

            // Show current step
            $(`#step-${step}`).addClass('active');

            // Update step indicators
            for (let i = 1; i <= totalSteps; i++) {
                const $step = $(`.step-item[data-step="${i}"]`);
                if (i < step) {
                    $step.addClass('completed');
                } else if (i === step) {
                    $step.addClass('active');
                }
            }

            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function updateNavigationButtons() {
            // Back button
            if (currentStep === 1) {
                $('#backBtn').hide();
            } else {
                $('#backBtn').show();
            }

            // Next/Save/Add Tenant buttons
            $('#nextBtn').hide();
            $('#saveBtn').hide();
            $('#addTenantBtn').hide();

            if (currentStep === 3) {
                $('#nextBtn').show();
            } else if (currentStep === 4) {
                // Final step - show create lease button
                $('#saveBtn').show();
            } else if (currentStep < totalSteps) {
                $('#nextBtn').show();
            }
        }

        function addTenant() {
            const tenantCount = $('#tenantsTableBody .tenant-row').length + 1;
            const newRow = `
                <tr class="tenant-row">
                    <td>
                        <div class="tenant-number">${tenantCount}</div>
                    </td>
                    <td><input type="text" class="form-control" placeholder="First Name"></td>
                    <td><input type="text" class="form-control" placeholder="Last Name"></td>
                    <td><input type="email" class="form-control" placeholder="Email"></td>
                    <td><input type="tel" class="form-control" placeholder="Phone Number"></td>
                    <td>
                        <select class="form-select">
                            <option>Select Package</option>
                        </select>
                    </td>
                    <td>
                        <span class="badge bg-secondary">Not applied yet</span>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeTenant(this)">
                            <i class="fe fe-x"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#tenantsTableBody').append(newRow);
        }

        function removeTenant(btn) {
            $(btn).closest('tr').remove();

            // Renumber tenants
            $('#tenantsTableBody .tenant-row').each(function(index) {
                $(this).find('.tenant-number').text(index + 1);
            });
        }

        // Lease Submission Functions
        function collectLeaseData() {
            const paymentFrequency = $('#payment_frequency').val() || 'MONTHLY';
            const isCustomPayment = paymentFrequency === 'CUSTOM';
            
            return {
                // Property & Bed
                property_id: leaseData.property_id,
                bed_id: leaseData.bed_id,
                
                // Season/Term
                season_id: leaseData.lease_term_id,
                lease_type: leaseData.lease_type,
                
                // Dates
                start_date: leaseData.start_date,
                end_date: leaseData.lease_type === 'month_to_month' ? null : leaseData.end_date,
                
                // Rent & Deposit
                rent_amount: parseFloat($('#rent_amount').val()) || 0,
                deposit_amount: parseFloat($('#deposit_amount').val()) || 0,
                payment_frequency: paymentFrequency,
                due_day: isCustomPayment ? null : (parseInt($('#due_day').val()) || 1),
                first_invoice_date: isCustomPayment ? null : ($('#first_invoice_date').val() || null),
                deposit_collected: $('#deposit_collected').is(':checked'),
                
                // Custom Payments (only if CUSTOM frequency)
                custom_payments: isCustomPayment ? getCustomPayments() : [],
                
                // Tenants
                tenant_ids: selectedTenants.map(t => t.id),
                
                // Lease Template
                lease_template_id: $('#lease_template_id').val() || null,
                
                // Options
                send_for_signature: $('#sendForSignature').is(':checked'),
                send_welcome_email: $('#sendWelcomeEmail').is(':checked'),
                allow_partial_payment: $('#partialPayment').is(':checked'),
                
                // Notes
                notes: $('#notes').val() || null,
                
                // CSRF Token
                _token: $('meta[name="csrf-token"]').attr('content')
            };
        }

        function validateLeaseData(data) {
            const errors = [];

            if (!data.property_id) errors.push('Please select a property');
            if (!data.bed_id) errors.push('Please select a bed');
            if (!data.season_id) errors.push('Please select a lease term');
            if (!data.start_date) errors.push('Please select a start date');
            if (data.lease_type === 'fixed' && !data.end_date) errors.push('Please select an end date for fixed term lease');
            if (!data.rent_amount || data.rent_amount <= 0) errors.push('Please enter a valid rent amount');
            if (data.tenant_ids.length === 0) errors.push('Please add at least one tenant');
            
            // Custom payment validation
            if (data.payment_frequency === 'CUSTOM') {
                if (!data.custom_payments || data.custom_payments.length === 0) {
                    errors.push('Please add at least one custom payment date');
                }
            }

            return errors;
        }

        function submitLease(saveAsDraft = false) {
            const data = collectLeaseData();
            data.save_as_draft = saveAsDraft;
            console.log(data);
            
            // Validate
            const errors = validateLeaseData(data);
            if (errors.length > 0 && !saveAsDraft) {
                Swal.fire({
                    title: "Validation Error",
                    text: 'Please fix the following errors:\n\n' + errors.join('\n, '),
                    icon: "error"
                });
                // alert();
                return;
            }

            // Confirm submission
            const confirmMsg = saveAsDraft 
                ? 'You want to save this lease as a draft?' 
                : 'You want to create this lease and send it for signing?';
            Swal.fire({
                title: "Are you sure?",
                text: confirmMsg,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, do it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    // Proceed with submission
                    proceedWithSubmission(data, saveAsDraft);
                }
            });
        }

        function proceedWithSubmission(data, saveAsDraft) {
            
            // Show loading state
            const btn = saveAsDraft ? $('#saveDraftBtn') : $('#createLeaseBtn');
            const originalText = btn.html();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Processing...');

            $.ajax({
                url: '/admin/leases/store',
                method: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                                title: "Success",
                                text: response.message,
                                icon: "success"
                            });
                        if (response.redirect_url) {
                            window.location.href = response.redirect_url;
                        }
                    } else {
                        Swal.fire({
                                title: "Hmm...",
                                text:  (response.message || 'Failed to create lease') + '\n\n' + response.errors.join('\n'),
                                icon: "error"
                            });
                        btn.prop('disabled', false).html(originalText);
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'Failed to create lease';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        if (xhr.responseJSON.errors) {
                            const validationErrors = Object.values(xhr.responseJSON.errors).flat();
                            errorMsg += '\n\n' + validationErrors.join('\n');
                        }
                    }
                    Swal.fire({
                        title: "Error",
                        text: errorMsg,
                        icon: "error"
                    });
                    btn.prop('disabled', false).html(originalText);
                }
            });
        }

        // Bind buttons on document ready
        $(document).ready(function() {
            $('#createLeaseBtn').on('click', function() {
                submitLease(false);
            });

            $('#saveDraftBtn').on('click', function() {
                submitLease(true);
            });
        });
    </script>