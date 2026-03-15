<script>
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
        end_date: null,
        actual_move_in: null
    };

    $(document).ready(function() {

        // ─── FIX #2: Date format changed to mm/dd/yy (US format) ───
        $('.datepicker2').each(function() {
            const value = $(this).val();
            $(this).datepicker({
                format: 'mm/dd/yy',
                autoclose: true
            });
            if (value) {
                // value may be yyyy-mm-dd from server, parse safely to avoid UTC shift
                $(this).datepicker('setDate', new Date(value + 'T00:00:00'));
            }
        });

        updateNavigationButtons();
        initializeSelect2();
        setupPropertyHierarchy();
        setupLeaseTermHandler();
        setupCustomPaymentHandler();
        setupAssignBedLaterHandler();

        // ─── FIX #2: Read ISO date from datepicker, not raw .val() ───
        $('#start_date, #end_date').on('change changeDate', function() {
            const pickerDate = $(this).datepicker('getDate');
            const isoVal = pickerDate ? formatDateForInput(pickerDate) : null;
            if ($(this).attr('id') === 'start_date') {
                leaseData.start_date = isoVal;
            } else {
                leaseData.end_date = isoVal;
            }
            updateRentalSummary();
            validateStep1();
        });

        $('#rent_amount').on('input', updateRentalSummary);
        $('#deposit_amount').on('input', updateRentalSummary);
    });

    // ─────────────────────────────────────────────────────────────────
    // Helper: converts JS Date → yyyy-mm-dd  (for backend)
    // ─────────────────────────────────────────────────────────────────
    function formatDateForInput(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // Helper: reads a datepicker element and returns yyyy-mm-dd (safe for backend)
    function getISOFromPicker(selector) {
        const d = $(selector).datepicker('getDate');
        return d ? formatDateForInput(d) : null;
    }

    // ─────────────────────────────────────────────────────────────────
    // Assign Bed Later handler  (unchanged)
    // ─────────────────────────────────────────────────────────────────
    function setupAssignBedLaterHandler() {
        $('#assign_bed_later').on('change', function() {
            const assignLater = $(this).is(':checked');

            if (assignLater) {
                $('#unit_id, #room_id, #bed_id').prop('disabled', true).val('').prop('required', false);
                $('#selectedPropertyInfo').hide();
                $('#pendingBedAssignmentInfo').show();
                $('.bed-required-marker').hide();
                $('#unitFieldContainer').addClass('opacity-50');
                $('#roomBedFieldContainer').addClass('opacity-50');
                leaseData.unit_id = null;
                leaseData.room_id = null;
                leaseData.bed_id = null;
                $('#summaryUnit').text('Pending Assignment');
            } else {
                if (leaseData.property_id) {
                    $('#unit_id').prop('disabled', false);
                }
                $('#pendingBedAssignmentInfo').hide();
                $('.bed-required-marker').show();
                $('#unitFieldContainer').removeClass('opacity-50');
                $('#roomBedFieldContainer').removeClass('opacity-50');
                $('#summaryUnit').text('Not Selected');
            }

            validateStep1();
        });
    }

    function initializeSelect2() {
        if ($('.select3').length && typeof $.fn.select2 !== 'undefined') {
            $('.select3').select2({
                placeholder: 'Select Property',
                allowClear: true,
                width: '100%'
            });
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // Property hierarchy  (unchanged)
    // ─────────────────────────────────────────────────────────────────
    function setupPropertyHierarchy() {
        $('#property_id').on('change', function() {
            const propertyId = $(this).val();
            leaseData.property_id = propertyId;

            $('#unit_id').html('<option value="">Loading units...</option>').prop('disabled', true);
            $('#room_id').html('<option value="">Select Unit First</option>').prop('disabled', true);
            $('#bed_id').html('<option value="">Select Room First</option>').prop('disabled', true);
            $('#selectedPropertyInfo').hide();
            validateStep1();

            if (propertyId) {
                $.ajax({
                    url: `/admin/properties/${propertyId}/units`,
                    method: 'GET',
                    success: function(response) {
                        let options = '<option value="">Select Unit</option>';
                        if (response.data && response.data.length > 0) {
                            response.data.forEach(unit => {
                                options +=
                                    `<option value="${unit.id}">${unit.name || unit.unit_number || 'Unit ' + unit.id}</option>`;
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
                                options +=
                                    `<option value="${room.id}">${room.name || room.room_number || 'Room ' + room.id}</option>`;
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
                                let bedLabel = bed.bed_label || bed.bed_number || 'Bed ' +
                                    bed.id;
                                let bookedInfo = '';

                                if (bed.is_booked && bed.lease_info) {
                                    const leaseInfo = bed.lease_info;
                                    bookedInfo =
                                        ` [BOOKED: ${leaseInfo.start_date_formatted} - ${leaseInfo.end_date_formatted}]`;
                                }

                                options +=
                                    `<option value="${bed.id}" data-booked="${bed.is_booked ? '1' : '0'}" data-lease='${JSON.stringify(bed.lease_info || {})}'>${bedLabel}${bookedInfo}</option>`;
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

        $('#bed_id').on('change', function() {
            const bedId = $(this).val();
            leaseData.bed_id = bedId;

            if (bedId) {
                const propertyName = $('#property_id option:selected').text();
                const bedName = $('#bed_id option:selected').text();

                $('#fullPropertyPath').text(`${propertyName} → ${bedName}`);
                $('#selectedPropertyInfo').show();
                $('#summaryUnit').text(`${bedName}`);
            }
            validateStep1();
        });
    }

    // ─────────────────────────────────────────────────────────────────
    // Custom payment handler
    // ─────────────────────────────────────────────────────────────────
    let customPayments = [];
    let customPaymentCounter = 0;

    function setupCustomPaymentHandler() {
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
                            <input type="text" class="form-control custom-payment-date datepicker2"
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
        updateRentalSummary();

        $(`[data-payment-id="${customPaymentCounter}"]`).find('.custom-payment-amount').on('input', function() {
            updateCustomPaymentsSummary();
            updateRentalSummary();
        });

        // ─── FIX #2: custom payment datepicker also uses mm/dd/yy ───
        $(`[data-payment-id="${customPaymentCounter}"]`).find('.custom-payment-date').each(function() {
            const value = $(this).val();
            $(this).datepicker({
                format: 'mm/dd/yy',
                autoclose: true
            });
            if (value) {
                $(this).datepicker('setDate', new Date(value + 'T00:00:00'));
            }
        });
    }

    function removeCustomPaymentEntry(paymentId) {
        $(`[data-payment-id="${paymentId}"]`).remove();

        if ($('#customPaymentsList .custom-payment-entry').length === 0) {
            $('#noCustomPaymentsAlert').show();
            $('#customPaymentsSummary').hide();
        }

        updateCustomPaymentsSummary();
        updateRentalSummary();
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

    // ─── FIX #2: convert display-format date → ISO for backend ───
    function getCustomPayments() {
        const payments = [];

        $('#customPaymentsList .custom-payment-entry').each(function() {
            const pickerDate = $(this).find('.custom-payment-date').datepicker('getDate');
            const dueDate = pickerDate ? formatDateForInput(pickerDate) : null;
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

    // ─────────────────────────────────────────────────────────────────
    // Lease term handler
    // ─────────────────────────────────────────────────────────────────
    function setupLeaseTermHandler() {
        $('#lease_term_type').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const termId = $(this).val();
            const termType = selectedOption.data('type') || 'fixed';

            leaseData.lease_term_id = termId;
            leaseData.lease_type = termType;
            leaseData.start_date = selectedOption.data('start') || null;
            leaseData.end_date = selectedOption.data('end') || null;

            if (!termId) {
                $('#leaseTypeDisplay').hide();
                $('#dateSelectionSection').hide();
                $('#switchToMonthSection').hide();
                validateStep1();
                return;
            }

            // ─── FIX #2: read actual_move_in via datepicker ───
            $('#actual_move_in').off('change changeDate').on('change changeDate', function() {
                const d = $(this).datepicker('getDate');
                leaseData.actual_move_in = d ? formatDateForInput(d) : null;
                updateRentalSummary();
                validateStep1();
            });

            $('#leaseTypeDisplay').show();
            $('#fixedTermCard').hide();
            $('#monthToMonthCard').hide();

            if (termType === 'fixed') {
                $('#fixedTermCard').show().addClass('active');
                handleFixedTermSelection();
            } else {
                $('#monthToMonthCard').show().addClass('active');
            }

            $('#dateSelectionSection').show();
            validateStep1();
        });
    }

    // ─── FIX #2: use datepicker('setDate') instead of .val() ───
    function handleFixedTermSelection() {
        const startDate = leaseData.start_date ?
            new Date(leaseData.start_date + 'T00:00:00') :
            new Date();

        const endDate = leaseData.end_date ?
            new Date(leaseData.end_date + 'T00:00:00') :
            new Date(startDate);

        $('#start_date').datepicker('setDate', startDate);
        $('#end_date').datepicker('setDate', endDate).prop('readonly', false);

        $('#endDateField').show();
        $('#endDateRequired').show();
        $('#end_date').prop('required', true);
        $('#switchToMonthSection').show();

        $('#startDateHint').text('Lease start date (auto-selected, can be modified)');
        $('#endDateHint').text('Lease end date (auto-selected, can be modified)');

        // Store as ISO for backend
        leaseData.start_date = formatDateForInput(startDate);
        leaseData.end_date = formatDateForInput(endDate);
        leaseData.lease_type = 'fixed';

        updateRentalSummary();
        validateStep1();
    }

    function handleMonthToMonthSelection() {
        const today = new Date();
        const startDate = new Date(today);
        startDate.setDate(today.getDate() + 7);

        $('#start_date').datepicker('setDate', startDate).prop('readonly', false);
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

    function validateStep1() {
        const assignBedLater = $('#assign_bed_later').is(':checked');

        const bedValid = assignBedLater || leaseData.bed_id;
        const roomValid = assignBedLater || leaseData.room_id;
        const unitValid = assignBedLater || leaseData.unit_id;

        const isValid = leaseData.property_id &&
            unitValid &&
            roomValid &&
            bedValid &&
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
                return;
            }
        }
        nextStep(step);
    }

    function handleLeaseTypeChange(type) {
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

    // ─────────────────────────────────────────────────────────────────
    // Rental summary
    // FIX #2: read dates from leaseData (always ISO), never from .val()
    // FIX #3: deposit is NOT added to the total amount
    // ─────────────────────────────────────────────────────────────────
    function updateRentalSummary() {
        // Always use leaseData for dates (guaranteed yyyy-mm-dd)
        const startDate = leaseData.start_date;
        const endDate = leaseData.end_date;
        const rentAmount = parseFloat($('#rent_amount').val()) || 0;
        const depositAmount = parseFloat($('#deposit_amount').val()) || 0;
        const isCustom = $('#payment_frequency').val() === 'CUSTOM';

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
            if (isCustom) {
                let customTotal = 0;
                let customCount = $('#customPaymentsList .custom-payment-entry').length;
                $('#customPaymentsList .custom-payment-entry').each(function() {
                    customTotal += parseFloat($(this).find('.custom-payment-amount').val()) || 0;
                });
                $('#summaryRent').text(
                    `$${customTotal.toFixed(2)} (${customCount} payment${customCount !== 1 ? 's' : ''})`);
            } else {
                $('#summaryRent').text('$' + rentAmount.toFixed(2) + '/month');
            }
            $('#summaryRentContainer').show();
            // Deposit shown separately, not added to rent
            $('#summaryDeposit').text('$' + depositAmount.toFixed(2));
            $('#summaryDepositContainer').show();
            $('#summaryInvoices').show();
        }

        // ─── FIX #3: total = rent only, deposit is separate ───
        if (isCustom) {
            let customTotal = 0;
            let customCount = 0;
            $('#customPaymentsList .custom-payment-entry').each(function() {
                customTotal += parseFloat($(this).find('.custom-payment-amount').val()) || 0;
                customCount++;
            });
            // Deposit NOT added
            $('.summary-amount h2').text('$' + customTotal.toFixed(2));
            if (customCount > 0) {
                $('#leaseDuration').text(`for ${customCount} custom payment${customCount !== 1 ? 's' : ''}`);
            } else {
                $('#leaseDuration').text('No custom payments added yet');
            }
        } else if (startDate && endDate && leaseData.lease_type === 'fixed') {
            const start = new Date(startDate + 'T00:00:00');
            const end = new Date(endDate + 'T00:00:00');
            const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
            const months = calculateCalendarMonths(start, end);

            $('#leaseDuration').text(`for ${months} month${months > 1 ? 's' : ''} (${days} days)`);

            // Deposit NOT added
            const totalRent = rentAmount * months;
            $('.summary-amount h2').text('$' + totalRent.toFixed(2));
        } else if (leaseData.lease_type === 'month_to_month') {
            // Deposit NOT added
            $('.summary-amount h2').text('$' + rentAmount.toFixed(2));
        }
    }

    function formatDate(dateString) {
        const date = new Date(dateString + 'T00:00:00');
        const options = {
            year: 'numeric',
            month: 'short',
            day: '2-digit'
        };
        return date.toLocaleDateString('en-US', options);
    }

    function calculateCalendarMonths(startDate, endDate) {
        let months = (endDate.getFullYear() - startDate.getFullYear()) * 12;
        months += endDate.getMonth() - startDate.getMonth();
        months += 1; // inclusive
        return Math.max(1, months);
    }

    // ─────────────────────────────────────────────────────────────────
    // Step navigation
    // ─────────────────────────────────────────────────────────────────
    function nextStep(step) {
        if (step) {
            currentStep = step;
        } else {
            if (currentStep < totalSteps) {
                currentStep++;
            }
        }

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

    // ─── FIX #2: use datepicker('setDate') so display stays mm/dd/yy ───
    function populateStep2Data() {
        if (leaseData.start_date) {
            $('#deposit_due_date').datepicker('setDate', new Date(leaseData.start_date + 'T00:00:00'));
        }

        if (leaseData.start_date) {
            const startDate = new Date(leaseData.start_date + 'T00:00:00');
            const dueDay = parseInt($('#due_day').val());
            let firstInvoiceDate = new Date(startDate);
            firstInvoiceDate.setDate(dueDay);

            if (firstInvoiceDate < startDate) {
                firstInvoiceDate.setMonth(firstInvoiceDate.getMonth() + 1);
            }

            $('#first_invoice_date').datepicker('setDate', firstInvoiceDate);
        }

        if (leaseData.bed_id) {
            const propertyName = $('#property_id option:selected').text();
            const bedName = $('#bed_id option:selected').text();
            $('#step2PropertyInfo').html(`<strong>${propertyName}</strong> → ${bedName}`);
        }
    }

    function populateStep3Data() {
        if (leaseData.bed_id) {
            const propertyName = $('#property_id option:selected').text();
            const bedName = $('#bed_id option:selected').text();
            $('#unitNameInfo').html(`<strong>${propertyName}</strong> → ${bedName}`);
        }

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

        loadActiveTenants();
        setupTenantManagement();
    }

    function populateStep4Data() {
        const propertyName = $('#property_id option:selected').text();
        const bedName = $('#bed_id option:selected').text();

        $('#finalPropertyName').text(propertyName || 'N/A');
        $('#finalUnitInfo').text(`${bedName}`);

        const leaseTermText = $('#lease_term_type option:selected').text();
        const leaseTypeText = leaseData.lease_type === 'month_to_month' ? 'Month-to-Month' : 'Fixed Term';
        $('#finalLeaseType').text(`${leaseTermText} (${leaseTypeText})`);

        const startDate = leaseData.start_date ? formatDate(leaseData.start_date) : 'N/A';
        const endDate = leaseData.lease_type === 'month_to_month' ?
            'No End Date' :
            (leaseData.end_date ? formatDate(leaseData.end_date) : 'N/A');
        $('#finalLeasePeriod').text(`${startDate} - ${endDate}`);

        const tenantListHtml = selectedTenants.length > 0 ?
            `<div class="d-flex align-items-center">
                    <div class="avatar avatar-md bg-primary-transparent text-primary rounded-circle me-2">
                        <i class="fe fe-user"></i>
                    </div>
                    <div>
                        <strong>${selectedTenants[0].first_name} ${selectedTenants[0].last_name}</strong>
                        <br><small class="text-muted">${selectedTenants[0].email}</small>
                    </div>
               </div>` :
            '<span class="text-muted">No tenant selected</span>';
        $('#finalTenantsList').html(tenantListHtml);

        const rentAmount = parseFloat($('#rent_amount').val()) || 0;
        const depositAmount = parseFloat($('#deposit_amount').val()) || 0;
        $('#finalRentAmount').text(`$${rentAmount.toFixed(2)}/season`);
        $('#finalDepositAmount').text(`$${depositAmount.toFixed(2)}`);

        const paymentFrequency = $('#payment_frequency option:selected').text() || 'Monthly';
        const dueDay = $('#due_day').val() || '1';

        // ─── FIX #2: use getISOFromPicker to get date, then format for display ───
        const firstInvoiceISO = getISOFromPicker('#first_invoice_date');
        const firstInvoice = firstInvoiceISO ? formatDate(firstInvoiceISO) : '-';

        const partialPayment = $('#partialPayment').is(':checked') ? 'Allowed' : 'Not Allowed';

        $('#finalPaymentFrequency').text(paymentFrequency);
        $('#finalDueDay').text(dueDay + getSuffix(parseInt(dueDay)) + ' of month');
        $('#finalFirstInvoice').text(firstInvoice);
        $('#finalPartialPayment').text(partialPayment);
    }

    function getSuffix(day) {
        if (day >= 11 && day <= 13) return 'th';
        switch (day % 10) {
            case 1:
                return 'st';
            case 2:
                return 'nd';
            case 3:
                return 'rd';
            default:
                return 'th';
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // Tenant management  (unchanged)
    // ─────────────────────────────────────────────────────────────────
    let selectedTenants = [];

    function setupTenantManagement() {
        $('#addExistingTenantBtn').off('click').on('click', function() {
            const tenantId = $('#existingTenantSelect').val();
            if (!tenantId) {
                Swal.fire({
                    title: "Tenant Not Selected",
                    text: "Please select a tenant.",
                    icon: "warning"
                });
                return;
            }

            const selectedOption = $('#existingTenantSelect option:selected');
            const tenantData = {
                id: tenantId,
                first_name: selectedOption.data('firstname'),
                last_name: selectedOption.data('lastname'),
                email: selectedOption.data('email'),
                phone: selectedOption.data('phone'),
                status: selectedOption.data('status')
            };

            setTenantForLease(tenantData);
        });

        $('#createNewTenantBtn').off('click').on('click', function() {
            const firstName = $('#newTenantFirstName').val().trim();
            const lastName = $('#newTenantLastName').val().trim();
            const email = $('#newTenantEmail').val().trim();
            const phone = $('#newTenantPhone').val().trim();

            if (!firstName || !lastName || !email || !phone) {
                Swal.fire({
                    title: "Incomplete Information",
                    text: "Please fill in all required fields.",
                    icon: "warning"
                });
                return;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                Swal.fire({
                    title: "Invalid Email",
                    text: "Please enter a valid email address.",
                    icon: "warning"
                });
                return;
            }

            const btn = $(this);
            btn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

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
                        setTenantForLease(response.data);
                        $('#newTenantFirstName, #newTenantLastName, #newTenantEmail, #newTenantPhone')
                            .val('');
                        loadActiveTenants();
                        Swal.fire({
                            title: "Tenant Created",
                            text: "The tenant has been successfully created and selected for this lease.",
                            icon: "success"
                        });
                    } else {
                        Swal.fire({
                            title: "Creation Failed",
                            text: "Failed to create tenant." + (response.message ? ' ' +
                                response.message : ''),
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
                    btn.prop('disabled', false).html(
                        '<i class="fe fe-save me-1"></i> Save & Add Tenant');
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
                            ${tenant.first_name} ${tenant.last_name} - ${tenant.email}
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

    function setTenantForLease(tenantData) {
        selectedTenants = [];
        $('#selectedTenantsList').empty();

        selectedTenants.push(tenantData);
        $('#tenantCount').text('1');
        $('#noTenantsAlert').hide();
        $('#selectedTenantsList').show();

        const card = `
            <div class="col-12 mb-3" data-tenant-id="${tenantData.id}">
                <div class="card tenant-card border-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-lg bg-success-transparent text-success rounded-circle me-3">
                                    <i class="fe fe-user fs-4"></i>
                                </div>
                               <div>
                                <h5 class="mb-1">${tenantData.first_name} ${tenantData.last_name}</h5>
                                <p class="mb-0 text-muted d-flex flex-wrap align-items-center gap-2">
                                    <span class="d-inline-flex align-items-center">
                                        <i class="fe fe-mail me-1"></i> ${tenantData.email}
                                    </span>
                                    <span class="text-muted">|</span>
                                    <span class="d-inline-flex align-items-center">
                                        <i class="fe fe-phone me-1"></i> ${tenantData.phone || 'N/A'}
                                    </span>
                                </p>
                            </div>
                            </div>
                            <button type="button"
                                class="btn btn-outline-danger btn-sm d-inline-flex align-items-center"
                                onclick="clearTenantSelection()">
                                <i class="fe fe-x me-1"></i>
                                Change Tenant
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('#selectedTenantsList').append(card);
    }

    function clearTenantSelection() {
        selectedTenants = [];
        $('#selectedTenantsList').empty().hide();
        $('#noTenantsAlert').show();
        $('#tenantCount').text('0');
        $('#existingTenantSelect').val('').trigger('change');
    }

    function addTenantToList(tenantData) {
        setTenantForLease(tenantData);
    }

    function removeTenantFromList(tenantId) {
        clearTenantSelection();
    }

    // ─────────────────────────────────────────────────────────────────
    // Step display helpers  (unchanged)
    // ─────────────────────────────────────────────────────────────────
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
        $('.step-content').removeClass('active');
        $('.step-item').removeClass('active completed');

        $(`#step-${step}`).addClass('active');

        for (let i = 1; i <= totalSteps; i++) {
            const $step = $(`.step-item[data-step="${i}"]`);
            if (i < step) {
                $step.addClass('completed');
            } else if (i === step) {
                $step.addClass('active');
            }
        }

        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    function updateNavigationButtons() {
        if (currentStep === 1) {
            $('#backBtn').hide();
        } else {
            $('#backBtn').show();
        }

        $('#nextBtn').hide();
        $('#saveBtn').hide();
        $('#addTenantBtn').hide();

        if (currentStep === 3) {
            $('#nextBtn').show();
        } else if (currentStep === 4) {
            $('#saveBtn').show();
        } else if (currentStep < totalSteps) {
            $('#nextBtn').show();
        }
    }

    function addTenant() {
        const tenantCount = $('#tenantsTableBody .tenant-row').length + 1;
        const newRow = `
            <tr class="tenant-row">
                <td><div class="tenant-number">${tenantCount}</div></td>
                <td><input type="text" class="form-control" placeholder="First Name"></td>
                <td><input type="text" class="form-control" placeholder="Last Name"></td>
                <td><input type="email" class="form-control" placeholder="Email"></td>
                <td><input type="tel" class="form-control" placeholder="Phone Number"></td>
                <td>
                    <select class="form-select">
                        <option>Select Package</option>
                    </select>
                </td>
                <td><span class="badge bg-secondary">Not applied yet</span></td>
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
        $('#tenantsTableBody .tenant-row').each(function(index) {
            $(this).find('.tenant-number').text(index + 1);
        });
    }

    // ─────────────────────────────────────────────────────────────────
    // Lease submission
    // ─────────────────────────────────────────────────────────────────
    function collectLeaseData() {
        const paymentFrequency = $('#payment_frequency').val() || 'MONTHLY';
        const isCustomPayment = paymentFrequency === 'CUSTOM';
        const assignBedLater = $('#assign_bed_later').is(':checked');

        return {
            property_id: leaseData.property_id,
            bed_id: assignBedLater ? null : leaseData.bed_id,
            assign_bed_later: assignBedLater,
            season_id: leaseData.lease_term_id,
            actual_move_in: leaseData.actual_move_in || leaseData.start_date,
            lease_type: leaseData.lease_type,
            start_date: leaseData.start_date,
            end_date: leaseData.lease_type === 'month_to_month' ? null : leaseData.end_date,
            rent_amount: parseFloat($('#rent_amount').val()) || 0,
            deposit_amount: parseFloat($('#deposit_amount').val()) || 0,
            payment_frequency: paymentFrequency,
            due_day: isCustomPayment ? null : (parseInt($('#due_day').val()) || 1),
            // ─── FIX #2: use getISOFromPicker so backend always gets yyyy-mm-dd ───
            first_invoice_date: isCustomPayment ? null : (getISOFromPicker('#first_invoice_date') || null),
            deposit_collected: $('#deposit_collected').is(':checked'),
            custom_payments: isCustomPayment ? getCustomPayments() : [],
            tenant_ids: selectedTenants.map(t => t.id),
            lease_template_id: $('#lease_template_id').val() || null,
            send_for_signature: $('#sendForSignature').is(':checked'),
            send_welcome_email: $('#sendWelcomeEmail').is(':checked'),
            allow_partial_payment: $('#partialPayment').is(':checked'),
            notes: $('#notes').val() || null,
            _token: $('meta[name="csrf-token"]').attr('content')
        };
    }

    function validateLeaseData(data) {
        const errors = [];

        if (!data.property_id) errors.push('Please select a property');
        if (!data.assign_bed_later && !data.bed_id) errors.push('Please select a bed or check "Assign bed later"');
        if (!data.season_id) errors.push('Please select a lease term');
        if (!data.start_date) errors.push('Please select a start date');
        if (data.lease_type === 'fixed' && !data.end_date) errors.push(
            'Please select an end date for fixed term lease');
        if (!data.rent_amount || data.rent_amount <= 0) errors.push('Please enter a valid rent amount');
        if (data.tenant_ids.length === 0) errors.push('Please select a tenant for this lease');
        if (data.payment_frequency === 'CUSTOM' && (!data.custom_payments || data.custom_payments.length === 0)) {
            errors.push('Please add at least one custom payment date');
        }

        return errors;
    }

    function submitLease(saveAsDraft = false) {
        const data = collectLeaseData();
        data.save_as_draft = saveAsDraft;

        const errors = validateLeaseData(data);
        if (errors.length > 0 && !saveAsDraft) {
            Swal.fire({
                title: "Validation Error",
                text: 'Please fix the following errors:\n\n' + errors.join('\n, '),
                icon: "error"
            });
            return;
        }

        const confirmMsg = saveAsDraft ?
            'You want to save this lease as a draft?' :
            'You want to create this lease and send it for signing?';

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
                proceedWithSubmission(data, saveAsDraft);
            }
        });
    }

    function proceedWithSubmission(data, saveAsDraft) {
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
                        text: (response.message || 'Failed to create lease') + '\n\n' + (response
                            .errors || []).join('\n'),
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

    // ─────────────────────────────────────────────────────────────────
    // On ready: bind submit buttons + pre-selected tenant
    // ─────────────────────────────────────────────────────────────────
    $(document).ready(function() {
        $('#createLeaseBtn').on('click', function() {
            submitLease(false);
        });

        $('#saveDraftBtn').on('click', function() {
            submitLease(true);
        });

        @if (isset($selectedTenant) && $selectedTenant)
            $('.add-tenant-section').addClass('d-none');
            let moveInDate = '{{ $selectedTenant->arrival_date }}';
            $('#actual_move_in').val('{{ $selectedTenant->arrival_date }}');
            const preSelectedTenant = {
                id: {{ $selectedTenant->id }},
                first_name: @json($selectedTenant->profile->first_name ?? ''),
                last_name: @json($selectedTenant->profile->last_name ?? ''),
                email: @json($selectedTenant->email ?? ''),
                phone: @json($selectedTenant->profile->phone ?? ''),
                status: 'approved'
            };
            setTenantForLease(preSelectedTenant);
        @endif
    });
</script>
