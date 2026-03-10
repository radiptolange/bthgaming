<?php
require_once __DIR__ . '/../includes/auth_check.php';

$auth = new Auth($pdo);
$message = '';

if (isset($_GET['del'])) {
    $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->execute([$_GET['del']]);
    header("Location: messages_manage.php");
    exit;
}

$messages = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC")->fetchAll();

$page_title = "Manage Messages - " . SITE_NAME;
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row px-lg-4">
        <div class="col-12 mb-4 border-bottom border-secondary pb-3">
            <h2 class="neon-text border-start border-4 border-info ps-3 text-uppercase">Contact Messages</h2>
        </div>

        <div class="col-12">
            <div class="card shadow p-0 border-secondary">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle">
                        <thead class="bg-black">
                            <tr class="small text-secondary fw-bold border-bottom border-secondary">
                                <th class="ps-4">SENDER</th>
                                <th>SUBJECT</th>
                                <th>DATE</th>
                                <th class="text-center pe-4">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($messages as $m): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-white">
                                    <?php echo htmlspecialchars($m['name']); ?><br>
                                    <small class="text-secondary"><?php echo htmlspecialchars($m['email']); ?></small>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($m['subject']); ?></strong><br>
                                    <small class="text-secondary"><?php echo substr(htmlspecialchars($m['content']), 0, 50); ?>...</small>
                                </td>
                                <td><small class="text-info"><?php echo $m['created_at']; ?></small></td>
                                <td class="text-center pe-4">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-info" onclick="alert('<?php echo addslashes(htmlspecialchars($m['content'])); ?>')">View</button>
                                        <a href="?del=<?php echo $m['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete message?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($messages)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-secondary">No messages received.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
