/**
 * Document Generation Manager
 * Handles document generation, listing, and viewing
 */

class DocumentGenerationManager {
    constructor() {
        this.leaseId = null;
        this.currentTemplate = null;
        this.templates = [];
        this.documents = [];
        this.currentDocument = null;
        this.docApiUrl = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
    }

    /**
     * Initialize the manager with lease ID
     */
    setLeaseId(leaseId) {
        this.leaseId = leaseId;
        this.docApiUrl = `/admin/lease-documents/leases/${leaseId}`;
        console.log('DocumentGenerationManager initialized for lease:', leaseId);
    }

    /**
     * Setup all event listeners
     */
    setupEventListeners() {
        // Generate Document Modal
        const generateModal = document.getElementById('generateDocumentModal');
        if (generateModal) {
            generateModal.addEventListener('show.bs.modal', () => this.onGenerateModalShow());
        }

        // Template selection change
        document.addEventListener('change', (e) => {
            if (e.target.id === 'generateTemplateSelect') {
                this.onTemplateSelected(e.target.value);
            }
        });

        // Generate document button
        document.addEventListener('click', (e) => {
            if (e.target.id === 'generateDocumentBtn') {
                e.preventDefault();
                this.generateDocument();
            }
        });

        // Documents List Modal
        const listModal = document.getElementById('documentsListModal');
        if (listModal) {
            listModal.addEventListener('show.bs.modal', () => this.loadDocumentsList());
        }

        // Refresh documents button
        document.addEventListener('click', (e) => {
            if (e.target.id === 'refreshDocumentsBtn') {
                this.loadDocumentsList();
            }
        });

        // Document viewer modal
        const viewerModal = document.getElementById('documentViewerModal');
        if (viewerModal) {
            viewerModal.addEventListener('hidden.bs.modal', () => this.cleanupViewer());
        }

        // Download document button
        document.addEventListener('click', (e) => {
            if (e.target.id === 'downloadDocumentBtn') {
                this.downloadDocument();
            }
        });
    }

    /**
     * Load available templates when generate modal opens
     */
    async onGenerateModalShow() {
        if (!this.leaseId) {
            this.showGenerateAlert('error', 'Lease ID not set. Please refresh the page.');
            return;
        }

        await this.loadAvailableTemplates();
    }

