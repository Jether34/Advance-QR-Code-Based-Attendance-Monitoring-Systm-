<?php
// developer_traffic.php - Audit/traffic monitor for logins, signups, and key events
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['developer_id'])) {
    header('Location: developer_login.php');
    exit;
}

$pdo = get_db();

// Check for CSV export request
$export_csv = isset($_GET['export']) && $_GET['export'] === 'csv';

// Filters
$q           = trim($_GET['q'] ?? ''); // email or ip contains
$event_type  = trim($_GET['event_type'] ?? '');
$user_role   = trim($_GET['user_role'] ?? '');
$date_from   = trim($_GET['date_from'] ?? '');
$date_to     = trim($_GET['date_to'] ?? '');
$limit       = (int)($_GET['limit'] ?? 200);
if ($limit <= 0 || $limit > 1000) { $limit = 200; }
// For CSV, export all matching records (up to 10k)
if ($export_csv) { $limit = 10000; }

$where = [];
$params = [];

if ($q !== '') {
    $where[] = '(email LIKE :q OR ip_address LIKE :q)';
    $params[':q'] = "%$q%";
}
if ($event_type !== '') {
    $where[] = 'event_type = :event_type';
    $params[':event_type'] = $event_type;
}
if ($user_role !== '') {
    $where[] = 'user_role = :user_role';
    $params[':user_role'] = $user_role;
}
if ($date_from !== '') {
    $where[] = 'created_at >= :date_from';
    $params[':date_from'] = $date_from . ' 00:00:00';
}
if ($date_to !== '') {
    $where[] = 'created_at <= :date_to';
    $params[':date_to'] = $date_to . ' 23:59:59';
}

