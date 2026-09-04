<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$pdo = bolso_db();
$error = null;
$workshops = [];

if ($pdo) {
    try {
        // Handle POST Actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_admin_csrf($_POST['csrf_token'] ?? null)) {
                $error = 'Session token invalid. Please refresh the page.';
            } else {
                $action = (string)($_POST['action'] ?? '');

                // 1. Edit Workshop
                if ($action === 'edit_workshop') {
                    $id = filter_var($_POST['workshop_id'] ?? null, FILTER_VALIDATE_INT);
                    $title = trim((string)($_POST['title'] ?? ''));
                    $onlinePrice = (float)($_POST['online_price'] ?? 0);
                    $offlinePrice = (float)($_POST['offline_price'] ?? 0);
                    $duration = (int)($_POST['duration_days'] ?? 1);
                    $maxStudents = (int)($_POST['max_students'] ?? 6);
                    $active = isset($_POST['active']) ? 1 : 0;

                    if (!$id || $title === '') {
                        $error = 'Please provide a valid workshop title and details.';
                    } else {
                        $stmt = $pdo->prepare(
                            'UPDATE workshops 
                             SET title = :title, online_price = :online_price, offline_price = :offline_price, 
                                 duration_days = :duration, max_students = :max_students, active = :active 
                             WHERE id = :id'
                        );
                        $stmt->execute([
                            ':title' => $title,
                            ':online_price' => $onlinePrice,
                            ':offline_price' => $offlinePrice,
                            ':duration' => $duration,
                            ':max_students' => $maxStudents,
                            ':active' => $active,
                            ':id' => $id,
                        ]);
                        admin_flash('success', "Workshop '{$title}' updated successfully.");
                        header('Location: workshops.php');
                        exit;
                    }
                }

                // 2. Add New Workshop
                elseif ($action === 'add_workshop') {
                    $slug = trim((string)($_POST['slug'] ?? ''));
                    $title = trim((string)($_POST['title'] ?? ''));
                    $onlinePrice = (float)($_POST['online_price'] ?? 0);
                    $offlinePrice = (float)($_POST['offline_price'] ?? 0);
                    $duration = (int)($_POST['duration_days'] ?? 1);
                    $maxStudents = (int)($_POST['max_students'] ?? 6);
                    $active = isset($_POST['active']) ? 1 : 0;

                    $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower($slug));

                    if ($slug === '' || $title === '') {
                        $error = 'Slug and title are required for a new workshop.';
                    } else {
                        try {
                            $stmt = $pdo->prepare(
                                'INSERT INTO workshops (slug, title, duration_days, online_price, offline_price, max_students, active)
                                 VALUES (:slug, :title, :duration, :online_price, :offline_price, :max_students, :active)'
                            );
                            $stmt->execute([
                                ':slug' => $slug,
                                ':title' => $title,
                                ':duration' => $duration,
                                ':online_price' => $onlinePrice,
                                ':offline_price' => $offlinePrice,
                                ':max_students' => $maxStudents,
                                ':active' => $active,
                            ]);
                            admin_flash('success', "New workshop '{$title}' created!");
                            header('Location: workshops.php');
                            exit;
                        } catch (PDOException $e) {
                            $error = 'A workshop with this slug already exists.';
                        }
                    }
                }

                // 3. Toggle Workshop Status
                elseif ($action === 'toggle_status') {
                    $id = filter_var($_POST['workshop_id'] ?? null, FILTER_VALIDATE_INT);
                    $newStatus = (int)($_POST['status'] ?? 0);
                    if ($id) {
                        $stmt = $pdo->prepare('UPDATE workshops SET active = :active WHERE id = :id');
                        $stmt->execute([':active' => $newStatus, ':id' => $id]);
                        admin_flash('success', 'Workshop status updated.');
                        header('Location: workshops.php');
                        exit;
                    }
                }
            }
        }

        // Fetch all workshops with student count
        $workshops = $pdo->query(
            'SELECT w.*, COUNT(r.id) as student_count 
             FROM workshops w 
             LEFT JOIN registrations r ON r.workshop = w.slug 
             GROUP BY w.id 
             ORDER BY w.duration_days ASC'
        )->fetchAll();

    } catch (PDOException $e) {
        error_log('Workshops admin load error: ' . $e->getMessage());
        $error = 'Could not load workshops data.';
    }
}

$pageTitle = 'Manage Workshops';
$isAdminArea = true;
$adminActiveTab = 'workshops';
require __DIR__ . '/../includes/header.php';
?>

