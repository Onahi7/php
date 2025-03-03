<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/mailer/email_queue.php';

// Create lock file
$lockFile = __DIR__ . '/email_queue.lock';
if (file_exists($lockFile)) {
    $lockTime = filectime($lockFile);
    if (time() - $lockTime < 3600) { // 1 hour lock
        die("Process already running\n");
    }
}
touch($lockFile);

try {
    $emailQueue = new EmailQueue($conn);
    $emailQueue->processQueue(50);
    $emailQueue->cleanupQueue();
    
    // Log stats
    $stats = $emailQueue->getQueueStats();
    error_log("Email queue processed: " . print_r($stats, true));
    
} catch (Exception $e) {
    error_log("Email queue error: " . $e->getMessage());
} finally {
    unlink($lockFile);
}

