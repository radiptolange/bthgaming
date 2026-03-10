<?php
require_once __DIR__ . '/config/config.php';

$message = '';
$auth = new Auth($pdo);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCSRFToken($token)) { die("CSRF validation failed."); }

    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $content = $_POST['message'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO messages (name, email, subject, content) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$name, $email, $subject, $content])) {
        $message = '<div class="alert alert-success">Your message has been sent. We will get back to you soon!</div>';
    }
}

$page_title = "Contact Us - " . SITE_NAME;
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row px-lg-4 align-items-center">
        <div class="col-lg-6 mb-5 mb-lg-0">
            <h1 class="display-3 fw-bold neon-text mb-4">GET IN TOUCH</h1>
            <p class="lead text-secondary mb-5">Have questions about upcoming tournaments or want to join the BTH Gaming community? Drop us a message!</p>

            <div class="d-flex flex-column gap-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-info bg-opacity-10 p-3 rounded border border-info border-opacity-25">📍</div>
                    <div>
                        <h6 class="mb-0 text-white fw-bold">HEADQUARTERS</h6>
                        <small class="text-secondary">Boys And The Hood Gaming Community</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-info bg-opacity-10 p-3 rounded border border-info border-opacity-25">📧</div>
                    <div>
                        <h6 class="mb-0 text-white fw-bold">EMAIL SUPPORT</h6>
                        <small class="text-secondary"><?php echo ADMIN_EMAIL; ?></small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow p-4 p-lg-5 border-info border-opacity-25">
                <?php echo $message; ?>
                <form action="" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCSRFToken(); ?>">
                    <div class="mb-4">
                        <label class="form-label small fw-bold">FULL NAME</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold">EMAIL ADDRESS</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold">SUBJECT</label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold">MESSAGE</label>
                        <textarea name="message" class="form-control" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-neon w-100 py-3 fw-bold">SEND MESSAGE</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
