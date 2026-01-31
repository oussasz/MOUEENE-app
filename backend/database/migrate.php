<?php
/**
 * Simple DB migration runner
 *
 * Usage:
 *   php backend/database/migrate.php
 *
 * Notes:
 * - Reads DB config from backend/config/database.php (env-based).
 * - Applies *.sql files in backend/database/migrations in lexical order.
 * - Records applied migrations in schema_migrations.
 */

require_once __DIR__ . '/../config/database.php';

$migrationsDir = __DIR__ . '/migrations';

if (!is_dir($migrationsDir)) {
    fwrite(STDERR, "Migrations dir not found: {$migrationsDir}\n");
    exit(1);
}

$db = Database::getConnection();
if ($db === null) {
    fwrite(STDERR, "Database connection failed. Check your env (.env / environment variables).\n");
    exit(1);
}

function ensureMigrationsTable(PDO $db): void {
    $db->exec(
        "CREATE TABLE IF NOT EXISTS schema_migrations (\n"
        . "  id INT AUTO_INCREMENT PRIMARY KEY,\n"
        . "  migration VARCHAR(255) NOT NULL UNIQUE,\n"
        . "  applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP\n"
        . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
    );
}

ensureMigrationsTable($db);

// Already applied migrations
$applied = [];
$stmt = $db->query("SELECT migration FROM schema_migrations");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $applied[$row['migration']] = true;
}

$files = glob($migrationsDir . '/*.sql');
if (!$files) {
    echo "No migrations found.\n";
    exit(0);
}

sort($files, SORT_STRING);

$appliedCount = 0;
foreach ($files as $file) {
    $name = basename($file);
    if (isset($applied[$name])) {
        continue;
    }

    $sql = file_get_contents($file);
    if ($sql === false) {
        fwrite(STDERR, "Failed reading migration: {$name}\n");
        exit(1);
    }

    echo "Applying {$name}...\n";

    try {
        $db->beginTransaction();
        $db->exec($sql);
        $ins = $db->prepare("INSERT INTO schema_migrations (migration) VALUES (?)");
        $ins->execute([$name]);
        $db->commit();
        $appliedCount++;
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        fwrite(STDERR, "Migration failed: {$name}\n");
        fwrite(STDERR, $e->getMessage() . "\n");
        exit(1);
    }
}

echo "Done. Applied {$appliedCount} migration(s).\n";
