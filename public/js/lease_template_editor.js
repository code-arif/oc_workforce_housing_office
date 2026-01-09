$(document).ready(function() {
    let placeholders = [];
    let signatures = [];
    const editor = $('#templateEditor');
    let dropIndicator = null;
    let dragSourceType = null; // 'palette' or 'document'
    let draggedPaletteItem = null;
    let draggedDocumentElement = null;

    // Create drop indicator
    createDropIndicator();
    
    // Load existing placeholders and signatures
    loadExistingElements();

    // Initialize drag and drop
    initPaletteDrag();
    initDocumentDrag();
    initEditorDropZone();

    // Save Template
    $('#saveTemplateBtn').on('click', function() {
        saveTemplate();
    });

    // Preview Template
    $('#previewTemplateBtn').on('click', function() {
        previewTemplate();
    });

    // Handle placeholder removal on click (in document)
    $(document).on('click', '#templateEditor .placeholder, #templateEditor .signature-placeholder', function(e) {
        // Ignore if we just finished dragging
        if ($(this).data('just-dragged')) {
            $(this).data('just-dragged', false);
            return;
        }
        e.preventDefault();
        e.stopPropagation();
        
        if (confirm('Remove this placeholder?')) {
            $(this).remove();
            collectElementsFromContent();
            showNotification('info', 'Placeholder removed');
        }
    });

    // Create drop indicator element
    function createDropIndicator() {
        dropIndicator = document.createElement('span');
        dropIndicator.className = 'drop-indicator';
        dropIndicator.innerHTML = '|';
    }

    // Initialize dragging from palette
    function initPaletteDrag() {
        const paletteItems = document.querySelectorAll('.palette-item');
        
        paletteItems.forEach(item => {
            item.addEventListener('dragstart', handlePaletteDragStart);
            item.addEventListener('dragend', handlePaletteDragEnd);
        });
    }

    function handlePaletteDragStart(e) {
        dragSourceType = 'palette';
        draggedPaletteItem = this;
        
        this.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'copy';
        e.dataTransfer.setData('text/plain', this.dataset.field);
        e.dataTransfer.setData('placeholder-type', this.dataset.type);
        
        // Add visual feedback to editor
        editor.addClass('drop-active');
    }

    function handlePaletteDragEnd(e) {
        this.classList.remove('dragging');
        dragSourceType = null;
        draggedPaletteItem = null;
        editor.removeClass('drop-active');
        removeDropIndicator();
    }

    // Initialize dragging within document (for repositioning)
    function initDocumentDrag() {
        const docPlaceholders = editor[0].querySelectorAll('.placeholder, .signature-placeholder');
        
        docPlaceholders.forEach(placeholder => {
            if (placeholder.dataset.dragInitialized) return;
            placeholder.dataset.dragInitialized = 'true';
            placeholder.setAttribute('draggable', 'true');
            
            placeholder.addEventListener('dragstart', handleDocumentDragStart);
            placeholder.addEventListener('dragend', handleDocumentDragEnd);
        });
    }

    function handleDocumentDragStart(e) {
        dragSourceType = 'document';
        draggedDocumentElement = this;
        
        this.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.outerHTML);
        
        setTimeout(() => {
            this.style.opacity = '0.4';
        }, 0);
    }

    function handleDocumentDragEnd(e) {
        this.classList.remove('dragging');
        this.style.opacity = '1';
        
        // Mark as just dragged to prevent click handler
        $(this).data('just-dragged', true);
        
        dragSourceType = null;
        draggedDocumentElement = null;
        removeDropIndicator();
    }

    // Initialize editor as drop zone
    function initEditorDropZone() {
        const editorEl = editor[0];
        
        editorEl.addEventListener('dragover', handleEditorDragOver);
        editorEl.addEventListener('drop', handleEditorDrop);
        editorEl.addEventListener('dragleave', handleEditorDragLeave);
    }

    function handleEditorDragOver(e) {
        e.preventDefault();
        
        if (dragSourceType === 'palette') {
            e.dataTransfer.dropEffect = 'copy';
        } else if (dragSourceType === 'document') {
            e.dataTransfer.dropEffect = 'move';
        }

        // Show drop indicator at cursor position
        const range = getCaretRangeFromPoint(e.clientX, e.clientY);
        
        if (range && editor[0].contains(range.startContainer)) {
            // Don't insert into the drop indicator itself
            if (range.startContainer === dropIndicator || 
                (range.startContainer.parentNode && range.startContainer.parentNode === dropIndicator)) {
                return;
            }
            
            removeDropIndicator();
            
            try {
                range.insertNode(dropIndicator);
            } catch (ex) {
                // Ignore errors
            }
        }
    }

    function handleEditorDrop(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const range = getCaretRangeFromPoint(e.clientX, e.clientY);
        
        if (!range || !editor[0].contains(range.startContainer)) {
            removeDropIndicator();
            return;
        }

        removeDropIndicator();

        if (dragSourceType === 'palette') {
            // Create new placeholder from palette
            const field = e.dataTransfer.getData('text/plain');
            const type = e.dataTransfer.getData('placeholder-type');
            
            if (field) {
                const newElement = createPlaceholderElement(field, type);
                range.insertNode(newElement);
                
                // Add space after
                const space = document.createTextNode('\u00A0');
                if (newElement.nextSibling) {
                    newElement.parentNode.insertBefore(space, newElement.nextSibling);
                } else {
                    newElement.parentNode.appendChild(space);
                }
                
                // Re-initialize drag for new element
                initDocumentDrag();
                collectElementsFromContent();
                
                showNotification('success', `Added: ${field.replace(/_/g, ' ')}`);
            }
        } else if (dragSourceType === 'document' && draggedDocumentElement) {
            // Move existing placeholder
            const clone = draggedDocumentElement.cloneNode(true);
            clone.style.opacity = '1';
            clone.classList.remove('dragging');
            clone.dataset.dragInitialized = '';
            
            // Remove original
            if (draggedDocumentElement.parentNode) {
                draggedDocumentElement.parentNode.removeChild(draggedDocumentElement);
            }
            
            // Insert at new position
            range.insertNode(clone);
            
            // Add space after
            const space = document.createTextNode('\u00A0');
            if (clone.nextSibling) {
                clone.parentNode.insertBefore(space, clone.nextSibling);
            } else {
                clone.parentNode.appendChild(space);
            }
            
            // Re-initialize drag
            initDocumentDrag();
            
            showNotification('info', 'Placeholder moved');
        }
        
        dragSourceType = null;
        draggedPaletteItem = null;
        draggedDocumentElement = null;
        editor.removeClass('drop-active');
    }

    function handleEditorDragLeave(e) {
        if (!editor[0].contains(e.relatedTarget)) {
            removeDropIndicator();
            editor.removeClass('drop-active');
        }
    }

    function removeDropIndicator() {
        if (dropIndicator && dropIndicator.parentNode) {
            dropIndicator.parentNode.removeChild(dropIndicator);
        }
    }

    function createPlaceholderElement(field, type) {
        const id = (type === 'signature' ? 'sig_' : 'ph_') + Date.now();
        const span = document.createElement('span');
        
        if (type === 'signature') {
            const signType = field === 'admin_signature' ? 'admin' : 'tenant';
            const cssClass = signType === 'admin' ? 'admin-signature' : 'tenant-signature';
            span.className = `signature-placeholder ${cssClass}`;
            span.dataset.id = id;
            span.dataset.type = signType;
            span.setAttribute('contenteditable', 'false');
            span.setAttribute('draggable', 'true');
            span.setAttribute('title', 'Drag to reposition, Click to remove');
            span.textContent = `{{${field}}}`;
        } else {
            span.className = 'placeholder';
            span.dataset.id = id;
            span.dataset.field = field;
            span.setAttribute('contenteditable', 'false');
            span.setAttribute('draggable', 'true');
            span.setAttribute('title', 'Drag to reposition, Click to remove');
            span.textContent = `{{${field}}}`;
        }
        
        return span;
    }

    // Cross-browser compatible caretRangeFromPoint
    function getCaretRangeFromPoint(x, y) {
        let range;
        
        if (document.caretRangeFromPoint) {
            range = document.caretRangeFromPoint(x, y);
        } else if (document.caretPositionFromPoint) {
            const pos = document.caretPositionFromPoint(x, y);
            if (pos) {
                range = document.createRange();
                range.setStart(pos.offsetNode, pos.offset);
                range.collapse(true);
            }
        }
        
        return range;
    }

    function loadExistingElements() {
        const templateData = window.templateData || {};
        
        if (templateData.placeholders && Array.isArray(templateData.placeholders)) {
            placeholders = templateData.placeholders;
        }
        
        if (templateData.signatures && Array.isArray(templateData.signatures)) {
            signatures = templateData.signatures;
        }
        
        // Add draggable attribute to existing placeholders
        editor.find('.placeholder, .signature-placeholder').each(function() {
            $(this).attr('draggable', 'true')
                   .attr('title', 'Drag to reposition, Click to remove')
                   .attr('contenteditable', 'false');
        });
    }

    function collectElementsFromContent() {
        placeholders = [];
        signatures = [];
        
        editor.find('.placeholder').each(function() {
            const id = $(this).data('id') || 'ph_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            const field = $(this).data('field');
            if (field) {
                $(this).attr('data-id', id);
                placeholders.push({ id, field });
            }
        });
        
        editor.find('.signature-placeholder').each(function() {
            const id = $(this).data('id') || 'sig_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            const type = $(this).data('type');
            if (type) {
                $(this).attr('data-id', id);
                signatures.push({ id, type });
            }
        });
    }

    function saveTemplate() {
        const content = editor.html();
        const templateId = window.templateData.id;
        const templateName = window.templateData.name;

        collectElementsFromContent();

        const saveBtn = $('#saveTemplateBtn');
        saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: `/admin/lease-templates/0/${templateId}`,
            method: 'PUT',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                name: templateName,
                content: content,
                placeholders: JSON.stringify(placeholders),
                signatures: JSON.stringify(signatures)
            },
            success: function(response) {
                if (response.success) {
                    showNotification('success', 'Template saved successfully!');
                }
                saveBtn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Template');
            },
            error: function(xhr) {
                showNotification('error', 'Error saving template: ' + (xhr.responseJSON?.message || 'Unknown error'));
                saveBtn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Template');
            }
        });
    }

    function previewTemplate() {
        const content = editor.html();
        
        const sampleData = {
            'tenant_name': 'John Doe',
            'tenant_email': 'john.doe@email.com',
            'tenant_phone': '(555) 123-4567',
            'property_address': '123 Main Street, Apt 4B, New York, NY 10001',
            'lease_start_date': 'January 1, 2026',
            'lease_end_date': 'December 31, 2026',
            'monthly_rent': '$1,500.00',
            'security_deposit': '$3,000.00',
            'lease_term': '12 Months',
            'admin_name': 'Robert Smith',
            'admin_email': 'robert.smith@propertymanagement.com',
            'current_date': new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
        };

        let previewContent = content;

        // Replace placeholders with sample values
        for (const [key, value] of Object.entries(sampleData)) {
            previewContent = previewContent.replace(new RegExp(`\\{\\{${key}\\}\\}`, 'g'), `<strong>${value}</strong>`);
            const pattern = new RegExp(`<span[^>]*data-field="${key}"[^>]*>.*?<\\/span>`, 'g');
            previewContent = previewContent.replace(pattern, `<strong>${value}</strong>`);
        }

        // Replace signature placeholders
        previewContent = previewContent.replace(
            /<span[^>]*class="signature-placeholder admin-signature"[^>]*>.*?<\/span>/g,
            `<div class="signature-block">
                <div class="signature-image">
                    <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjUwIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxwYXRoIGQ9Ik0xMCA0MEMzMCAyMCA1MCAzNSA3MCAyNVM5MCAzNSAxMTAgMjVTMTMwIDM1IDE1MCAzMFMxNzAgMjAgMTkwIDMwIiBzdHJva2U9IiMzMzMiIHN0cm9rZS13aWR0aD0iMiIgZmlsbD0ibm9uZSIvPjwvc3ZnPg==" alt="Admin Signature" style="height: 40px;">
                </div>
                <div class="signature-line"></div>
                <div class="signature-info">
                    <span class="signer-name">Robert Smith</span>
                    <span class="sign-date">${new Date().toLocaleDateString('en-US')}</span>
                </div>
                <div class="signature-title">Landlord/Property Manager</div>
            </div>`
        );
        previewContent = previewContent.replace(
            /<span[^>]*class="signature-placeholder tenant-signature"[^>]*>.*?<\/span>/g,
            `<div class="signature-block">
                <div class="signature-image">
                    <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjUwIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxwYXRoIGQ9Ik0xMCAzNUMyMCA0NSA0MCAyMCA2MCAzNVM4MCAyMCAxMDAgMzVTMTIwIDIwIDE0MCAzMFMxNjAgNDUgMTgwIDMwIiBzdHJva2U9IiMzMzMiIHN0cm9rZS13aWR0aD0iMiIgZmlsbD0ibm9uZSIvPjwvc3ZnPg==" alt="Tenant Signature" style="height: 40px;">
                </div>
                <div class="signature-line"></div>
                <div class="signature-info">
                    <span class="signer-name">John Doe</span>
                    <span class="sign-date">${new Date().toLocaleDateString('en-US')}</span>
                </div>
                <div class="signature-title">Tenant</div>
            </div>`
        );

        $('#previewContent').html(`
            <div class="preview-controls mb-3 no-print">
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Document
                </button>
                <span class="ms-3 text-white-50"><i class="fas fa-info-circle"></i> Preview with sample data</span>
            </div>
            <div class="print-document" id="printableArea">
                <style>
                    .print-document {
                        background: white;
                        padding: 60px 80px;
                        max-width: 8.5in;
                        min-height: 11in;
                        margin: 0 auto;
                        box-shadow: 0 0 30px rgba(0,0,0,0.2);
                        font-family: 'Times New Roman', Times, serif;
                        font-size: 12pt;
                        line-height: 1.8;
                        color: #000;
                    }
                    .signature-block {
                        display: inline-block;
                        min-width: 250px;
                        margin: 20px 0;
                    }
                    .signature-line {
                        border-bottom: 1px solid #000;
                        margin-bottom: 5px;
                    }
                    .signature-info {
                        display: flex;
                        justify-content: space-between;
                        font-size: 10pt;
                    }
                    .signature-title {
                        font-size: 9pt;
                        color: #666;
                        font-style: italic;
                    }
                </style>
                ${previewContent}
            </div>
        `);
        $('#previewModal').modal('show');
    }

    function showNotification(type, message) {
        const bgColors = {
            'success': '#28a745',
            'error': '#dc3545',
            'info': '#17a2b8',
            'warning': '#ffc107'
        };
        const textColor = type === 'warning' ? '#212529' : '#fff';
        const icons = {
            'success': 'check-circle',
            'error': 'exclamation-circle',
            'info': 'info-circle',
            'warning': 'exclamation-triangle'
        };
        
        const notification = $(`
            <div class="editor-notification" style="position: fixed; top: 20px; right: 20px; background: ${bgColors[type]}; color: ${textColor}; padding: 15px 25px; border-radius: 4px; z-index: 9999; box-shadow: 0 4px 6px rgba(0,0,0,0.2); display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-${icons[type]}"></i>
                <span>${message}</span>
            </div>
        `);
        
        $('.editor-notification').remove();
        $('body').append(notification);
        
        setTimeout(function() {
            notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }
});