    /**
     * Load available lease templates
     */
    async loadAvailableTemplates() {
        try {
            const response = await $.ajax({
                url: '/admin/lease-templates',
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            this.templates = response.data || response;
            this.populateTemplateSelect();
        } catch (error) {
            console.error('Error loading templates:', error);
            this.showGenerateAlert('error', 'Failed to load templates. Please try again.');
        }
    }

    /**
     * Populate template select dropdown
     */
    populateTemplateSelect() {
        const select = document.getElementById('generateTemplateSelect');
        if (!select) return;

        // Keep the default option
        const defaultOption = select.querySelector('option:first-child');
        select.innerHTML = '';
        if (defaultOption) {
            select.appendChild(defaultOption.cloneNode(true));
        }

        // Add templates
        this.templates.forEach(template => {
            const option = document.createElement('option');
            option.value = template.id;
            option.textContent = template.name;
            option.dataset.templateId = template.id;
            select.appendChild(option);
        });
    }

    /**
     * Handle template selection
     */
    async onTemplateSelected(templateId) {
        if (!templateId) {
            document.getElementById('readinessChecklist').innerHTML = `
                <div class="text-center py-3">
                    <small class="text-muted">Select a template above to check data readiness</small>
                </div>
            `;
            return;
        }

        document.getElementById('generateTemplateId').value = templateId;
        await this.checkDataReadiness(templateId);
    }

    /**
     * Check if lease data is ready for document generation
     */
    async checkDataReadiness(templateId) {
        try {
            const checklistDiv = document.getElementById('readinessChecklist');
            checklistDiv.innerHTML = `
                <div class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Checking...</span>
                    </div>
                    <small class="text-muted d-block mt-2">Checking data readiness...</small>
                </div>
            `;

            const response = await $.ajax({
                url: `${this.docApiUrl}/templates/${templateId}/check-readiness`,
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            this.renderReadinessChecklist(response.data || response);
        } catch (error) {
            console.error('Error checking readiness:', error);
            document.getElementById('readinessChecklist').innerHTML = `
                <div class="alert alert-danger mb-0">
                    <i class="ri-alert-line me-2"></i>Error checking data readiness
                </div>
            `;
        }
    }

    /**
     * Render the readiness checklist
     */
    renderReadinessChecklist(data) {
        const checklistDiv = document.getElementById('readinessChecklist');
        let html = '';

        const checks = [
            {
                label: 'Tenant Data',
                icon: 'ri-user-line',
                ready: data.has_tenant,
                details: data.tenant_data ? `${data.tenant_data.full_name}` : 'Missing tenant information'
            },
            {
                label: 'Property Data',
                icon: 'ri-home-line',
                ready: data.has_property,
                details: data.property_data ? `${data.property_data.name}` : 'Missing property information'
            },
            {
                label: 'Lease Data',
                icon: 'ri-file-contract-line',
                ready: data.has_lease,
                details: data.lease_data ? `From ${data.lease_data.start_date} to ${data.lease_data.end_date}` : 'Missing lease information'
            }
        ];

        checks.forEach(check => {
            const statusClass = check.ready ? 'success' : 'warning';
            const statusIcon = check.ready ? 'ri-check-line' : 'ri-alert-line';
            const statusText = check.ready ? 'Ready' : 'Missing';

            html += `
                <div class="d-flex align-items-center mb-2 p-2 border rounded" style="background-color: rgba(0,0,0,0.02);">
                    <i class="ri-${check.icon} me-3 text-muted"></i>
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-dark">${check.label}</div>
                        <small class="text-muted">${check.details}</small>
                    </div>
                    <span class="badge bg-${statusClass}">
                        <i class="${statusIcon} me-1"></i>${statusText}
                    </span>
                </div>
            `;
        });

        // Overall status
        const allReady = data.has_tenant && data.has_property && data.has_lease;
        const statusMessage = allReady 
            ? '<i class="ri-check-circle-line text-success me-2"></i><strong>All data ready!</strong> You can generate the document.'
            : '<i class="ri-alert-circle-line text-warning me-2"></i><strong>Some data missing.</strong> Generate anyway to see placeholders.';

        html = `
            <div class="alert ${allReady ? 'alert-success' : 'alert-warning'} mb-3">
                ${statusMessage}
            </div>
        ` + html;

        checklistDiv.innerHTML = html;
    }

    /**
     * Generate document
     */
    async generateDocument() {
        const form = document.getElementById('generateDocumentForm');
        const templateId = document.getElementById('generateTemplateId').value;
        const generatePdf = document.getElementById('generatePdfCheckbox').checked;
        const forceRegenerate = document.getElementById('forceRegenerateCheckbox').checked;

        if (!templateId) {
            this.showGenerateAlert('error', 'Please select a template');
            return;
        }

        // Show loading state
        const btn = document.getElementById('generateDocumentBtn');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            Generating...
        `;

        try {
            const response = await $.ajax({
                url: `${this.docApiUrl}/generate`,
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                data: JSON.stringify({
                    template_id: templateId,
                    generate_pdf: generatePdf,
                    force_regenerate: forceRegenerate,
                    _token: document.querySelector('meta[name="csrf-token"]')?.content || form.querySelector('[name="_token"]')?.value
                })
            });

            this.showGenerateAlert('success', 'Document generated successfully!');
            
            // Show preview or success message
            if (response.data && response.data.generated_document_content) {
                document.getElementById('documentPreviewSection').classList.remove('d-none');
                document.getElementById('documentPreviewContent').innerHTML = response.data.generated_document_content;
            }

            // Reset form and close after a delay
            setTimeout(() => {
                form.reset();
                document.getElementById('documentPreviewSection').classList.add('d-none');
                const modal = bootstrap.Modal.getInstance(document.getElementById('generateDocumentModal'));
                modal.hide();
            }, 2000);

        } catch (error) {
            console.error('Error generating document:', error);
            const errorMessage = error.responseJSON?.message || 'Failed to generate document. Please try again.';
            this.showGenerateAlert('error', errorMessage);
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }

    /**
     * Load list of generated documents
     */
    async loadDocumentsList() {
        const loadingDiv = document.getElementById('documentsListLoading');
        const emptyDiv = document.getElementById('documentsListEmpty');
        const tableDiv = document.getElementById('documentsListTable');

        loadingDiv.classList.remove('d-none');
        emptyDiv.classList.add('d-none');
        tableDiv.classList.add('d-none');

        try {
            const response = await $.ajax({
                url: `${this.docApiUrl}`,
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            this.documents = Array.isArray(response) ? response : (response.data || []);
            this.renderDocumentsList();

        } catch (error) {
            console.error('Error loading documents:', error);
            this.showDocumentsAlert('error', 'Failed to load documents. Please try again.');
            emptyDiv.classList.remove('d-none');
        } finally {
            loadingDiv.classList.add('d-none');
        }
    }

    /**
     * Render documents list table
     */
    renderDocumentsList() {
        const emptyDiv = document.getElementById('documentsListEmpty');
        const tableDiv = document.getElementById('documentsListTable');
        const tbody = document.getElementById('documentsListBody');

        if (!this.documents || this.documents.length === 0) {
            emptyDiv.classList.remove('d-none');
            tableDiv.classList.add('d-none');
            return;
        }

        tableDiv.classList.remove('d-none');
        emptyDiv.classList.add('d-none');

        tbody.innerHTML = this.documents.map(doc => {
            const createdDate = new Date(doc.created_at).toLocaleDateString();
            const statusBadge = this.getStatusBadge(doc);
            const actions = this.getDocumentActions(doc);

            return `
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <i class="ri-file-pdf-2-line text-danger me-2" style="font-size: 1.5rem;"></i>
                            <div>
                                <div class="fw-semibold">
                                    ${doc.template?.name || 'Unknown Template'} 
                                    ${doc.version ? `<span class="badge bg-secondary ms-1">v${doc.version}</span>` : ''}
                                </div>
                                <small class="text-muted">#${doc.id}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="fw-semibold">${doc.template?.name || 'N/A'}</div>
                    </td>
                    <td>
                        <small>${createdDate}</small>
                        <br>
                        <small class="text-muted">${new Date(doc.created_at).toLocaleTimeString()}</small>
                    </td>
                    <td>
                        ${statusBadge}
                    </td>
                    <td class="text-end">
                        ${actions}
                    </td>
                </tr>
            `;
        }).join('');

        // Setup action listeners
        this.setupDocumentActions();
    }

    /**
     * Get status badge HTML
     */
    getStatusBadge(doc) {
        const isPdfGenerated = !!doc.pdf_path;
        const badges = [];

        if (doc.is_generated) {
            badges.push('<span class="badge bg-success me-1"><i class="ri-check-line me-1"></i>Generated</span>');
        }

        if (isPdfGenerated) {
            badges.push('<span class="badge bg-primary"><i class="ri-file-pdf-line me-1"></i>PDF</span>');
        }

        if (doc.is_signed) {
            badges.push('<span class="badge bg-info"><i class="ri-shield-check-line me-1"></i>Signed</span>');
        }

        return badges.length > 0 ? badges.join('') : '<span class="text-muted">Pending</span>';
    }

    /**
     * Get document action buttons HTML
     */
    getDocumentActions(doc) {
        let buttons = '';

        // View button
        buttons += `
            <button class="btn btn-sm btn-info me-2 view-document-btn" data-document-id="${doc.id}">
                <i class="ri-eye-line me-1"></i>View
            </button>
        `;

        // Download PDF button (if available)
        if (doc.pdf_path) {
            buttons += `
                <a href="/storage/${doc.pdf_path}" class="btn btn-sm btn-success me-2" download>
                    <i class="ri-download-line me-1"></i>Download
                </a>
            `;
        }

        // Delete button
        buttons += `
            <button class="btn btn-sm btn-danger delete-document-btn" data-document-id="${doc.id}">
                <i class="ri-delete-bin-line me-1"></i>Delete
            </button>
        `;

        return buttons;
    }

    /**
     * Setup document action listeners
     */
    setupDocumentActions() {
        document.querySelectorAll('.view-document-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const docId = btn.dataset.documentId;
                this.viewDocument(docId);
            });
        });

        document.querySelectorAll('.delete-document-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const docId = btn.dataset.documentId;
                this.deleteDocument(docId);
            });
        });
    }

    /**
     * View document content
     */
    async viewDocument(documentId) {
        const viewerModal = new bootstrap.Modal(document.getElementById('documentViewerModal'));
        const contentDiv = document.getElementById('documentViewerContent');
        const loadingDiv = document.getElementById('documentViewerLoading');
        const errorDiv = document.getElementById('documentViewerError');

        loadingDiv.classList.remove('d-none');
        contentDiv.classList.add('d-none');
        errorDiv.classList.add('d-none');

        viewerModal.show();

        try {
            const response = await $.ajax({
                url: `${this.docApiUrl}/documents/${documentId}/content`,
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            this.currentDocument = response.data || response;
            const content = response.content || response.data?.generated_document_content || '';

            if (!content) {
                throw new Error('No document content available');
            }

            contentDiv.innerHTML = content;
            loadingDiv.classList.add('d-none');
            contentDiv.classList.remove('d-none');

        } catch (error) {
            console.error('Error loading document:', error);
            loadingDiv.classList.add('d-none');
            errorDiv.classList.remove('d-none');
            document.getElementById('documentViewerErrorMessage').textContent = 
                error.responseJSON?.message || 'Failed to load document content';
        }
    }

    /**
     * Download document as PDF
     */
    downloadDocument() {
        if (!this.currentDocument) {
            alert('No document loaded');
            return;
        }

        const pdfPath = this.currentDocument.pdf_path || this.currentDocument.data?.pdf_path;
        if (!pdfPath) {
            alert('PDF not available for this document');
            return;
        }

        const link = document.createElement('a');
        link.href = `/storage/${pdfPath}`;
        link.download = true;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    /**
     * Delete document
     */
    async deleteDocument(documentId) {
        if (!confirm('Are you sure you want to delete this document? This action cannot be undone.')) {
            return;
        }

        try {
            await $.ajax({
                url: `${this.docApiUrl}/documents/${documentId}`,
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json'
                }
            });

            this.showDocumentsAlert('success', 'Document deleted successfully');
            setTimeout(() => this.loadDocumentsList(), 1500);

        } catch (error) {
            console.error('Error deleting document:', error);
            const errorMessage = error.responseJSON?.message || 'Failed to delete document';
            this.showDocumentsAlert('error', errorMessage);
        }
    }

    /**
     * Show alert in generate modal
     */
    showGenerateAlert(type, message) {
        const alertDiv = document.getElementById('generateDocumentAlert');
        const alertMessage = document.getElementById('generateDocumentAlertMessage');

        alertDiv.className = `alert alert-${type} d-block`;
        alertMessage.textContent = message;

        // Auto-hide success alerts
        if (type === 'success') {
            setTimeout(() => {
                alertDiv.classList.add('d-none');
            }, 5000);
        }
    }

    /**
     * Show alert in documents list modal
     */
    showDocumentsAlert(type, message) {
        const alertDiv = document.getElementById('documentsListAlert');
        const alertMessage = document.getElementById('documentsListAlertMessage');

        alertDiv.className = `alert alert-${type} d-block`;
        alertMessage.textContent = message;

        // Auto-hide success alerts
        if (type === 'success') {
            setTimeout(() => {
                alertDiv.classList.add('d-none');
            }, 5000);
        }
    }

    /**
     * Cleanup viewer modal
     */
    cleanupViewer() {
        document.getElementById('documentViewerContent').innerHTML = '';
        document.getElementById('documentViewerError').classList.add('d-none');
        this.currentDocument = null;
    }

    /**
     * Public method to trigger document generation modal
     */
    openGenerateModal(leaseId) {
        if (leaseId) {
            this.setLeaseId(leaseId);
        }
        const modal = new bootstrap.Modal(document.getElementById('generateDocumentModal'));
        modal.show();
    }

    /**
     * Public method to trigger documents list modal
     */
    openDocumentsModal(leaseId) {
        if (leaseId) {
            this.setLeaseId(leaseId);
        }
        const modal = new bootstrap.Modal(document.getElementById('documentsListModal'));
        modal.show();
    }
}

// Initialize globally
const documentManager = new DocumentGenerationManager();
