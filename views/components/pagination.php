<?php
/**
 * Pagination component
 * 
 * Usage:
 * $pagination = [
 *     'current_page' => 1,
 *     'total_pages' => 10,
 *     'base_url' => '/admin/participants'
 * ];
 * include 'components/pagination.php';
 */
?>
<div class="pagination">
    <div class="pagination-info">
        Page <?= $pagination['current_page'] ?> of <?= $pagination['total_pages'] ?>
    </div>
    
    <div class="pagination-links">
        <?php if ($pagination['current_page'] > 1): ?>
            <a href="<?= $pagination['base_url'] ?>?page=1<?= isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : '' ?><?= isset($_GET['status']) ? '&status=' . htmlspecialchars($_GET['status']) : '' ?>" class="pagination-link">
                First
            </a>
            <a href="<?= $pagination['base_url'] ?>?page=<?= $pagination['current_page'] - 1 ?><?= isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : '' ?><?= isset($_GET['status']) ? '&status=' . htmlspecialchars($_GET['status']) : '' ?>" class="pagination-link">
                Previous
            </a>
        <?php endif; ?>
        
        <?php
        // Show page numbers
        $start = max(1, $pagination['current_page'] - 2);
        $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
        
        for ($i = $start; $i <= $end; $i++):
        ?>
            <a href="<?= $pagination['base_url'] ?>?page=<?= $i ?><?= isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : '' ?><?= isset($_GET['status']) ? '&status=' . htmlspecialchars($_GET['status']) : '' ?>" class="pagination-link <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
        
        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
            <a href="<?= $pagination['base_url'] ?>?page=<?= $pagination['current_page'] + 1 ?><?= isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : '' ?><?= isset($_GET['status']) ? '&status=' . htmlspecialchars($_GET['status']) : '' ?>" class="pagination-link">
                Next
            </a>
            <a href="<?= $pagination['base_url'] ?>?page=<?= $pagination['total_pages'] ?><?= isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : '' ?><?= isset($_GET['status']) ? '&status=' . htmlspecialchars($_GET['status']) : '' ?>" class="pagination-link">
                Last
            </a>
        <?php endif; ?>
    </div>
</div>