<main class="admin-page-container">
    <div class="container-fluid px-lg-4">
        <div class="dashboard-heading">
            <div>
                <span class="eyebrow">BOLSO Studio / Curriculum</span>
                <h1>Workshops & Pricing</h1>
            </div>
            <div class="dash-actions">
                <button type="button" class="btn btn-primary-bolso" data-bs-toggle="modal" data-bs-target="#newWorkshopModal">
                    <i class="bi bi-plus-lg"></i> Add Workshop
                </button>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert bolso-alert mt-4" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php $flash = take_admin_flash(); if ($flash): ?>
            <div class="alert alert-success bolso-success-alert mt-4" role="alert">
                <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div class="admin-card p-0 mt-4">
            <div class="table-responsive">
                <table class="table bolso-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Workshop</th>
                            <th>Slug / Identifier</th>
                            <th>Duration</th>
                            <th>Online Price</th>
                            <th>Offline Studio Price</th>
                            <th>Max Batch Size</th>
                            <th>Enrolled</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($workshops)): ?>
                        <tr><td colspan="9" class="empty-row">No workshops configured in database.</td></tr>
                    <?php else: ?>
                        <?php foreach ($workshops as $ws): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($ws['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars($ws['slug'], ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td><?= (int)$ws['duration_days'] ?> Days</td>
                            <td><strong class="text-primary-emphasis">₹<?= number_format((float)$ws['online_price']) ?></strong></td>
                            <td><strong class="text-primary-emphasis">₹<?= number_format((float)$ws['offline_price']) ?></strong></td>
                            <td><?= (int)$ws['max_students'] ?> students</td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary-emphasis px-2 py-1">
                                    <?= (int)$ws['student_count'] ?> students
                                </span>
                            </td>
                            <td>
                                <?php if ($ws['active']): ?>
                                    <span class="badge-active"><i class="bi bi-check-circle"></i> Active</span>
                                <?php else: ?>
                                    <span class="badge-inactive"><i class="bi bi-pause-circle"></i> Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <div class="manage-actions justify-content-end">
                                    <button type="button" class="btn btn-sm btn-outline-secondary p-1" title="Edit Workshop"
                                            onclick='openEditWsModal(<?= json_encode($ws, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    <!-- Quick Toggle Active Status -->
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="workshop_id" value="<?= (int)$ws['id'] ?>">
                                        <input type="hidden" name="status" value="<?= $ws['active'] ? '0' : '1' ?>">
                                        <button class="btn btn-sm btn-outline-secondary p-1" type="submit" 
                                                title="<?= $ws['active'] ? 'Deactivate workshop' : 'Activate workshop' ?>">
                                            <i class="bi <?= $ws['active'] ? 'bi-toggle-on text-success' : 'bi-toggle-off text-secondary' ?>"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- MODAL: Add New Workshop -->
<div class="modal fade bolso-modal" id="newWorkshopModal" tabindex="-1" aria-labelledby="newWorkshopModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="add_workshop">
                
                <div class="modal-header">
                    <div>
                        <span class="eyebrow">Curriculum Creation</span>
                        <h5 class="modal-title" id="newWorkshopModalLabel">Add New Workshop Tier</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label" for="ws_title">Workshop Title *</label>
                            <input type="text" class="form-control" id="ws_title" name="title" placeholder="e.g. Masterclass Fabric Art" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label" for="ws_slug">URL Slug *</label>
                            <input type="text" class="form-control" id="ws_slug" name="slug" placeholder="e.g. masterclass" required>
                            <small class="text-muted" style="font-size: 10px;">Letters, numbers, and dashes only</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="ws_online_price">Online Price (₹) *</label>
                            <input type="number" class="form-control" id="ws_online_price" name="online_price" value="499" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="ws_offline_price">Offline Studio Price (₹) *</label>
                            <input type="number" class="form-control" id="ws_offline_price" name="offline_price" value="699" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="ws_duration">Duration (Days)</label>
                            <input type="number" class="form-control" id="ws_duration" name="duration_days" value="2" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="ws_max_students">Max Batch Capacity</label>
                            <input type="number" class="form-control" id="ws_max_students" name="max_students" value="6" min="1" required>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="ws_active" name="active" value="1" checked>
                                <label class="form-check-label" for="ws_active">Publish and accept registrations immediately</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-bolso">Create Workshop</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Edit Workshop -->
<div class="modal fade bolso-modal" id="editWorkshopModal" tabindex="-1" aria-labelledby="editWorkshopModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="edit_workshop">
                <input type="hidden" name="workshop_id" id="edit_ws_id">
                
                <div class="modal-header">
                    <div>
                        <span class="eyebrow">Modify Curriculum</span>
                        <h5 class="modal-title" id="editWorkshopModalLabel">Edit Workshop Details</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="edit_ws_title">Workshop Title *</label>
                            <input type="text" class="form-control" id="edit_ws_title" name="title" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="edit_ws_online">Online Price (₹) *</label>
                            <input type="number" class="form-control" id="edit_ws_online" name="online_price" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="edit_ws_offline">Offline Studio Price (₹) *</label>
                            <input type="number" class="form-control" id="edit_ws_offline" name="offline_price" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="edit_ws_duration">Duration (Days)</label>
                            <input type="number" class="form-control" id="edit_ws_duration" name="duration_days" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="edit_ws_max">Max Batch Capacity</label>
                            <input type="number" class="form-control" id="edit_ws_max" name="max_students" min="1" required>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="edit_ws_active" name="active" value="1">
                                <label class="form-check-label" for="edit_ws_active">Active & Open for Registration</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-bolso">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditWsModal(ws) {
    document.getElementById('edit_ws_id').value = ws.id;
    document.getElementById('edit_ws_title').value = ws.title || '';
    document.getElementById('edit_ws_online').value = ws.online_price || 0;
    document.getElementById('edit_ws_offline').value = ws.offline_price || 0;
    document.getElementById('edit_ws_duration').value = ws.duration_days || 1;
    document.getElementById('edit_ws_max').value = ws.max_students || 6;
    document.getElementById('edit_ws_active').checked = (parseInt(ws.active) === 1);

    const modal = new bootstrap.Modal(document.getElementById('editWorkshopModal'));
    modal.show();
}
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
