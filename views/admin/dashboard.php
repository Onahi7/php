<div class="max-w-7xl mx-auto">
    <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Admin Dashboard</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-blue-50 rounded-lg p-6">
                <h3 class="text-lg font-medium text-blue-900">Total Registrations</h3>
                <p class="mt-2 text-3xl font-bold text-blue-600"><?= $stats['total_registrations'] ?></p>
                <p class="mt-2 text-sm text-blue-600">of <?= MAX_PARTICIPANTS ?> slots</p>
            </div>
            
            <div class="bg-green-50 rounded-lg p-6">
                <h3 class="text-lg font-medium text-green-900">Total Payments</h3>
                <p class="mt-2 text-3xl font-bold text-green-600">₦<?= number_format($stats['total_payments'], 2) ?></p>
                <p class="mt-2 text-sm text-green-600"><?= $stats['payment_count'] ?> successful payments</p>
            </div>
            
            <div class="bg-yellow-50 rounded-lg p-6">
                <h3 class="text-lg font-medium text-yellow-900">Pending Verifications</h3>
                <p class="mt-2 text-3xl font-bold text-yellow-600"><?= $stats['pending_verifications'] ?></p>
                <p class="mt-2 text-sm text-yellow-600">require attention</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white shadow-sm rounded-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Recent Registrations</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($recent_registrations as $registration): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?= htmlspecialchars($registration['name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= date('M j, Y', strtotime($registration['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?= $registration['status'] === 'verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                    <?= ucfirst($registration['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Recent Payments</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($recent_payments as $payment): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?= htmlspecialchars($payment['reference']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ₦<?= number_format($payment['amount'], 2) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?= $payment['status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                                       ($payment['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                    <?= ucfirst($payment['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

