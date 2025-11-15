<?php
// developer_students.php - Developer-only comprehensive student directory with filters
session_start();
require_once __DIR__ . '/db.php';

if(!isset($_SESSION['developer_id'])){ header('Location: developer_login.php'); exit; }

$pdo = get_db();
$errors = [];
$success = '';

// Handle UPDATE action
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action']) && $_POST['action']==='update_student'){
    $sid = (int)($_POST['student_id'] ?? 0);
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $grade = trim($_POST['grade_level'] ?? '');
    $strand = trim($_POST['strand'] ?? '');
    $section = trim($_POST['section_block'] ?? '');
    $lrn = trim($_POST['lrn'] ?? '');
    
    foreach(['full_name','email'] as $req){ if(empty($$req)) $errors[] = "$req is required"; }
    if($email && !filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Invalid email format';
    if(!$errors){
        $updates = [];$params=[':id'=>$sid];
        $updates[]='full_name = :full_name'; $params[':full_name']=$full_name;
        $updates[]='email = :email'; $params[':email']=$email;
        if($password!==''){ $updates[]='password = :password'; $params[':password']=password_hash($password,PASSWORD_DEFAULT); }
        if($gender!==''){ $updates[]='gender = :gender'; $params[':gender']=$gender; }
        if($grade!==''){ $updates[]='grade_level = :grade'; $params[':grade']=$grade; }
        if($strand!==''){ $updates[]='strand = :strand'; $params[':strand']=$strand; }
        if($section!==''){ $updates[]='section_block = :section'; $params[':section']=$section; }
        if($lrn!==''){ $updates[]='lrn = :lrn'; $params[':lrn']=$lrn; }
        $sql = 'UPDATE students SET '.implode(', ',$updates).' WHERE id = :id';
        try { $stmt=$pdo->prepare($sql); $stmt->execute($params); $success='Student updated successfully'; }
        catch(Exception $e){ $errors[]='Update failed: '.$e->getMessage(); }
    }
}

// Handle DELETE action
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action']) && $_POST['action']==='delete_student'){
    $sid = (int)($_POST['student_id'] ?? 0);
    try { $stmt=$pdo->prepare('DELETE FROM students WHERE id = :id'); $stmt->execute([':id'=>$sid]); $success='Student deleted successfully'; }
    catch(Exception $e){ $errors[]='Delete failed: '.$e->getMessage(); }
}

// Collect distinct values for filters
$distinct = [
  'grades' => [], 'strands' => [], 'sections' => []
];
foreach(['grade_level'=>'grades','strand'=>'strands','section_block'=>'sections'] as $col=>$key){
  $stmt = $pdo->query("SELECT DISTINCT $col AS v FROM students WHERE $col IS NOT NULL AND $col<>'' ORDER BY v");
  $distinct[$key] = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
}

// Read filters
$grade = trim($_GET['grade'] ?? '');
$strand = trim($_GET['strand'] ?? '');
$section = trim($_GET['section'] ?? '');
$search = trim($_GET['search'] ?? '');

// Build dynamic WHERE
$where = [];
$params = [];
if($grade !== ''){ $where[] = 'grade_level = :grade'; $params[':grade'] = $grade; }
if($strand !== ''){ $where[] = 'strand = :strand'; $params[':strand'] = $strand; }
if($section !== ''){ $where[] = 'section_block = :section'; $params[':section'] = $section; }
if($search !== ''){ $where[] = '(full_name LIKE :search OR student_id LIKE :search OR lrn LIKE :search)'; $params[':search'] = "%$search%"; }

