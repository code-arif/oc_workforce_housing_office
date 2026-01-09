$(document).ready(function() {
    let canvas = null;
    let ctx = null;
    let isDrawing = false;
    let currentSignatureType = null;
    let documentId = window.documentId; // Get from window object

    // Sign Admin Button
    $('#signAdminBtn').on('click', function() {
        currentSignatureType = 'admin';
        $('#signatureModalLabel').text('Admin/Landlord Signature');
        $('#signatureModal').modal('show');
        initializeCanvas();
    });

    // Sign Tenant Button
    $('#signTenantBtn').on('click', function() {
        currentSignatureType = 'tenant';
        $('#signatureModalLabel').text('Tenant Signature');
        $('#signatureModal').modal('show');
        initializeCanvas();
    });

    // Initialize Canvas when modal is shown
    $('#signatureModal').on('shown.bs.modal', function() {
        initializeCanvas();
    });

    function initializeCanvas() {
        canvas = document.getElementById('signatureCanvas');
        if (!canvas) return;

        // Set canvas size
        canvas.width = canvas.offsetWidth;
        canvas.height = 200;

        ctx = canvas.getContext('2d');
        ctx.strokeStyle = '#000';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        // Clear canvas
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // Mouse events
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);

        // Touch events for mobile
        canvas.addEventListener('touchstart', handleTouchStart);
        canvas.addEventListener('touchmove', handleTouchMove);
        canvas.addEventListener('touchend', stopDrawing);
    }

    function startDrawing(e) {
        isDrawing = true;
        const rect = canvas.getBoundingClientRect();
        ctx.beginPath();
        ctx.moveTo(e.clientX - rect.left, e.clientY - rect.top);
    }

    function draw(e) {
        if (!isDrawing) return;
        
        const rect = canvas.getBoundingClientRect();
        ctx.lineTo(e.clientX - rect.left, e.clientY - rect.top);
        ctx.stroke();
    }

    function stopDrawing() {
        isDrawing = false;
    }

    function handleTouchStart(e) {
        e.preventDefault();
        const touch = e.touches[0];
        const mouseEvent = new MouseEvent('mousedown', {
            clientX: touch.clientX,
            clientY: touch.clientY
        });
        canvas.dispatchEvent(mouseEvent);
    }

    function handleTouchMove(e) {
        e.preventDefault();
        const touch = e.touches[0];
        const mouseEvent = new MouseEvent('mousemove', {
            clientX: touch.clientX,
            clientY: touch.clientY
        });
        canvas.dispatchEvent(mouseEvent);
    }

    // Clear Signature
    $('#clearSignatureBtn').on('click', function() {
        if (ctx && canvas) {
            ctx.fillStyle = '#fff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
        }
    });

    // Save Signature
    $('#saveSignatureBtn').on('click', function() {
        if (!canvas) {
            alert('Canvas not initialized');
            return;
        }

        // Check if canvas is empty
        if (isCanvasEmpty()) {
            alert('Please provide a signature');
            return;
        }

        // Convert canvas to base64
        const signatureData = canvas.toDataURL('image/png');

        // Determine the endpoint
        const endpoint = currentSignatureType === 'admin' 
            ? `/lease-documents/0/${documentId}/sign-admin`
            : `/lease-documents/0/${documentId}/sign-tenant`;

        // Save signature
        $.ajax({
            url: endpoint,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                signature: signatureData
            },
            beforeSend: function() {
                $('#saveSignatureBtn').prop('disabled', true).text('Saving...');
            },
            success: function(response) {
                if (response.success) {
                    $('#signatureModal').modal('hide');
                    
                    // Show success notification
                    showNotification('success', response.message);
                    
                    // Reload page after 1 second
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                }
            },
            error: function(xhr) {
                alert('Error saving signature: ' + (xhr.responseJSON?.message || 'Unknown error'));
                $('#saveSignatureBtn').prop('disabled', false).text('Save Signature');
            }
        });
    });

    function isCanvasEmpty() {
        const blank = document.createElement('canvas');
        blank.width = canvas.width;
        blank.height = canvas.height;
        const blankCtx = blank.getContext('2d');
        blankCtx.fillStyle = '#fff';
        blankCtx.fillRect(0, 0, blank.width, blank.height);
        
        return canvas.toDataURL() === blank.toDataURL();
    }

    function showNotification(type, message) {
        const bgColor = type === 'success' ? '#28a745' : '#dc3545';
        const notification = $(`
            <div style="position: fixed; top: 20px; right: 20px; background: ${bgColor}; color: white; padding: 15px 25px; border-radius: 4px; z-index: 9999; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <i class="fas fa-check-circle"></i> ${message}
            </div>
        `);
        
        $('body').append(notification);
        
        setTimeout(function() {
            notification.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }

    // Clean up canvas when modal is hidden
    $('#signatureModal').on('hidden.bs.modal', function() {
        if (canvas) {
            canvas.removeEventListener('mousedown', startDrawing);
            canvas.removeEventListener('mousemove', draw);
            canvas.removeEventListener('mouseup', stopDrawing);
            canvas.removeEventListener('mouseout', stopDrawing);
            canvas.removeEventListener('touchstart', handleTouchStart);
            canvas.removeEventListener('touchmove', handleTouchMove);
            canvas.removeEventListener('touchend', stopDrawing);
        }
        canvas = null;
        ctx = null;
        currentSignatureType = null;
        $('#saveSignatureBtn').prop('disabled', false).text('Save Signature');
    });
});