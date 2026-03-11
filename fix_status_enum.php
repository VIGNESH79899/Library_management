<?php
/**
 * fix_status_enum.php — One-time migration
 * Run once via browser: https://library-management-6km7.onrender.com/fix_status_enum.php
 * DELETE this file after running successfully.
 */
include "config/db.php";

$steps = [];

// 1. Alter the ENUM to include 'Unavailable'
$alter = $conn->query(
    "ALTER TABLE book MODIFY COLUMN Status ENUM('Available','Unavailable','Issued') NOT NULL DEFAULT 'Available'"
);
$steps[] = $alter
    ? "✅ ENUM altered: Status now supports 'Available', 'Unavailable', 'Issued'."
    : "❌ ENUM alter failed: " . $conn->error;

// 2. Migrate any legacy 'Issued' rows where Available_Quantity <= 0 to 'Unavailable'
$migrate = $conn->query(
    "UPDATE book SET Status='Unavailable' WHERE Status='Issued' AND Available_Quantity <= 0"
);
$steps[] = $migrate
    ? "✅ Legacy 'Issued' rows migrated to 'Unavailable' (affected: " . $conn->affected_rows . " rows)."
    : "❌ Migration failed: " . $conn->error;

// 3. Fix any rows where Available_Quantity <= 0 but Status is still 'Available'
$fix = $conn->query(
    "UPDATE book SET Status='Unavailable' WHERE Available_Quantity <= 0 AND Status='Available'"
);
$steps[] = $fix
    ? "✅ Fixed rows with 0 availability stuck as 'Available' (affected: " . $conn->affected_rows . " rows)."
    : "❌ Fix failed: " . $conn->error;

// 4. Fix any rows where Available_Quantity > 0 but Status is 'Unavailable'
$fix2 = $conn->query(
    "UPDATE book SET Status='Available' WHERE Available_Quantity > 0 AND Status='Unavailable'"
);
$steps[] = $fix2
    ? "✅ Fixed rows with available copies stuck as 'Unavailable' (affected: " . $conn->affected_rows . " rows)."
    : "❌ Fix2 failed: " . $conn->error;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>DB Migration — Fix Status ENUM</title>
<style>
  body { font-family: sans-serif; max-width: 640px; margin: 60px auto; background: #f8fafc; color: #1e293b; }
  h1   { font-size: 1.4rem; margin-bottom: 1rem; }
  p    { padding: 10px 14px; border-radius: 8px; margin: 8px 0; background: #fff; border: 1px solid #e2e8f0; font-size: 0.92rem; }
  .ok  { border-left: 4px solid #22c55e; }
  .err { border-left: 4px solid #ef4444; }
  .warn { margin-top: 24px; background: #fffbeb; border: 1px solid #fcd34d; padding: 12px 16px; border-radius: 8px; font-size: 0.88rem; }
</style>
</head>
<body>
  <h1>🔧 DB Migration: book.Status ENUM Fix</h1>
  <?php foreach ($steps as $s): ?>
    <p class="<?= str_starts_with($s, '✅') ? 'ok' : 'err' ?>"><?= htmlspecialchars($s) ?></p>
  <?php endforeach; ?>
  <div class="warn">
    ⚠️ <strong>Important:</strong> Delete <code>fix_status_enum.php</code> from your server after this migration is complete.
  </div>
</body>
</html>