$sql = 'SELECT * FROM students';
if($where){ $sql .= ' WHERE ' . implode(' AND ', $where); }
$sql .= ' ORDER BY grade_level, strand, section_block, full_name';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Separate male / female
$male = []; $female = []; $other = [];
foreach($students as $stu){
  $g = strtolower($stu['gender'] ?? '');
  if($g === 'male') $male[] = $stu; elseif($g === 'female') $female[] = $stu; else $other[] = $stu;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Developer Student Directory</title>
<link rel="stylesheet" href="style.css" />
<style>
  *{margin:0;padding:0;box-sizing:border-box;}
  body{font-family:'Segoe UI',-apple-system,BlinkMacSystemFont,Arial,sans-serif;background:linear-gradient(135deg,#1e5128 0%,#2d6a4f 100%);min-height:100vh;padding:24px;color:#2c3e50;}
  .wrap{max-width:1700px;margin:0 auto;}
  .head{background:linear-gradient(135deg,#ffffff 0%,#f8fffe 100%);border-radius:20px;padding:28px 40px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 10px 40px rgba(0,0,0,.12),0 2px 8px rgba(0,0,0,.06);margin-bottom:28px;border:1px solid rgba(255,255,255,0.8);}
  .head h1{margin:0;font-size:2em;color:#1e5128;font-weight:700;display:flex;align-items:center;gap:14px;letter-spacing:-0.5px;}
  .filters{background:linear-gradient(135deg,#ffffff 0%,#f8fffe 100%);border-radius:20px;padding:24px 32px;margin-bottom:24px;box-shadow:0 8px 28px rgba(0,0,0,.1);display:flex;flex-wrap:wrap;gap:16px;align-items:flex-end;border:1px solid rgba(45,106,79,0.08);}
  .filters label{font-size:.72rem;font-weight:700;text-transform:uppercase;color:#1e5128;display:block;margin-bottom:6px;letter-spacing:.6px;}
  .filters select,.filters input[type=text]{padding:11px 14px;border:2px solid #d8f3dc;border-radius:10px;background:#f6fff7;min-width:160px;font-size:.9em;transition:all 0.3s;}
  .filters select:focus,.filters input[type=text]:focus{outline:none;border-color:#2d6a4f;box-shadow:0 0 0 4px rgba(45,106,79,.12);background:#fff;}
  .filters button,.filters .btn{padding:11px 24px;border:none;border-radius:10px;background:linear-gradient(135deg,#2d6a4f 0%,#1e5128 100%);color:#fff;font-weight:600;cursor:pointer;font-size:.9em;box-shadow:0 4px 12px rgba(45,106,79,0.3);transition:all 0.3s;text-decoration:none;display:inline-block;}
  .filters button:hover,.filters .btn:hover{transform:translateY(-2px);box-shadow:0 6px 18px rgba(45,106,79,0.5);}
  .filters .btn-reset{background:linear-gradient(135deg,#95a5a6 0%,#7f8c8d 100%);box-shadow:0 4px 12px rgba(127,140,141,0.3);}
  .filters .btn-reset:hover{box-shadow:0 6px 18px rgba(127,140,141,0.5);}
  .back-link{color:#1e5128;text-decoration:none;font-weight:600;padding:10px 20px;background:#d8f3dc;border-radius:10px;transition:all 0.3s;}
  .back-link:hover{background:#b7e4c7;transform:translateY(-2px);}
  .msg.success{background:linear-gradient(135deg,#d4edda 0%,#c3e6cb 100%);color:#155724;padding:14px 20px;border-radius:12px;margin-bottom:18px;font-size:.9em;border-left:4px solid #28a745;font-weight:600;}
  .msg.error{background:linear-gradient(135deg,#f8d7da 0%,#f5c6cb 100%);color:#721c24;padding:14px 20px;border-radius:12px;margin-bottom:12px;font-size:.88em;border-left:4px solid #dc3545;font-weight:600;}
  .section{background:linear-gradient(135deg,#ffffff 0%,#f8fffe 100%);border-radius:20px;padding:28px 32px;box-shadow:0 10px 40px rgba(0,0,0,.08);margin-bottom:24px;border:1px solid rgba(45,106,79,0.08);}
  .section h2{margin:0 0 20px;font-size:1.35em;color:#1e5128;font-weight:700;padding-bottom:14px;border-bottom:3px solid #d8f3dc;display:flex;align-items:center;gap:10px;}
  .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:18px;}
  .card{background:linear-gradient(135deg,#f6fff7 0%,#fff 100%);border:2px solid #d8f3dc;border-radius:14px;padding:18px 20px;font-size:.85em;display:flex;flex-direction:column;gap:6px;position:relative;transition:all 0.3s;box-shadow:0 2px 8px rgba(0,0,0,0.04);}
  .card:hover{transform:translateY(-4px);box-shadow:0 8px 20px rgba(45,106,79,0.15);border-color:#2d6a4f;}
  .card strong{color:#1e5128;font-weight:700;}
  .card-actions{display:flex;gap:8px;margin-top:10px;}
  .btn-edit,.btn-del{padding:8px 16px;border:none;border-radius:8px;font-size:.8em;cursor:pointer;font-weight:600;transition:all 0.3s;box-shadow:0 2px 6px rgba(0,0,0,0.1);flex:1;}
  .btn-edit{background:linear-gradient(135deg,#3498db 0%,#2980b9 100%);color:#fff;}
  .btn-edit:hover{transform:translateY(-2px);box-shadow:0 4px 10px rgba(52,152,219,0.4);}
  .btn-del{background:linear-gradient(135deg,#e74c3c 0%,#c0392b 100%);color:#fff;}
  .btn-del:hover{transform:translateY(-2px);box-shadow:0 4px 10px rgba(231,76,60,0.4);}
  .modal{display:none;position:fixed;z-index:9999;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,.65);justify-content:center;align-items:center;}
  .modal.active{display:flex;}
  .modal-content{background:linear-gradient(135deg,#ffffff 0%,#f8fffe 100%);border-radius:20px;padding:32px 40px;max-width:650px;width:92%;max-height:92vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,0.3);}
  .modal-content h3{margin:0 0 24px;color:#1e5128;font-size:1.5em;font-weight:700;padding-bottom:14px;border-bottom:3px solid #d8f3dc;}
  .modal-content label{display:block;margin-bottom:6px;color:#1e5128;font-weight:700;font-size:.8em;text-transform:uppercase;letter-spacing:.5px;}
  .modal-content input,.modal-content select{width:100%;padding:12px 14px;margin-bottom:14px;border:2px solid #d8f3dc;border-radius:10px;background:#f6fff7;font-size:.9em;transition:all 0.3s;}
  .modal-content input:focus,.modal-content select:focus{outline:none;border-color:#2d6a4f;box-shadow:0 0 0 4px rgba(45,106,79,.12);background:#fff;}
  .modal-content button{padding:13px 26px;border:none;border-radius:11px;background:linear-gradient(135deg,#2d6a4f 0%,#1e5128 100%);color:#fff;font-weight:600;cursor:pointer;font-size:.95em;box-shadow:0 4px 12px rgba(45,106,79,0.3);transition:all 0.3s;margin-right:10px;}
  .modal-content button:hover{transform:translateY(-2px);box-shadow:0 6px 18px rgba(45,106,79,0.5);}
  .modal-content .cancel{background:linear-gradient(135deg,#95a5a6 0%,#7f8c8d 100%);}
  .modal-content .cancel:hover{box-shadow:0 6px 18px rgba(127,140,141,0.5);}
  .close-modal{float:right;font-size:2em;font-weight:700;color:#aaa;cursor:pointer;line-height:1;transition:all 0.2s;}
  .close-modal:hover{color:#e74c3c;transform:rotate(90deg);}
  .empty{color:#7f8c8d;font-style:italic;padding:20px;text-align:center;}
  @media(max-width:900px){.grid{grid-template-columns:repeat(auto-fill,minmax(240px,1fr));}}
  .filters select:focus,.filters input[type=text]:focus{outline:none;border-color:#2d6a4f;box-shadow:0 0 0 3px rgba(45,106,79,.15);}
  .filters .btn{padding:12px 24px;border:none;border-radius:10px;background:#2d6a4f;color:#fff;font-weight:600;cursor:pointer;font-size:.95em;}
  .filters .btn:hover{background:#1e5128;}
  .columns{display:grid;grid-template-columns:repeat(auto-fit,minmax(370px,1fr));gap:22px;}
  .col{background:#fff;border-radius:16px;padding:20px 22px;box-shadow:0 8px 24px rgba(0,0,0,.1);display:flex;flex-direction:column;}
  .col h2{margin:0 0 14px;font-size:1.25em;color:#1e5128;display:flex;align-items:center;gap:8px;}
  .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:16px;}
  .card{background:#f8fff9;border:1px solid #d8f3dc;border-radius:12px;padding:12px 14px;font-size:.8em;display:flex;flex-direction:column;gap:4px;position:relative;}
  .card strong{font-size:.9em;color:#1e5128;}
  .idtag{position:absolute;top:6px;right:10px;font-size:.65rem;background:#2d6a4f;color:#fff;padding:3px 8px;border-radius:6px;letter-spacing:.5px;}
  .edit-btn,.del-btn{padding:6px 12px;border:none;border-radius:6px;font-size:.75em;cursor:pointer;font-weight:600;margin-top:8px;}
  .edit-btn{background:#3498db;color:#fff;}
  .edit-btn:hover{background:#2980b9;}
  .del-btn{background:#e74c3c;color:#fff;}
  .del-btn:hover{background:#c0392b;}
  .modal{display:none;position:fixed;z-index:9999;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,.6);justify-content:center;align-items:center;}
  .modal.active{display:flex;}
  .modal-content{background:#fff;border-radius:16px;padding:28px 32px;max-width:600px;width:90%;max-height:90vh;overflow-y:auto;}
  .modal-content h3{margin:0 0 18px;color:#1e5128;font-size:1.3em;}
  .close-modal{float:right;font-size:1.8em;font-weight:700;color:#aaa;cursor:pointer;line-height:1;}
  .close-modal:hover{color:#000;}
  .msg{padding:12px 18px;border-radius:10px;margin-bottom:12px;font-size:.85em;}
  .msg.success{background:#d4edda;color:#155724;}
  .msg.error{background:#f8d7da;color:#721c24;}
  .fg{margin-bottom:14px;}
  .fg label{font-size:.65rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#1e5128;display:block;margin-bottom:4px;}
  .fg input,.fg select{width:100%;padding:10px 12px;border:2px solid #d8f3dc;border-radius:10px;background:#f6fff7;font-size:.85em;}
  .fg input:focus,.fg select:focus{outline:none;border-color:#2d6a4f;box-shadow:0 0 0 3px rgba(45,106,79,.15);}
  .actions{margin-top:18px;display:flex;gap:14px;}
  .btn{padding:12px 22px;border:none;border-radius:10px;background:#2d6a4f;color:#fff;font-weight:600;cursor:pointer;font-size:.9em;text-decoration:none;}
  .btn:hover{background:#1e5128;}
  .empty{color:#555;font-style:italic;}
  .back-link{color:#fff;text-decoration:none;font-weight:600;}
  .back-link:hover{text-decoration:underline;}
  @media (max-width:800px){.columns{grid-template-columns:1fr;} .grid{grid-template-columns:repeat(auto-fill,minmax(180px,1fr));}}
</style>
</head>
<body>
<div class="wrap">
  <div class="head">
    <h1>👤 Student Directory</h1>
    <div><a class="back-link" href="developer_dashboard.php">← Back to Dashboard</a></div>
  </div>
  <?php if($success): ?><div class="msg success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
  <?php foreach($errors as $er): ?><div class="msg error"><?php echo htmlspecialchars($er); ?></div><?php endforeach; ?>
  <form class="filters" method="get" action="">
    <div>
      <label for="grade">Grade</label>
      <select id="grade" name="grade">
        <option value="">All</option>
        <?php foreach($distinct['grades'] as $g): ?>
          <option value="<?php echo htmlspecialchars($g); ?>" <?php if($g===$grade) echo 'selected'; ?>><?php echo htmlspecialchars($g); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label for="strand">Strand</label>
      <select id="strand" name="strand">
        <option value="">All</option>
        <?php foreach($distinct['strands'] as $s): ?>
          <option value="<?php echo htmlspecialchars($s); ?>" <?php if($s===$strand) echo 'selected'; ?>><?php echo htmlspecialchars($s); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label for="section">Block/Section</label>
      <select id="section" name="section">
        <option value="">All</option>
        <?php foreach($distinct['sections'] as $sec): ?>
          <option value="<?php echo htmlspecialchars($sec); ?>" <?php if($sec===$section) echo 'selected'; ?>><?php echo htmlspecialchars($sec); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label for="search">Search</label>
      <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Name / ID / LRN" />
    </div>
    <div>
      <button class="btn" type="submit">Filter</button>
    </div>
    <div>
      <a class="btn btn-reset" href="developer_students.php">Reset</a>
    </div>
  </form>
  <div class="columns">
    <div class="col">
      <h2>👦 Male (<?php echo count($male); ?>)</h2>
      <div class="grid">
        <?php if(!$male): ?><div class="empty">No male students found.</div><?php endif; ?>
        <?php foreach($male as $stu): ?>
          <div class="card">
            <div class="idtag"><?php echo htmlspecialchars($stu['student_id']); ?></div>
            <strong><?php echo htmlspecialchars($stu['full_name']); ?></strong><br>
            Grade: <?php echo htmlspecialchars($stu['grade_level']); ?> | Strand: <?php echo htmlspecialchars($stu['strand']); ?><br>
            Section: <?php echo htmlspecialchars($stu['section_block']); ?><br>
            LRN: <?php echo htmlspecialchars($stu['lrn'] ?? ''); ?><br>
            Email: <?php echo htmlspecialchars($stu['email']); ?><br>
            Created: <?php echo htmlspecialchars($stu['created_at'] ?? ''); ?>
            <button class="edit-btn" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($stu)); ?>)">Edit</button>
            <form method="post" style="display:inline;" onsubmit="return confirm('Delete this student?');">
              <input type="hidden" name="action" value="delete_student">
              <input type="hidden" name="student_id" value="<?php echo $stu['id']; ?>">
              <button class="del-btn" type="submit">Delete</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="col">
      <h2>👧 Female (<?php echo count($female); ?>)</h2>
      <div class="grid">
        <?php if(!$female): ?><div class="empty">No female students found.</div><?php endif; ?>
        <?php foreach($female as $stu): ?>
          <div class="card">
            <div class="idtag"><?php echo htmlspecialchars($stu['student_id']); ?></div>
            <strong><?php echo htmlspecialchars($stu['full_name']); ?></strong><br>
            Grade: <?php echo htmlspecialchars($stu['grade_level']); ?> | Strand: <?php echo htmlspecialchars($stu['strand']); ?><br>
            Section: <?php echo htmlspecialchars($stu['section_block']); ?><br>
            LRN: <?php echo htmlspecialchars($stu['lrn'] ?? ''); ?><br>
            Email: <?php echo htmlspecialchars($stu['email']); ?><br>
            Created: <?php echo htmlspecialchars($stu['created_at'] ?? ''); ?>
            <button class="edit-btn" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($stu)); ?>)">Edit</button>
            <form method="post" style="display:inline;" onsubmit="return confirm('Delete this student?');">
              <input type="hidden" name="action" value="delete_student">
              <input type="hidden" name="student_id" value="<?php echo $stu['id']; ?>">
              <button class="del-btn" type="submit">Delete</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php if($other): ?>
    <div class="col">
      <h2>⚧ Other / Unspecified (<?php echo count($other); ?>)</h2>
      <div class="grid">
        <?php foreach($other as $stu): ?>
          <div class="card">
            <div class="idtag"><?php echo htmlspecialchars($stu['student_id']); ?></div>
            <strong><?php echo htmlspecialchars($stu['full_name']); ?></strong><br>
            Grade: <?php echo htmlspecialchars($stu['grade_level']); ?> | Strand: <?php echo htmlspecialchars($stu['strand']); ?><br>
            Section: <?php echo htmlspecialchars($stu['section_block']); ?><br>
            Gender: <?php echo htmlspecialchars($stu['gender'] ?? ''); ?><br>
            LRN: <?php echo htmlspecialchars($stu['lrn'] ?? ''); ?><br>
            Email: <?php echo htmlspecialchars($stu['email']); ?><br>
            Created: <?php echo htmlspecialchars($stu['created_at'] ?? ''); ?>
            <button class="edit-btn" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($stu)); ?>)">Edit</button>
            <form method="post" style="display:inline;" onsubmit="return confirm('Delete this student?');">
              <input type="hidden" name="action" value="delete_student">
              <input type="hidden" name="student_id" value="<?php echo $stu['id']; ?>">
              <button class="del-btn" type="submit">Delete</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
  <p style="color:#fff;margin-top:30px;font-size:.75rem;opacity:.8;">Directory generated at <?php echo gmdate('Y-m-d H:i:s'); ?> UTC • Total: <?php echo count($students); ?> students • Filters applied: <?php echo htmlspecialchars(http_build_query(array_filter(['grade'=>$grade,'strand'=>$strand,'section'=>$section,'search'=>$search])) ?: 'none'); ?></p>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
  <div class="modal-content">
    <span class="close-modal" onclick="closeEditModal()">&times;</span>
    <h3>Edit Student</h3>
    <form method="post" action="">
      <input type="hidden" name="action" value="update_student">
      <input type="hidden" name="student_id" id="edit_id">
      <div class="fg"><label for="edit_full_name">Full Name *</label><input type="text" id="edit_full_name" name="full_name" required /></div>
      <div class="fg"><label for="edit_email">Email *</label><input type="email" id="edit_email" name="email" required /></div>
      <div class="fg"><label for="edit_password">New Password (leave blank to keep)</label><input type="text" id="edit_password" name="password" placeholder="Leave blank to keep current" /></div>
      <div class="fg"><label for="edit_gender">Gender</label>
        <select id="edit_gender" name="gender">
          <option value="">--</option>
          <option value="male">Male</option>
          <option value="female">Female</option>
        </select>
      </div>
      <div class="fg"><label for="edit_grade_level">Grade Level</label><input type="text" id="edit_grade_level" name="grade_level" /></div>
      <div class="fg"><label for="edit_strand">Strand</label><input type="text" id="edit_strand" name="strand" /></div>
      <div class="fg"><label for="edit_section_block">Section Block</label><input type="text" id="edit_section_block" name="section_block" /></div>
      <div class="fg"><label for="edit_lrn">LRN</label><input type="text" id="edit_lrn" name="lrn" /></div>
      <div class="actions">
        <button class="btn" type="submit">Update Student</button>
        <button class="btn" type="button" style="background:#6c757d" onclick="closeEditModal()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditModal(student){
  document.getElementById('edit_id').value = student.id;
  document.getElementById('edit_full_name').value = student.full_name;
  document.getElementById('edit_email').value = student.email;
  document.getElementById('edit_password').value = '';
  document.getElementById('edit_gender').value = student.gender || '';
  document.getElementById('edit_grade_level').value = student.grade_level || '';
  document.getElementById('edit_strand').value = student.strand || '';
  document.getElementById('edit_section_block').value = student.section_block || '';
  document.getElementById('edit_lrn').value = student.lrn || '';
  document.getElementById('editModal').classList.add('active');
}
function closeEditModal(){
  document.getElementById('editModal').classList.remove('active');
}
</script>
</body>
</html>
