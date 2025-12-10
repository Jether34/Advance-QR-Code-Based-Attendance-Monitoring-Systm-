<?php
// developer_teachers.php - Developer-only teacher directory + add teacher form
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security_utils.php';

$csrf_token = generate_csrf_token();

if(!isset($_SESSION['developer_id'])){ header('Location: developer_login.php'); exit; }
$pdo = get_db();
$errors = [];$success='';

// Detect existing teacher table columns (handle schema variations)
$columns = [];
try {
    // Use prepared introspection instead of bare query for consistency
    $colStmt = $pdo->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'teachers'");
    $colStmt->execute();
    $columns = $colStmt->fetchAll(PDO::FETCH_COLUMN);
} catch(Exception $e){ $errors[]='Unable to read teachers table structure.'; }

$hasGender = in_array('gender',$columns,true);
$hasFaculty = in_array('faculty',$columns,true);
$hasUsername = in_array('username',$columns,true);

// Get distinct filter values
$distinct = ['grades'=>[],'strands'=>[],'sections'=>[]];
// Collect distinct values for filters (validate column names before using in SQL)
$allowedCols = ['grade_level','strand','section_block'];
foreach(['grade_level'=>'grades','strand'=>'strands','section_block'=>'sections'] as $col=>$key){
  if (!in_array($col, $allowedCols, true)) { $distinct[$key] = []; continue; }
  $sql = "SELECT DISTINCT `$col` AS v FROM teachers WHERE `$col` IS NOT NULL AND `$col` <> '' ORDER BY v";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $distinct[$key] = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
}

// Read filters
$filterGrade = trim($_GET['grade'] ?? '');
$filterStrand = trim($_GET['strand'] ?? '');
$filterSection = trim($_GET['section'] ?? '');
$search = trim($_GET['search'] ?? '');

// Handle UPDATE action
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action']) && $_POST['action']==='update_teacher'){
  $token = $_POST['csrf_token'] ?? '';
  if (!verify_csrf_token($token)) { $errors[] = 'Invalid CSRF token'; }
  else {
  $tid = (int)($_POST['teacher_id'] ?? 0);
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $grade = trim($_POST['grade_level'] ?? '');
    $strand = trim($_POST['strand'] ?? '');
    $section = trim($_POST['section_block'] ?? '');
    $faculty = trim($_POST['faculty'] ?? '');
    
    foreach(['full_name','email'] as $req){ if(empty($$req)) $errors[] = "$req is required"; }
    if($email && !filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Invalid email format';
    if(!$errors){
        $updates = [];$params=[':id'=>$tid];
        $updates[]='full_name = :full_name'; $params[':full_name']=$full_name;
        $updates[]='email = :email'; $params[':email']=$email;
        if($password!==''){ $updates[]='password = :password'; $params[':password']=password_hash($password,PASSWORD_DEFAULT); }
        if($grade!==''){ $updates[]='grade_level = :grade'; $params[':grade']=$grade; }
        if($strand!==''){ $updates[]='strand = :strand'; $params[':strand']=$strand; }
        if($section!==''){ $updates[]='section_block = :section'; $params[':section']=$section; }
        if($hasGender && $gender!==''){ $updates[]='gender = :gender'; $params[':gender']=$gender; }
        if($hasFaculty && $faculty!==''){ $updates[]='faculty = :faculty'; $params[':faculty']=$faculty; }
        $sql = 'UPDATE teachers SET '.implode(', ',$updates).' WHERE id = :id';
        try { $stmt=$pdo->prepare($sql); $stmt->execute($params); $success='Teacher updated successfully'; }
        catch(Exception $e){ $errors[]='Update failed: '.$e->getMessage(); }
      }
    }
}

