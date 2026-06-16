<?php
class Notifications extends Controller {
    private $notificationModel;

    public function __construct() {
        $this->requireLogin();
        $this->notificationModel = $this->model('Notification');
    }

    public function markAllRead() {
        $this->notificationModel->markAllAsRead($_SESSION['user_id']);
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? URLROOT));
        exit;
    }

    public function read($id) {
        $this->notificationModel->markAsRead($id, $_SESSION['user_id']);
        // Redirect logic can be complex, for now just back
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? URLROOT));
        exit;
    }
}
