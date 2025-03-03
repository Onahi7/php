<?php
namespace Summit\Core;

class QueueManager {
    private $cache;
    private static $instance = null;
    private $queuePrefix = 'summit:queue:';

    private function __construct() {
        $this->cache = CacheManager::getInstance();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function addToQueue($queue, $task, $data) {
        $job = [
            'id' => uniqid('job_', true),
            'task' => $task,
            'data' => $data,
            'added_at' => time(),
            'attempts' => 0,
            'status' => 'pending'
        ];

        try {
            $queueKey = $this->queuePrefix . $queue;
            $this->cache->set("job:{$job['id']}", json_encode($job));
            $this->cache->cache->rPush($queueKey, $job['id']);
            return $job['id'];
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to add job to queue: " . $e->getMessage());
            return false;
        }
    }

    public function processQueue($queue, $callback) {
        $queueKey = $this->queuePrefix . $queue;

        while (true) {
            try {
                // Get job from queue
                $jobId = $this->cache->cache->lPop($queueKey);
                if (!$jobId) {
                    break;
                }

                $jobData = $this->cache->get("job:{$jobId}");
                if (!$jobData) {
                    continue;
                }

                $job = json_decode($jobData, true);
                $job['attempts']++;
                $job['status'] = 'processing';
                $job['started_at'] = time();

                // Update job status
                $this->cache->set("job:{$jobId}", json_encode($job));

                // Process job
                try {
                    $result = $callback($job['task'], $job['data']);
                    $this->markJobComplete($jobId, $result);
                } catch (\Exception $e) {
                    $this->handleJobFailure($jobId, $job, $e->getMessage());
                }

            } catch (\Exception $e) {
                ErrorHandler::logError("Queue processing error: " . $e->getMessage());
                sleep(1); // Prevent tight loop in case of persistent errors
            }
        }
    }

    private function markJobComplete($jobId, $result = null) {
        try {
            $jobData = $this->cache->get("job:{$jobId}");
            if (!$jobData) {
                return;
            }

            $job = json_decode($jobData, true);
            $job['status'] = 'completed';
            $job['completed_at'] = time();
            $job['result'] = $result;

            $this->cache->set("job:{$jobId}", json_encode($job));
            $this->cache->set("job_result:{$jobId}", json_encode($result));
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to mark job complete: " . $e->getMessage());
        }
    }

    private function handleJobFailure($jobId, $job, $error) {
        try {
            $maxAttempts = 3;
            if ($job['attempts'] < $maxAttempts) {
                // Requeue job
                $queueKey = $this->queuePrefix . $job['task'];
                $job['status'] = 'failed_retry';
                $job['last_error'] = $error;
                $this->cache->set("job:{$jobId}", json_encode($job));
                $this->cache->cache->rPush($queueKey, $jobId);
            } else {
                // Mark as failed
                $job['status'] = 'failed';
                $job['last_error'] = $error;
                $this->cache->set("job:{$jobId}", json_encode($job));
                ErrorHandler::logError("Job {$jobId} failed after {$maxAttempts} attempts: {$error}");
            }
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to handle job failure: " . $e->getMessage());
        }
    }

    public function getJobStatus($jobId) {
        try {
            $jobData = $this->cache->get("job:{$jobId}");
            return $jobData ? json_decode($jobData, true) : null;
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to get job status: " . $e->getMessage());
            return null;
        }
    }

    public function clearQueue($queue) {
        try {
            $queueKey = $this->queuePrefix . $queue;
            return $this->cache->cache->del($queueKey);
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to clear queue: " . $e->getMessage());
            return false;
        }
    }

    // Example: Email Queue Processing
    public function processEmailQueue() {
        $this->processQueue('email', function($task, $data) {
            // Process email sending
            return mail($data['to'], $data['subject'], $data['message'], $data['headers'] ?? '');
        });
    }

    // Example: Export Queue Processing
    public function processExportQueue() {
        $this->processQueue('export', function($task, $data) {
            // Process data export
            $exporter = new DataExporter();
            return $exporter->export($data['type'], $data['filters']);
        });
    }
}