$sql = 'SELECT * FROM system_events';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY created_at DESC, id DESC LIMIT ' . (int)$limit;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// CSV Export
if ($export_csv) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="traffic_log_' . date('Y-m-d_His') . '.csv"');
    $output = fopen('php://output', 'w');
    
    // CSV Headers
    fputcsv($output, ['Time', 'Event Type', 'User Role', 'User ID', 'Email', 'IP Address', 'Device/Platform', 'Method', 'Path', 'Status Code', 'Object Type', 'Object ID', 'Context JSON', 'Result', 'Latency (ms)', 'Message']);
    
    foreach ($events as $e) {
        // Parse device/platform
        $ua = $e['user_agent'] ?? '';
        $device = 'Unknown';
        $platform = '';
        
        if ($ua) {
            if (stripos($ua, 'Windows') !== false) $platform = 'Windows';
            elseif (stripos($ua, 'Mac OS') !== false || stripos($ua, 'Macintosh') !== false) $platform = 'macOS';
            elseif (stripos($ua, 'Linux') !== false) $platform = 'Linux';
            elseif (stripos($ua, 'Android') !== false) $platform = 'Android';
            elseif (stripos($ua, 'iOS') !== false || stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) $platform = 'iOS';
            
            if (stripos($ua, 'Mobile') !== false || stripos($ua, 'Android') !== false || stripos($ua, 'iPhone') !== false) {
                $device = 'Mobile';
            } elseif (stripos($ua, 'Tablet') !== false || stripos($ua, 'iPad') !== false) {
                $device = 'Tablet';
            } else {
                $device = 'Desktop';
            }
            
            $device_info = $device . ($platform ? ' / ' . $platform : '');
        } else {
            $device_info = 'Unknown';
        }
        
        fputcsv($output, [
            $e['created_at'],
            $e['event_type'],
            $e['user_role'] ?? '',
            $e['user_id'] ?? '',
            $e['email'] ?? '',
            $e['ip_address'] ?? '',
            $device_info,
            $e['method'] ?? '',
            $e['path'] ?? '',
            $e['status_code'] ?? '',
            $e['object_type'] ?? '',
            $e['object_id'] ?? '',
            $e['context_json'] ?? '',
            ((int)$e['success'] === 1) ? 'OK' : 'FAILED',
            $e['latency_ms'] ?? '',
            $e['message'] ?? '',
        ]);
    }
    
    fclose($output);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Traffic Monitor - Developer</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #f3faf6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 24px; }
        .container { max-width: 1280px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .header h1 { color: #1e5128; }
        .filters { background: #fff; border: 1px solid #e2efe7; padding: 16px; border-radius: 10px; margin-bottom: 16px; display: grid; grid-template-columns: repeat(auto-fit,minmax(180px,1fr)); gap: 12px; }
        .filters input, .filters select { width: 100%; padding: 8px 10px; border: 1px solid #cfe7dc; border-radius: 8px; }
        .filters .actions { grid-column: 1 / -1; display: flex; gap: 10px; }
        .btn { padding: 10px 14px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; text-decoration: none; display: inline-block; }
        .btn-primary { background: #2d6a4f; color: #fff; }
        .btn-secondary { background: #eaf5ef; color: #2d6a4f; }
        .btn-export { background: #0c5460; color: #fff; }
        table { width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #e2efe7; border-radius: 10px; overflow: hidden; }
        th, td { padding: 10px 12px; border-bottom: 1px solid #eef5f1; font-size: 0.95em; text-align: left; }
        th { background: #d8f3dc; color: #1e5128; text-transform: uppercase; font-size: 0.82em; letter-spacing: 0.4px; }
        .chip { display: inline-block; padding: 4px 8px; border-radius: 999px; font-size: 0.8em; font-weight: 700; }
        .chip.ok { background: #e6f7ed; color: #1e5128; border: 1px solid #c6ead6; }
        .chip.fail { background: #fdecea; color: #c0392b; border: 1px solid #f5c6cb; }
        .muted { color: #6b7c70; font-size: 0.88em; }
        .back-link { text-decoration: none; color: #2d6a4f; font-weight: 600; }
    </style>
    <script>
        function clearFilters(){
            const params = new URLSearchParams(window.location.search);
            ['q','event_type','user_role','date_from','date_to','limit'].forEach(k=>params.delete(k));
            window.location.search = params.toString();
        }
    </script>
    </head>
<body>
  <div class="container">
    <div class="header">
      <h1>📈 Traffic Monitor</h1>
      <a class="back-link" href="developer_dashboard.php">← Back to Dashboard</a>
    </div>

    <form method="get" class="filters">
      <div>
        <label>Search (email or IP)</label>
        <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="juan@example.com or 192.168.1.10" />
      </div>
      <div>
        <label>Event Type</label>
        <select name="event_type">
          <option value="">All</option>
          <?php
            $types = ['login_success','login_failed','signup_success','developer_login_success','developer_login_failed','attendance_scan','report_export'];
            foreach ($types as $t) {
                $sel = $event_type === $t ? 'selected' : '';
                echo "<option value=\"".htmlspecialchars($t)."\" $sel>".htmlspecialchars($t)."</option>";
            }
          ?>
        </select>
      </div>
      <div>
        <label>User Role</label>
        <select name="user_role">
          <option value="">All</option>
          <?php
            $roles = ['student','teacher','developer'];
            foreach ($roles as $r) {
                $sel = $user_role === $r ? 'selected' : '';
                echo "<option value=\"".htmlspecialchars($r)."\" $sel>".htmlspecialchars($r)."</option>";
            }
          ?>
        </select>
      </div>
      <div>
        <label>Date From</label>
        <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>" />
      </div>
      <div>
        <label>Date To</label>
        <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>" />
      </div>
      <div>
        <label>Limit</label>
        <input type="number" name="limit" min="1" max="1000" value="<?php echo (int)$limit; ?>" />
      </div>
      <div class="actions">
        <button class="btn btn-primary" type="submit">Apply Filters</button>
        <button class="btn btn-secondary" type="button" onclick="clearFilters()">Clear</button>
        <a class="btn btn-export" href="?<?php echo http_build_query(array_merge($_GET, ['export' => 'csv'])); ?>">📥 Export CSV</a>
      </div>
    </form>

    <table>
      <thead>
        <tr>
          <th>Time</th>
          <th>Event</th>
          <th>Role</th>
          <th>User/Email</th>
          <th>IP Address</th>
          <th>Device/Platform</th>
          <th>Method</th>
          <th>Path</th>
          <th>Object</th>
          <th>Context</th>
          <th>Result</th>
          <th>Latency</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$events): ?>
          <tr><td colspan="12" class="muted">No events found for given filters.</td></tr>
        <?php else: ?>
          <?php foreach ($events as $e): ?>
            <?php
              // Parse device/platform from user agent
              $ua = $e['user_agent'] ?? '';
              $device = 'Unknown';
              $platform = '';
              
              if ($ua) {
                // Detect platform
                if (stripos($ua, 'Windows') !== false) $platform = 'Windows';
                elseif (stripos($ua, 'Mac OS') !== false || stripos($ua, 'Macintosh') !== false) $platform = 'macOS';
                elseif (stripos($ua, 'Linux') !== false) $platform = 'Linux';
                elseif (stripos($ua, 'Android') !== false) $platform = 'Android';
                elseif (stripos($ua, 'iOS') !== false || stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) $platform = 'iOS';
                
                // Detect device type
                if (stripos($ua, 'Mobile') !== false || stripos($ua, 'Android') !== false || stripos($ua, 'iPhone') !== false) {
                  $device = 'Mobile';
                } elseif (stripos($ua, 'Tablet') !== false || stripos($ua, 'iPad') !== false) {
                  $device = 'Tablet';
                } else {
                  $device = 'Desktop';
                }
                
                $device_info = $device . ($platform ? ' / ' . $platform : '');
              } else {
                $device_info = 'Unknown';
              }
            ?>
            <tr>
              <td><?php echo htmlspecialchars($e['created_at']); ?></td>
              <td><?php echo htmlspecialchars($e['event_type']); ?></td>
              <td><?php echo htmlspecialchars($e['user_role'] ?? ''); ?></td>
              <td>
                <?php
                  $label = '';
                  if (!empty($e['email'])) $label = $e['email'];
                  if (!empty($e['user_id'])) $label = ($label ? $label.' / ' : '').('ID: '.$e['user_id']);
                  echo htmlspecialchars($label);
                ?>
              </td>
              <td><strong><?php echo htmlspecialchars($e['ip_address'] ?? 'N/A'); ?></strong></td>
              <td class="muted" title="<?php echo htmlspecialchars($ua); ?>">
                <?php echo htmlspecialchars($device_info); ?>
              </td>
              <td><?php echo htmlspecialchars($e['method'] ?? ''); ?></td>
              <td class="muted" title="<?php echo htmlspecialchars($e['path'] ?? ''); ?>">
                <?php $p = $e['path'] ?? ''; echo htmlspecialchars(mb_strimwidth($p, 0, 30, '…')); ?>
              </td>
              <td class="muted">
                <?php
                  $obj = '';
                  if (!empty($e['object_type'])) $obj = $e['object_type'];
                  if (!empty($e['object_id'])) $obj .= ($obj ? '#' : '') . $e['object_id'];
                  echo htmlspecialchars($obj);
                ?>
              </td>
              <td class="muted" title="<?php echo htmlspecialchars($e['context_json'] ?? ''); ?>">
                <?php $ctx = $e['context_json'] ?? ''; echo htmlspecialchars(mb_strimwidth($ctx, 0, 36, '…')); ?>
              </td>
              <td>
                <?php if ((int)$e['success'] === 1): ?>
                    <span class="chip ok">OK</span>
                <?php else: ?>
                    <span class="chip fail">FAILED</span>
                <?php endif; ?>
              </td>
              <td class="muted"><?php echo isset($e['latency_ms']) ? htmlspecialchars($e['latency_ms']).'ms' : ''; ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
