<?php
require_once '../../includes/core/Auth.php';
require_once '../../includes/core/Logger.php';

// Ensure user is admin
if (!Auth::isAdmin()) {
    header('Location: ' . BASE_PATH . '/admin/login.php');
    exit;
}

$logger = Logger::getInstance();

// Get filter parameters
$level = $_GET['level'] ?? 'error';
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');
$limit = $_GET['limit'] ?? 100;

// Get logs
$logs = $logger->getLogs($level, $startDate, $endDate, $limit);
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold text-gray-900">System Logs</h1>
        <div class="flex space-x-4">
            <button onclick="clearOldLogs()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                Clear Old Logs
            </button>
            <button onclick="downloadLogs()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Download Logs
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <form id="logFilter" class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Log Level</label>
                <select name="level" class="w-full border-gray-300 rounded-md shadow-sm">
                    <option value="emergency" <?= $level === 'emergency' ? 'selected' : '' ?>>Emergency</option>
                    <option value="alert" <?= $level === 'alert' ? 'selected' : '' ?>>Alert</option>
                    <option value="critical" <?= $level === 'critical' ? 'selected' : '' ?>>Critical</option>
                    <option value="error" <?= $level === 'error' ? 'selected' : '' ?>>Error</option>
                    <option value="warning" <?= $level === 'warning' ? 'selected' : '' ?>>Warning</option>
                    <option value="notice" <?= $level === 'notice' ? 'selected' : '' ?>>Notice</option>
                    <option value="info" <?= $level === 'info' ? 'selected' : '' ?>>Info</option>
                    <option value="debug" <?= $level === 'debug' ? 'selected' : '' ?>>Debug</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" 
                       class="w-full border-gray-300 rounded-md shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" 
                       class="w-full border-gray-300 rounded-md shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Limit</label>
                <input type="number" name="limit" value="<?= htmlspecialchars($limit) ?>" min="1" max="1000" 
                       class="w-full border-gray-300 rounded-md shadow-sm">
            </div>
            <div class="md:col-span-4">
                <button type="submit" class="w-full px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Log Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Context</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($logs as $log): ?>
                        <?php
                        preg_match('/^\[(.*?)\] \[(.*?)\] \[(.*?)\] \[User:(.*?)\] (.*?) ({.*})?$/', $log, $matches);
                        $timestamp = $matches[1] ?? '';
                        $level = $matches[2] ?? '';
                        $requestId = $matches[3] ?? '';
                        $userId = $matches[4] ?? '';
                        $message = $matches[5] ?? '';
                        $context = $matches[6] ?? '';
                        
                        $levelClass = match (strtolower($level)) {
                            'emergency', 'alert', 'critical' => 'bg-red-100 text-red-800',
                            'error' => 'bg-orange-100 text-orange-800',
                            'warning' => 'bg-yellow-100 text-yellow-800',
                            'notice' => 'bg-blue-100 text-blue-800',
                            'info' => 'bg-green-100 text-green-800',
                            default => 'bg-gray-100 text-gray-800'
                        };
                        ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= htmlspecialchars($timestamp) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $levelClass ?>">
                                    <?= htmlspecialchars($level) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?= htmlspecialchars($message) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php if ($context): ?>
                                    <pre class="whitespace-pre-wrap"><?= htmlspecialchars($context) ?></pre>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function clearOldLogs() {
    if (!confirm('Are you sure you want to clear logs older than 30 days?')) {
        return;
    }

    fetch('/includes/handlers/log_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'clear_old_logs',
            csrf_token: '<?= CSRF::generateToken() ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Old logs cleared successfully');
            location.reload();
        } else {
            alert('Failed to clear logs: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while clearing logs');
    });
}

function downloadLogs() {
    const form = document.getElementById('logFilter');
    const formData = new FormData(form);
    const queryString = new URLSearchParams(formData).toString();
    
    window.location.href = `/includes/handlers/log_handler.php?action=download&${queryString}`;
}

// Auto-submit form when filters change
document.querySelectorAll('#logFilter select, #logFilter input').forEach(element => {
    element.addEventListener('change', () => {
        document.getElementById('logFilter').submit();
    });
});
</script>
