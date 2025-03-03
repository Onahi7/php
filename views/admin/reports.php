<?php
if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
    header('HTTP/1.1 403 Forbidden');
    exit('Invalid CSRF token');
}
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Generate Reports</h2>

            <form action="<?= BASE_PATH ?>/admin/reports/generate" method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="report_type" class="block text-sm font-medium text-gray-700">Report Type</label>
                        <select id="report_type" name="report_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="participants">Participants Report</option>
                            <option value="payments">Payments Report</option>
                            <option value="activities">Activities Report</option>
                        </select>
                    </div>

                    <div>
                        <label for="format" class="block text-sm font-medium text-gray-700">Format</label>
                        <select id="format" name="format" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="pdf">PDF</option>
                            <option value="csv">CSV</option>
                            <option value="excel">Excel</option>
                        </select>
                    </div>

                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700">Date From</label>
                        <input type="date" id="date_from" name="date_from" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700">Date To</label>
                        <input type="date" id="date_to" name="date_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($reports)): ?>
    <div class="mt-8 bg-white shadow-sm rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Reports</h3>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Report Type</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Format</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Generated At</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($reports as $report): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= ucfirst($report['type']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= strtoupper($report['format']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= date('M j, Y H:i', strtotime($report['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if (file_exists(BASE_PATH . '/uploads/reports/' . $report['filename'])): ?>
                                    <a href="<?= BASE_PATH ?>/uploads/reports/<?= htmlspecialchars($report['filename']) ?>" 
                                       class="text-blue-600 hover:text-blue-900">Download</a>
                                <?php else: ?>
                                    <span class="text-red-600">File not found</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

