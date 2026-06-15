<?php
session_start();
require_once '../../config/database.php';
require_once '../../src/functions.php';

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script_name = $_SERVER['SCRIPT_NAME'];
$script_dir = dirname($script_name);
$public_pos = strpos($script_dir, '/public');
if ($public_pos !== false) {
    $project_root = substr($script_dir, 0, $public_pos);
} else {
    $project_root = dirname($script_dir);
}
if (!defined('URLROOT')) {
    define('URLROOT', $protocol . "://" . $host . $project_root);
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Authentication required.']);
        exit;
    }

    $employee_id = $_POST['employee_id'] ?? null;
    $request_id = $_POST['request_id'] ?? null;
    $leave_type_id = $_POST['leave_type_id'] ?? null;
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');
    $reason = trim($_POST['reason'] ?? '');

    if (empty($employee_id) || empty($leave_type_id) || empty($start_date) || empty($end_date)) {
        echo json_encode(['success' => false, 'message' => 'Leave type, start date, and end date are required.']);
        exit;
    }

    $attachment_path = $_POST['existing_attachment'] ?? null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $attachment_path = handle_upload('attachment', '../uploads/leaves/');
        if ($attachment_path) {
            $attachment_path = str_replace('../', '', $attachment_path);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload attachment.']);
            exit;
        }
    }

    try {
        if ($request_id) {
            $sql = "UPDATE leave_requests SET leave_type_id = :leave_type_id, start_date = :start_date, end_date = :end_date, reason = :reason, attachment_path = :attachment_path WHERE id = :id AND employee_id = :employee_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'leave_type_id' => $leave_type_id,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'reason' => $reason,
                'attachment_path' => $attachment_path,
                'id' => $request_id,
                'employee_id' => $employee_id
            ]);
            $message = 'Leave request updated successfully.';
        } else {
            $sql = "INSERT INTO leave_requests (employee_id, leave_type_id, start_date, end_date, reason, attachment_path) VALUES (:employee_id, :leave_type_id, :start_date, :end_date, :reason, :attachment_path)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'employee_id' => $employee_id,
                'leave_type_id' => $leave_type_id,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'reason' => $reason,
                'attachment_path' => $attachment_path
            ]);
            $message = 'Leave request submitted successfully.';
        }
        echo json_encode(['success' => true, 'message' => $message]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// --- GET request: Display the form ---
$employee_id = $_GET['employee_id'] ?? null;
$tenant_id = $_SESSION['tenant_id'] ?? null; // Assume tenant_id is in session
$request_id = $_GET['request_id'] ?? null;
$request = null;

// Fetch leave types for the current tenant
$leave_types = [];
if ($tenant_id) {
    $stmt_types = $pdo->prepare("SELECT * FROM leave_types WHERE tenant_id = :tenant_id ORDER BY name");
    $stmt_types->execute(['tenant_id' => $tenant_id]);
    $leave_types = $stmt_types->fetchAll();
}

if ($request_id) {
    $stmt = $pdo->prepare("SELECT * FROM leave_requests WHERE id = :id AND employee_id = :employee_id");
    $stmt->execute(['id' => $request_id, 'employee_id' => $employee_id]);
    $request = $stmt->fetch();
}
?>

<div class="modal-header">
    <h5 class="modal-title"><?= $request ? 'Edit' : 'Apply for' ?> Leave</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="leave_request_form" action="ajax/leave_request_form.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="employee_id" value="<?= htmlspecialchars($employee_id ?? '') ?>">
        <?php if ($request): ?>
            <input type="hidden" name="request_id" value="<?= htmlspecialchars($request['id'] ?? '') ?>">
            <input type="hidden" name="existing_attachment" value="<?= htmlspecialchars($request['attachment_path'] ?? '') ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label class="form-label">Leave Type</label>
            <select name="leave_type_id" class="form-select" required>
                <option value="">Select a leave type</option>
                <?php foreach ($leave_types as $type): ?>
                    <option value="<?= $type['id'] ?>" <?= (isset($request['leave_type_id']) && $request['leave_type_id'] == $type['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($type['name'] ?? '') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($request['start_date'] ?? '') ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($request['end_date'] ?? '') ?>" required>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Reason</label>
            <textarea name="reason" class="form-control" rows="3"><?= htmlspecialchars($request['reason'] ?? '') ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Attachment (optional)</label>
            <input type="file" name="attachment" class="form-control">
            <?php if ($request && !empty($request['attachment_path'])): ?>
                <small class="form-text text-muted">Current file: <a href="<?= htmlspecialchars($request['attachment_path'] ?? '') ?>" target="_blank">View</a></small>
            <?php endif; ?>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Cancel</button>
    <button type="button" id="save_leave_request_button" class="btn btn-primary">Submit Request</button>
</div>