// Handle DELETE action
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action']) && $_POST['action']==='delete_teacher'){
  $token = $_POST['csrf_token'] ?? '';
  if (!verify_csrf_token($token)) { $errors[] = 'Invalid CSRF token'; }
  else {
    $tid = (int)($_POST['teacher_id'] ?? 0);
    try { $stmt=$pdo->prepare('DELETE FROM teachers WHERE id = :id'); $stmt->execute([':id'=>$tid]); $success='Teacher deleted successfully'; }
    catch(Exception $e){ $errors[]='Delete failed: '.$e->getMessage(); }
  }
}

  if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action']) && $_POST['action']==='add_teacher'){
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) { $errors[] = 'Invalid CSRF token'; }
    else {
    $tid = trim($_POST['id'] ?? ''); // optional manual id
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $grade = trim($_POST['grade_level'] ?? '');
    $strand = trim($_POST['strand'] ?? '');
    $section = trim($_POST['section_block'] ?? '');
    $faculty = trim($_POST['faculty'] ?? '');
    $username = $hasUsername ? strtolower(str_replace(' ','_',substr($full_name,0,30))) : null;

    // Validation
    foreach(['full_name','email','password'] as $req){ if(empty($$req)) $errors[] = "$req is required"; }
    if($email && !filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Invalid email format';
    if(!$errors){
        $hash = password_hash($password,PASSWORD_DEFAULT);
        // Build dynamic insert
        $fields = [];$place=[];$params=[];
        if($tid!==''){ $fields[]='id'; $place[]=':id'; $params[':id']=$tid; }
        if($hasUsername){ $fields[]='username'; $place[]=':username'; $params[':username']=$username; }
        $fields[]='password'; $place[]=':password'; $params[':password']=$hash;
        $fields[]='full_name'; $place[]=':full_name'; $params[':full_name']=$full_name;
        $fields[]='email'; $place[]=':email'; $params[':email']=$email;
        if($grade!==''){ $fields[]='grade_level'; $place[]=':grade'; $params[':grade']=$grade; }
        if($strand!==''){ $fields[]='strand'; $place[]=':strand'; $params[':strand']=$strand; }
        if($section!==''){ $fields[]='section_block'; $place[]=':section'; $params[':section']=$section; }
        if($hasGender && $gender!==''){ $fields[]='gender'; $place[]=':gender'; $params[':gender']=$gender; }
        if($hasFaculty && $faculty!==''){ $fields[]='faculty'; $place[]=':faculty'; $params[':faculty']=$faculty; }
        // role default implicit
        $sql = 'INSERT INTO teachers (' . implode(',', $fields) . ') VALUES (' . implode(',', $place) . ')';
        try { $stmt=$pdo->prepare($sql); $stmt->execute($params); $success='Teacher added successfully'; }
        catch(Exception $e){ $errors[]='Insert failed: '.$e->getMessage(); }
        }
    }
}

// Fetch teachers with filters
$where = [];
$params = [];
if($filterGrade !== ''){ $where[] = 'grade_level = :grade'; $params[':grade'] = $filterGrade; }
if($filterStrand !== ''){ $where[] = 'strand = :strand'; $params[':strand'] = $filterStrand; }
if($filterSection !== ''){ $where[] = 'section_block = :section'; $params[':section'] = $filterSection; }
if($search !== ''){ $where[] = '(full_name LIKE :search OR email LIKE :search)'; $params[':search'] = "%$search%"; }

