<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Activity Logs</h2>

                <div class="flex gap-4">
                    <button type="button" onclick="document.getElementById('filters').classList.toggle('hidden')"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Filters
                    </button>

                    <form action="<?= BASE_PATH ?>/admin/activity-logs/export" method="POST" class="inline-block">
                        <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Export
                        </button>
                    </form>
                </div>
            </div>

            <div id="filters" class="hidden mb-6 p-4 bg-gray-50 rounded-lg">
                <form action="" method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label for="action" class="block text-sm font-medium text-gray-700">Action</label>
                        <select id="action" name="action" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Actions</option>
                            <option value="login" <?= isset($_GET['action']) && $_GET['action'] === 'login' ? 'selected' : '' ?>>Login</option>
                            <option value="register" <?= isset($_GET['action']) && $_GET['action'] === 'register' ? 'selected' : '' ?>>Register</option>
                            <option value="payment" <?= isset($_GET['action']) && $_GET['action'] === 'payment' ? 'selected' : '' ?>>Payment</option>
                        </select>
                    </div>

                    <div>
                        <label for="user" class="block text-sm font-medium text-gray-700">User</label>
                        <input type="text" id="user" name="user" value="<?= htmlspecialchars($_GET['user'] ?? '') ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700">Date From</label>
                        <input type="date" id="date_from" name="date_from" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700">Date To</label>
                        <input type="date" id="date_to" name="date_to" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div class="sm:col-span-2 lg:col-span-4 flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($activities as $activity): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= date('M j, Y H:i:s', strtotime($activity['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= htmlspecialchars($activity['user_name'] ?? 'System') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= htmlspecialchars($activity['action']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?= htmlspecialchars($activity['details']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= htmlspecialchars($activity['ip_address']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($pagination)): ?>
                <div class="mt-4">
                    <?php require __DIR__ . '/../components/pagination.php'; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('bulk-action-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    try {
        const formData = new FormData(this);
        const response = await fetch('/admin/bulk-actions', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();

        if (result.success) {
            alert('Bulk action processed successfully');
            window.location.reload();
        } else {
            alert(result.message || 'Failed to process bulk action');
        }
    } catch (error) {
        console.error('Bulk action error:', error);
        alert('An error occurred while processing the bulk action');
    }
});

function validateForm() {
    const action = document.getElementById('action').value;
    if (!action) {
        alert('Please select an action');
        return false;
    }

    if (action === 'email') {
        const subject = document.getElementById('email-subject').value;
        const message = document.getElementById('email-message').value;
        if (!subject || !message) {
            alert('Please fill in both subject and message');
            return false;
        }
    }

    return true;
}
</script>

