<div class="participants-list">
    <h1>Participants</h1>
    
    <div class="filters">
        <form action="" method="GET" class="filter-form">
            <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <select name="status">
                <option value="">All Status</option>
                <option value="pending" <?= isset($_GET['status']) && $_GET['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="verified" <?= isset($_GET['status']) && $_GET['status'] === 'verified' ? 'selected' : '' ?>>Verified</option>
            </select>
            <button type="submit" class="btn">Filter</button>
        </form>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Registration Date</th>
                <th>Payment Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($participants as $participant): ?>
            <tr>
                <td><?= htmlspecialchars($participant['name']) ?></td>
                <td><?= htmlspecialchars($participant['email']) ?></td>
                <td><?= date('Y-m-d', strtotime($participant['created_at'])) ?></td>
                <td><?= $participant['payment_status'] ?></td>
                <td>
                    <a href="<?= BASE_PATH ?>/admin/participant/<?= $participant['id'] ?>" class="btn btn-small">View</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php require_once __DIR__ . '/../components/pagination.php'; ?>
</div>