$sqlList = 'SELECT * FROM teachers';
if($where){ $sqlList .= ' WHERE ' . implode(' AND ', $where); }
$sqlList .= ' ORDER BY grade_level, strand, section_block, full_name';
$stmt = $pdo->prepare($sqlList);
$stmt->execute($params);
$teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
$male = [];
$female = [];
$other = [];
if($hasGender){
    foreach($teachers as $t){
        $g = strtolower($t['gender'] ?? '');
        if($g === 'male') $male[] = $t;
        elseif($g === 'female') $female[] = $t;
        else $other[] = $t;
    }
} else {
    $other = $teachers;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Developer Teacher Directory</title>
<link rel="stylesheet" href="style.css" />
<style>
 *{margin:0;padding:0;box-sizing:border-box;}
 body{font-family:'Segoe UI',-apple-system,BlinkMacSystemFont,Arial,sans-serif;background:linear-gradient(135deg,#1e5128 0%,#2d6a4f 100%);min-height:100vh;padding:24px;color:#2c3e50;}
 .wrap{max-width:1600px;margin:0 auto;}
 .head{background:linear-gradient(135deg,#ffffff 0%,#f8fffe 100%);border-radius:20px;padding:28px 40px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 10px 40px rgba(0,0,0,.12),0 2px 8px rgba(0,0,0,.06);margin-bottom:28px;border:1px solid rgba(255,255,255,0.8);}
 .head h1{margin:0;font-size:2em;color:#1e5128;font-weight:700;display:flex;align-items:center;gap:14px;letter-spacing:-0.5px;}
 .back-link{color:#1e5128;text-decoration:none;font-weight:600;padding:10px 20px;background:#d8f3dc;border-radius:10px;transition:all 0.3s;}
 .back-link:hover{background:#b7e4c7;transform:translateY(-2px);}
 .form-panel{background:linear-gradient(135deg,#ffffff 0%,#f8fffe 100%);border-radius:20px;padding:32px 40px;box-shadow:0 10px 40px rgba(0,0,0,.1),0 2px 8px rgba(0,0,0,.05);margin-bottom:28px;border:1px solid rgba(45,106,79,0.08);}
 .form-panel h2{margin:0 0 20px;font-size:1.4em;color:#1e5128;font-weight:700;padding-bottom:14px;border-bottom:3px solid #d8f3dc;}
 .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:18px;}
 .fg label{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#1e5128;display:block;margin-bottom:6px;}
 .fg input,.fg select{width:100%;padding:12px 14px;border:2px solid #d8f3dc;background:#f6fff7;border-radius:10px;font-size:.9em;transition:all 0.3s;}
 .fg input:focus,.fg select:focus{outline:none;border-color:#2d6a4f;box-shadow:0 0 0 4px rgba(45,106,79,.12);background:#fff;}
 .actions{margin-top:20px;display:flex;gap:14px;}
 .btn{padding:13px 26px;border:none;border-radius:11px;background:linear-gradient(135deg,#2d6a4f 0%,#1e5128 100%);color:#fff;font-weight:600;cursor:pointer;font-size:.95em;box-shadow:0 4px 12px rgba(45,106,79,0.3);transition:all 0.3s;}
 .btn:hover{transform:translateY(-2px);box-shadow:0 6px 18px rgba(45,106,79,0.5);}
 .msg.success{background:linear-gradient(135deg,#d4edda 0%,#c3e6cb 100%);color:#155724;padding:14px 20px;border-radius:12px;margin-bottom:18px;font-size:.9em;border-left:4px solid #28a745;font-weight:600;}
 .msg.error{background:linear-gradient(135deg,#f8d7da 0%,#f5c6cb 100%);color:#721c24;padding:14px 20px;border-radius:12px;margin-bottom:12px;font-size:.88em;border-left:4px solid #dc3545;font-weight:600;}
 .filters{background:linear-gradient(135deg,#ffffff 0%,#f8fffe 100%);border-radius:20px;padding:24px 32px;margin-bottom:24px;box-shadow:0 8px 28px rgba(0,0,0,.1);display:flex;flex-wrap:wrap;gap:16px;align-items:flex-end;border:1px solid rgba(45,106,79,0.08);}
 .filters label{font-size:.72rem;font-weight:700;text-transform:uppercase;color:#1e5128;display:block;margin-bottom:6px;letter-spacing:.6px;}
 .filters select,.filters input[type=text]{padding:11px 14px;border:2px solid #d8f3dc;border-radius:10px;background:#f6fff7;min-width:160px;font-size:.9em;transition:all 0.3s;}
 .filters select:focus,.filters input[type=text]:focus{outline:none;border-color:#2d6a4f;box-shadow:0 0 0 4px rgba(45,106,79,.12);background:#fff;}
 .columns{display:grid;grid-template-columns:repeat(auto-fit,minmax(400px,1fr));gap:24px;}
 .col{background:linear-gradient(135deg,#ffffff 0%,#f8fffe 100%);border-radius:20px;padding:28px 32px;box-shadow:0 10px 40px rgba(0,0,0,.08);display:flex;flex-direction:column;border:1px solid rgba(45,106,79,0.08);}
 .col h2{margin:0 0 20px;font-size:1.35em;color:#1e5128;font-weight:700;padding-bottom:14px;border-bottom:3px solid #d8f3dc;display:flex;align-items:center;gap:10px;}
 .tgrid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px;}
 .card{background:linear-gradient(135deg,#f6fff7 0%,#fff 100%);border:2px solid #d8f3dc;border-radius:14px;padding:18px 20px;font-size:.85em;display:flex;flex-direction:column;gap:6px;position:relative;transition:all 0.3s;box-shadow:0 2px 8px rgba(0,0,0,0.04);}
 .card:hover{transform:translateY(-4px);box-shadow:0 8px 20px rgba(45,106,79,0.15);border-color:#2d6a4f;}
 .idtag{position:absolute;top:8px;right:12px;font-size:.65rem;background:linear-gradient(135deg,#2d6a4f 0%,#1e5128 100%);color:#fff;padding:4px 10px;border-radius:8px;letter-spacing:.5px;font-weight:700;}
 .edit-btn,.del-btn{padding:8px 16px;border:none;border-radius:8px;font-size:.8em;cursor:pointer;font-weight:600;margin-top:10px;transition:all 0.3s;box-shadow:0 2px 6px rgba(0,0,0,0.1);}
 .edit-btn{background:linear-gradient(135deg,#3498db 0%,#2980b9 100%);color:#fff;}
 .edit-btn:hover{transform:translateY(-2px);box-shadow:0 4px 10px rgba(52,152,219,0.4);}
 .del-btn{background:linear-gradient(135deg,#e74c3c 0%,#c0392b 100%);color:#fff;}
 .del-btn:hover{transform:translateY(-2px);box-shadow:0 4px 10px rgba(231,76,60,0.4);}
 .modal{display:none;position:fixed;z-index:9999;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,.65);justify-content:center;align-items:center;}
 .modal.active{display:flex;}
 .modal-content{background:linear-gradient(135deg,#ffffff 0%,#f8fffe 100%);border-radius:20px;padding:32px 40px;max-width:650px;width:92%;max-height:92vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,0.3);}
 .modal-content h3{margin:0 0 24px;color:#1e5128;font-size:1.5em;font-weight:700;padding-bottom:14px;border-bottom:3px solid #d8f3dc;}
 .close-modal{float:right;font-size:2em;font-weight:700;color:#aaa;cursor:pointer;line-height:1;transition:all 0.2s;}
 .close-modal:hover{color:#e74c3c;transform:rotate(90deg);}
 .empty{color:#7f8c8d;font-style:italic;padding:20px;text-align:center;}
 @media(max-width:900px){.columns{grid-template-columns:1fr;} .tgrid{grid-template-columns:repeat(auto-fill,minmax(220px,1fr));}}
</style>
</head>
<body>
<div class="wrap">
  <div class="head">
    <h1>👨‍🏫 Teacher Directory</h1>
    <a class="back-link" href="developer_dashboard.php">← Back to Dashboard</a>
  </div>
  
  <form class="filters" method="get" action="">
    <div>
      <label for="grade">Grade</label>
      <select id="grade" name="grade">
        <option value="">All</option>
        <?php foreach($distinct['grades'] as $g): ?>
          <option value="<?php echo htmlspecialchars($g); ?>" <?php if($g===$filterGrade) echo 'selected'; ?>><?php echo htmlspecialchars($g); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label for="strand">Strand</label>
      <select id="strand" name="strand">
        <option value="">All</option>
        <?php foreach($distinct['strands'] as $s): ?>
          <option value="<?php echo htmlspecialchars($s); ?>" <?php if($s===$filterStrand) echo 'selected'; ?>><?php echo htmlspecialchars($s); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label for="section">Block/Section</label>
      <select id="section" name="section">
        <option value="">All</option>
        <?php foreach($distinct['sections'] as $sec): ?>
          <option value="<?php echo htmlspecialchars($sec); ?>" <?php if($sec===$filterSection) echo 'selected'; ?>><?php echo htmlspecialchars($sec); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label for="search">Search</label>
      <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Name / Email" />
    </div>
    <div>
      <button class="btn" type="submit">Filter</button>
    </div>
    <div>
      <a class="btn" style="background:#6c757d;text-decoration:none;" href="developer_teachers.php">Reset</a>
    </div>
  </form>
  
  <div class="form-panel">
    <h2>➕ Add Teacher</h2>
    <?php if($success): ?><div class="msg success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
    <?php foreach($errors as $er): ?><div class="msg error"><?php echo htmlspecialchars($er); ?></div><?php endforeach; ?>
    <form method="post" action="">
      <input type="hidden" name="action" value="add_teacher" />
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>" />
      <div class="grid">
        <div class="fg"><label for="id">ID (optional)</label><input type="number" id="id" name="id" min="1" /></div>
        <div class="fg"><label for="full_name">Full Name *</label><input type="text" id="full_name" name="full_name" required /></div>
        <div class="fg"><label for="email">Email *</label><input type="email" id="email" name="email" required /></div>
        <div class="fg"><label for="password">Password *</label><input type="text" id="password" name="password" required placeholder="Plain password" /></div>
        <?php if($hasGender): ?>
          <div class="fg"><label for="gender">Gender</label>
            <select id="gender" name="gender">
              <option value="">--</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
          </div>
        <?php endif; ?>
        <div class="fg"><label for="grade_level">Grade Level</label><input type="text" id="grade_level" name="grade_level" /></div>
        <div class="fg"><label for="strand">Strand</label><input type="text" id="strand" name="strand" /></div>
        <div class="fg"><label for="section_block">Section Block</label><input type="text" id="section_block" name="section_block" /></div>
        <?php if($hasFaculty): ?><div class="fg"><label for="faculty">Faculty</label><input type="text" id="faculty" name="faculty" /></div><?php endif; ?>
      </div>
      <div class="actions">
        <button class="btn" type="submit">Add Teacher</button>
        <button class="btn" type="reset" style="background:#6c757d">Reset</button>
      </div>
    </form>
  </div>
  <div class="columns">
    <?php if($hasGender): ?>
    <div class="col">
      <h2>👨 Male (<?php echo count($male); ?>)</h2>
      <div class="tgrid">
        <?php if(!$male): ?><div class="empty">No male teachers.</div><?php endif; ?>
        <?php foreach($male as $t): ?>
          <div class="card">
            <div class="idtag">ID <?php echo htmlspecialchars($t['id']); ?></div>
            <strong><?php echo htmlspecialchars($t['full_name']); ?></strong><br>
            Email: <?php echo htmlspecialchars($t['email']); ?><br>
            Grade: <?php echo htmlspecialchars($t['grade_level'] ?? ''); ?> | Strand: <?php echo htmlspecialchars($t['strand'] ?? ''); ?><br>
            Section: <?php echo htmlspecialchars($t['section_block'] ?? ''); ?><br>
            <?php if($hasFaculty): ?>Faculty: <?php echo htmlspecialchars($t['faculty'] ?? ''); ?><br><?php endif; ?>
            Created: <?php echo htmlspecialchars($t['created_at'] ?? ''); ?>
            <button class="edit-btn" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($t)); ?>)">Edit</button>
            <form method="post" style="display:inline;" onsubmit="return confirm('Delete this teacher?');">
              <input type="hidden" name="action" value="delete_teacher">
              <input type="hidden" name="teacher_id" value="<?php echo $t['id']; ?>">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
              <button class="del-btn" type="submit">Delete</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="col">
      <h2>👩 Female (<?php echo count($female); ?>)</h2>
      <div class="tgrid">
        <?php if(!$female): ?><div class="empty">No female teachers.</div><?php endif; ?>
        <?php foreach($female as $t): ?>
          <div class="card">
            <div class="idtag">ID <?php echo htmlspecialchars($t['id']); ?></div>
            <strong><?php echo htmlspecialchars($t['full_name']); ?></strong><br>
            Email: <?php echo htmlspecialchars($t['email']); ?><br>
            Grade: <?php echo htmlspecialchars($t['grade_level'] ?? ''); ?> | Strand: <?php echo htmlspecialchars($t['strand'] ?? ''); ?><br>
            Section: <?php echo htmlspecialchars($t['section_block'] ?? ''); ?><br>
            <?php if($hasFaculty): ?>Faculty: <?php echo htmlspecialchars($t['faculty'] ?? ''); ?><br><?php endif; ?>
            Created: <?php echo htmlspecialchars($t['created_at'] ?? ''); ?>
            <button class="edit-btn" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($t)); ?>)">Edit</button>
            <form method="post" style="display:inline;" onsubmit="return confirm('Delete this teacher?');">
              <input type="hidden" name="action" value="delete_teacher">
              <input type="hidden" name="teacher_id" value="<?php echo $t['id']; ?>">
              <button class="del-btn" type="submit">Delete</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
    <?php if($other): ?>
    <div class="col">
      <h2>📋 All Teachers (<?php echo count($other); ?>)</h2>
      <div class="tgrid">
        <?php foreach($other as $t): ?>
          <div class="card">
            <div class="idtag">ID <?php echo htmlspecialchars($t['id']); ?></div>
            <strong><?php echo htmlspecialchars($t['full_name']); ?></strong><br>
            Email: <?php echo htmlspecialchars($t['email']); ?><br>
            Grade: <?php echo htmlspecialchars($t['grade_level'] ?? ''); ?> | Strand: <?php echo htmlspecialchars($t['strand'] ?? ''); ?><br>
            Section: <?php echo htmlspecialchars($t['section_block'] ?? ''); ?><br>
            <?php if($hasGender): ?>Gender: <?php echo htmlspecialchars($t['gender'] ?? ''); ?><br><?php endif; ?>
            <?php if($hasFaculty): ?>Faculty: <?php echo htmlspecialchars($t['faculty'] ?? ''); ?><br><?php endif; ?>
            Created: <?php echo htmlspecialchars($t['created_at'] ?? ''); ?>
            <button class="edit-btn" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($t)); ?>)">Edit</button>
            <form method="post" style="display:inline;" onsubmit="return confirm('Delete this teacher?');">
              <input type="hidden" name="action" value="delete_teacher">
              <input type="hidden" name="teacher_id" value="<?php echo $t['id']; ?>">
              <button class="del-btn" type="submit">Delete</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
  <p style="color:#fff;margin-top:30px;font-size:.7rem;opacity:.8;">Directory generated at <?php echo gmdate('Y-m-d H:i:s'); ?> UTC • Total Teachers: <?php echo count($teachers); ?> • Schema columns: <?php echo htmlspecialchars(implode(',',$columns)); ?></p>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
  <div class="modal-content">
    <span class="close-modal" onclick="closeEditModal()">&times;</span>
    <h3>Edit Teacher</h3>
    <form method="post" action="">
      <input type="hidden" name="action" value="update_teacher">
      <input type="hidden" name="teacher_id" id="edit_id">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
      <div class="grid">
        <div class="fg"><label for="edit_full_name">Full Name *</label><input type="text" id="edit_full_name" name="full_name" required /></div>
        <div class="fg"><label for="edit_email">Email *</label><input type="email" id="edit_email" name="email" required /></div>
        <div class="fg"><label for="edit_password">New Password (leave blank to keep)</label><input type="text" id="edit_password" name="password" placeholder="Leave blank to keep current" /></div>
        <?php if($hasGender): ?>
          <div class="fg"><label for="edit_gender">Gender</label>
            <select id="edit_gender" name="gender">
              <option value="">--</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
          </div>
        <?php endif; ?>
        <div class="fg"><label for="edit_grade_level">Grade Level</label><input type="text" id="edit_grade_level" name="grade_level" /></div>
        <div class="fg"><label for="edit_strand">Strand</label><input type="text" id="edit_strand" name="strand" /></div>
        <div class="fg"><label for="edit_section_block">Section Block</label><input type="text" id="edit_section_block" name="section_block" /></div>
        <?php if($hasFaculty): ?><div class="fg"><label for="edit_faculty">Faculty</label><input type="text" id="edit_faculty" name="faculty" /></div><?php endif; ?>
      </div>
      <div class="actions">
        <button class="btn" type="submit">Update Teacher</button>
        <button class="btn" type="button" style="background:#6c757d" onclick="closeEditModal()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditModal(teacher){
  document.getElementById('edit_id').value = teacher.id;
  document.getElementById('edit_full_name').value = teacher.full_name;
  document.getElementById('edit_email').value = teacher.email;
  document.getElementById('edit_password').value = '';
  <?php if($hasGender): ?>
  document.getElementById('edit_gender').value = teacher.gender || '';
  <?php endif; ?>
  document.getElementById('edit_grade_level').value = teacher.grade_level || '';
  document.getElementById('edit_strand').value = teacher.strand || '';
  document.getElementById('edit_section_block').value = teacher.section_block || '';
  <?php if($hasFaculty): ?>
  document.getElementById('edit_faculty').value = teacher.faculty || '';
  <?php endif; ?>
  document.getElementById('editModal').classList.add('active');
}
function closeEditModal(){
  document.getElementById('editModal').classList.remove('active');
}
</script>
</body>
</html>
