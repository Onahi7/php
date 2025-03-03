<div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" id="bulk-actions-modal" style="display: none;">
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pt-5 pb-4 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                <div>
                    <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">
                        Bulk Actions
                    </h3>
                    
                    <div class="mt-4">
                        <form id="bulk-action-form" class="space-y-4">
                            <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                            <input type="hidden" name="selected_ids" id="selected-ids">
                            
                            <div>
                                <label for="action" class="block text-sm font-medium text-gray-700">Action</label>
                                <select id="action" name="action" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select Action</option>
                                    <option value="approve">Approve Selected</option>
                                    <option value="email">Send Email</option>
                                    <option value="export">Export Data</option>
                                    <option value="update_status">Update Status</option>
                                </select>
                            </div>
                            
                            <div id="email-fields" style="display: none;">
                                <div class="space-y-4">
                                    <div>
                                        <label for="email-subject" class="block text-sm font-medium text-gray-700">Subject</label>
                                        <input type="text" id="email-subject" name="subject" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                    
                                    <div>
                                        <label for="email-message" class="block text-sm font-medium text-gray-700">Message</label>
                                        <textarea id="email-message" name="message" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="export-fields" style="display: none;">
                                <label for="export-format" class="block text-sm font-medium text-gray-700">Format</label>
                                <select id="export-format" name="format" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="pdf">PDF</option>
                                    <option value="csv">CSV</option>
                                    <option value="excel">Excel</option>
                                </select>
                            </div>
                            
                            <div id="status-fields" style="display: none;">
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                            </div>
                            
                            <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                                <button type="submit" class="inline-flex w-full justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:col-start-2 sm:text-sm">
                                    Process
                                </button>
                                <button type="button" onclick="closeBulkActionsModal()" class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:col-start-1 sm:mt-0 sm:text-sm">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const actionSelect = document.getElementById('action');
    const emailFields = document.getElementById('email-fields');
    const exportFields = document.getElementById('export-fields');
    const statusFields = document.getElementById('status-fields');
    
    actionSelect.addEventListener('change', function() {
        emailFields.style.display = this.value === 'email' ? 'block' : 'none';
        exportFields.style.display = this.value === 'export' ? 'block' : 'none';
        statusFields.style.display = this.value === 'update_status' ? 'block' : 'none';
    });
    
    document.getElementById('bulk-action-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('/admin/bulk-actions', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('Bulk action processed successfully');
                window.location.reload();
            } else {
                alert(result.message || 'Failed to process bulk action');
            }
        } catch (error) {
            alert('An error occurred while processing the bulk action');
        }
    });
});

function openBulkActionsModal(ids) {
    document.getElementById('selected-ids').value = ids.join(',');
    document.getElementById('bulk-actions-modal').style.display = 'block';
}

function closeBulkActionsModal() {
    document.getElementById('bulk-actions-modal').style.display = 'none';
}
</script>

