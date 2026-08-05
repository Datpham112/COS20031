<?php
require_once __DIR__ . '/config/database.php';
$pdo = get_db_connection();

// Table name => which page(s) on the site use it.
$tables = [
    'Depot'                => ['Workshop', 'Dashboard'],
    'Workshop'             => ['Workshop'],
    'Vehicle'              => ['Workshop', 'Dashboard'],
    'Predictive_Alert'     => ['Workshop', 'Dashboard'],
    'Maintenance_Job'      => ['Workshop', 'Dashboard'],
    'Driver'               => ['Dashboard'],
    'Driver_Safety_Score'  => ['Dashboard'],
    'Driver_Certification' => ['Dashboard'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Workshop & Dashboard Data</title>
<style>
  body { font-family: -apple-system, Segoe UI, Arial, sans-serif; background:#f5f6f8; margin:0; padding:24px; color:#171c22; }
  h1 { font-size:20px; margin-bottom:4px; }
  .sub { color:#8a93a2; font-size:13px; margin-bottom:20px; }
  .toc { background:#fff; border:1px solid #e4e7ec; border-radius:10px; padding:14px 18px; margin-bottom:24px; }
  .toc a { display:inline-block; margin:3px 8px 3px 0; font-size:12.5px; color:#158c7a; text-decoration:none; background:#e4f3ef; padding:4px 10px; border-radius:14px; }
  .table-block { background:#fff; border:1px solid #e4e7ec; border-radius:10px; margin-bottom:28px; overflow-x:auto; }
  .table-head { padding:12px 18px; border-bottom:1px solid #e4e7ec; display:flex; justify-content:space-between; align-items:center; gap:10px; }
  .table-head h2 { font-size:14.5px; margin:0; font-family: monospace; }
  .table-head-left { display:flex; align-items:center; gap:8px; }
  .badge { font-size:10px; text-transform:uppercase; letter-spacing:.3px; padding:2px 8px; border-radius:10px; background:#eef1f5; color:#5b6472; }
  .table-head span.count { font-size:11.5px; color:#8a93a2; white-space:nowrap; }
  table { border-collapse:collapse; width:100%; font-size:12.5px; }
  th { text-align:left; padding:8px 14px; background:#fafbfc; border-bottom:1px solid #e4e7ec; white-space:nowrap; color:#5b6472; text-transform:uppercase; font-size:10.5px; letter-spacing:.4px; }
  td { padding:8px 14px; border-bottom:1px solid #eee; white-space:nowrap; }
  tr:last-child td { border-bottom:none; }
  .empty { padding:16px 18px; color:#8a93a2; font-size:12.5px; }
  .error { background:#fbe9e7; color:#c4433b; padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:13px; }
</style>
</head>
<body>

<h1>Workshop &amp; Dashboard Data</h1>
<div class="sub">Database: <strong><?= htmlspecialchars(DB_NAME) ?></strong> · <?= count($tables) ?> tables · Generated <?= date('Y-m-d H:i:s') ?></div>

<div class="toc">
  <?php foreach ($tables as $t => $pages): ?>
    <a href="#<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></a>
  <?php endforeach; ?>
</div>

<?php foreach ($tables as $table => $pages): ?>
  <div class="table-block" id="<?= htmlspecialchars($table) ?>">
    <?php
    try {
        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll();
        $rowCount = count($rows);
    } catch (PDOException $e) {
        echo '<div class="error">Error reading ' . htmlspecialchars($table) . ': ' . htmlspecialchars($e->getMessage()) . '</div>';
        continue;
    }
    ?>
    <div class="table-head">
      <div class="table-head-left">
        <h2><?= htmlspecialchars($table) ?></h2>
        <?php foreach ($pages as $page): ?>
          <span class="badge"><?= htmlspecialchars($page) ?></span>
        <?php endforeach; ?>
      </div>
      <span class="count"><?= $rowCount ?> row<?= $rowCount === 1 ? '' : 's' ?></span>
    </div>

    <?php if ($rowCount === 0): ?>
      <div class="empty">No data yet.</div>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <?php foreach (array_keys($rows[0]) as $col): ?>
              <th><?= htmlspecialchars($col) ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr>
              <?php foreach ($row as $val): ?>
                <td><?= $val === null ? '<span style="color:#bbb">NULL</span>' : htmlspecialchars((string) $val) ?></td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
<?php endforeach; ?>

</body>
</html>


