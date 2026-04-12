document.addEventListener('DOMContentLoaded', function () {
    const tableContainer = document.querySelector('.table-responsive');
    const table = document.querySelector('.custom-table');

    if (!table) return;

    const moduleName = table.dataset.module;
    if (!moduleName) return;

    // Elements
    const selectionActions = document.getElementById('selectionActions');
    const selectAllCheckbox = document.getElementById('selectAll');
    const selectedCountElement = document.getElementById('selectedCount');
    const deselectAllBtn = document.getElementById('deselectAll');
    const exportExcelBtn = document.getElementById('exportExcel');
    const exportPdfBtn = document.getElementById('exportPdf');

    // State
    let selectedIds = new Set();
    let checkboxes = document.querySelectorAll('.row-checkbox');

    // Initialize
    function init() {
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('click', toggleSelectAll);
        }
        if (deselectAllBtn) {
            deselectAllBtn.addEventListener('click', deselectAll);
        }

        // Re-bind row checkboxes (useful after ajax reload if applied)
        bindRowCheckboxes();

        // Export Buttons
        if (exportExcelBtn) exportExcelBtn.addEventListener('click', () => openExportModal('excel'));
        if (exportPdfBtn) exportPdfBtn.addEventListener('click', () => openExportModal('pdf'));

        // Confirm Export
        document.addEventListener('click', function (e) {
            if (e.target && e.target.id === 'confirmExportBtn') {
                confirmExport();
            }
        });
    }

    function bindRowCheckboxes() {
        checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => {
            cb.removeEventListener('click', toggleRow); // prevent double binding
            cb.addEventListener('click', toggleRow);
        });
    }

    // Row selection
    function toggleRow(e) {
        e.stopPropagation();
        const id = this.dataset.id;
        const row = this.closest('tr');

        if (this.classList.contains('checked')) {
            this.classList.remove('checked');
            row.classList.remove('selected');
            selectedIds.delete(id);
        } else {
            this.classList.add('checked');
            row.classList.add('selected');
            selectedIds.add(id);
        }
        updateUI();
    }

    // Select All
    function toggleSelectAll() {
        const isSelectAll = !this.classList.contains('checked');

        checkboxes.forEach(cb => {
            const id = cb.dataset.id;
            const row = cb.closest('tr');

            if (isSelectAll) {
                cb.classList.add('checked');
                row.classList.add('selected');
                selectedIds.add(id);
            } else {
                cb.classList.remove('checked');
                row.classList.remove('selected');
                selectedIds.delete(id);
            }
        });

        if (isSelectAll) this.classList.add('checked');
        else this.classList.remove('checked');

        updateUI();
    }

    // Deselect All
    function deselectAll() {
        selectedIds.clear();
        checkboxes.forEach(cb => {
            cb.classList.remove('checked');
            cb.closest('tr').classList.remove('selected');
        });
        if (selectAllCheckbox) selectAllCheckbox.classList.remove('checked');
        updateUI();
    }

    // Update UI
    function updateUI() {
        const count = selectedIds.size;
        if (selectedCountElement) selectedCountElement.textContent = count;

        if (count > 0) {
            if (selectionActions) selectionActions.style.display = 'flex';
        } else {
            if (selectionActions) selectionActions.style.display = 'none';
        }

        // Update SelectAll state
        if (checkboxes.length > 0 && count === checkboxes.length) {
            selectAllCheckbox.classList.add('checked');
        } else {
            selectAllCheckbox.classList.remove('checked');
        }
    }

    // Export Modal Logic
    function openExportModal(format) {
        const modalEl = document.getElementById('exportModal');
        if (!modalEl) return;

        const modal = new bootstrap.Modal(modalEl);
        const title = document.getElementById('exportModalTitle');
        const formatInput = document.getElementById('exportFormat');
        const confirmBtn = document.getElementById('confirmExportBtn');
        const countDisplay = document.getElementById('exportCountDisplay');
        const alertBox = document.getElementById('exportAlert');
        const alertIcon = document.getElementById('exportAlertIcon');

        if (formatInput) formatInput.value = format;
        if (countDisplay) countDisplay.textContent = selectedIds.size;

        if (format === 'excel') {
            if (title) title.innerHTML = '<i class="fas fa-file-excel me-2 text-success"></i> Exportar a Excel';
            if (confirmBtn) confirmBtn.className = 'btn btn-outline-success btn-sm px-4';
            if (alertBox) alertBox.className = 'alert alert-success border-0 bg-success bg-opacity-10 d-flex align-items-center mb-4';
            if (alertIcon) alertIcon.className = 'fas fa-info-circle me-3 fs-5 text-success';
        } else {
            if (title) title.innerHTML = '<i class="fas fa-file-pdf me-2 text-danger"></i> Exportar a PDF';
            if (confirmBtn) confirmBtn.className = 'btn btn-outline-danger btn-sm px-4';
            if (alertBox) alertBox.className = 'alert alert-danger border-0 bg-danger bg-opacity-10 d-flex align-items-center mb-4';
            if (alertIcon) alertIcon.className = 'fas fa-info-circle me-3 fs-5 text-danger';
        }

        modal.show();
    }

    function confirmExport() {
        const format = document.getElementById('exportFormat').value;
        const ids = Array.from(selectedIds);

        // Optional checkboxes in modal
        // We collect all checked inputs in modal body to pass as params
        const modalBody = document.querySelector('#exportModal .modal-body');
        const options = {};
        if (modalBody) {
            modalBody.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                options[cb.id] = cb.checked;
            });
        }

        // Close modal
        const modalEl = document.getElementById('exportModal');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        modalInstance.hide();

        // Show loading
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Generando archivo...',
                text: 'Por favor espere un momento.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
        }

        // Create Form
        const form = document.createElement('form');
        form.method = 'POST';
        // Route: /export/{module}/{format}
        form.action = `/export/${moduleName}/${format}`;

        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);

        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            form.appendChild(input);
        });

        // Add options
        Object.keys(options).forEach(key => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = options[key];
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();

        // Clean up
        setTimeout(() => {
            deselectAll();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Exportación iniciada',
                    text: 'El archivo se descargará automáticamente.',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
            form.remove();
        }, 1000);
    }

    // Handle row click (optional, if user clicks anywhere on row)
    document.addEventListener('click', function (e) {
        if (e.target.closest('tr') &&
            !e.target.closest('.btn-action-group') &&
            !e.target.closest('.custom-checkbox') &&
            !e.target.closest('a') &&
            !e.target.closest('button')) {

            const row = e.target.closest('tr');
            const checkbox = row.querySelector('.row-checkbox');
            if (checkbox) {
                // Manually trigger toggle logic
                checkbox.click();
            }
        }
    });

    // Expose init for external calls (e.g. after Livewire update or Ajax)
    window.TableExport = { init, updateUI, deselectAll };

    init();
});
