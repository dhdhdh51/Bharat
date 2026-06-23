<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_admin();
if (!is_super_admin()) { http_response_code(403); include __DIR__ . '/../includes/denied.php'; exit; }

// Generate a SQL dump and stream as download
if (input('download') === '1') {
    $pdo = DB::conn();
    if (!$pdo) { flash_set('error', 'Database unavailable.'); redirect('admin/pages/backup.php'); }

    header('Content-Type: application/sql; charset=utf-8');
    header('Content-Disposition: attachment; filename="bharatseo-backup-' . date('Ymd-His') . '.sql"');

    echo "-- Bharat SEO database backup\n-- Generated: " . date('Y-m-d H:i:s') . "\n";
    echo "SET FOREIGN_KEY_CHECKS=0;\nSET NAMES utf8mb4;\n\n";

    $tables = [];
    foreach ($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_NUM) as $t) {
        $tables[] = $t[0];
    }
    foreach ($tables as $table) {
        $create = $pdo->query('SHOW CREATE TABLE `' . $table . '`')->fetch(PDO::FETCH_NUM);
        echo "DROP TABLE IF EXISTS `$table`;\n" . $create[1] . ";\n\n";
        $rows = $pdo->query('SELECT * FROM `' . $table . '`');
        foreach ($rows as $row) {
            $cols = array_map(fn($c) => '`' . $c . '`', array_keys($row));
            $vals = array_map(function ($v) use ($pdo) {
                return $v === null ? 'NULL' : $pdo->quote((string) $v);
            }, array_values($row));
            echo 'INSERT INTO `' . $table . '` (' . implode(',', $cols) . ') VALUES (' . implode(',', $vals) . ");\n";
        }
        echo "\n";
    }
    echo "SET FOREIGN_KEY_CHECKS=1;\n";
    activity_log((int) $_SESSION['admin_id'], 'db_backup', 'Database backup downloaded');
    exit;
}

$pageTitle = 'Database Backup';
include __DIR__ . '/../includes/header.php';
?>
<div class="ad-card">
  <div class="ad-card__head"><h2>Database Backup</h2></div>
  <p style="color:var(--ad-muted);margin-bottom:16px;">Download a full SQL dump of your database. Store it safely — it contains all your content and settings.</p>
  <a href="<?= e(admin_url('pages/backup.php?download=1')) ?>" class="ad-btn ad-btn--primary"><i class="fas fa-download"></i> Download Backup (.sql)</a>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
