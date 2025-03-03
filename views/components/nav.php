<nav class="main-nav">
    <ul>
        <li><a href="<?= BASE_PATH ?>/">Home</a></li>
        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="<?= BASE_PATH ?>/dashboard">Dashboard</a></li>
            <li><a href="<?= BASE_PATH ?>/profile">Profile</a></li>
            <li><a href="<?= BASE_PATH ?>/logout">Logout</a></li>
        <?php else: ?>
            <li><a href="<?= BASE_PATH ?>/login">Login</a></li>
            <li><a href="<?= BASE_PATH ?>/register">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>

