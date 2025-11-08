<?php
require_once __DIR__ . '/db.php';
$pdo = get_db();

$users = [
    ['role'=>'student','lrn'=>'20250001','email'=>'juan@example.com','password'=>'student123','full_name'=>'Juan Dela Cruz','grade'=>'11','strand'=>'STEM','block_section'=>'1','faculty'=>null],
    ['role'=>'student','lrn'=>'20250002','email'=>'maria@example.com','password'=>'student123','full_name'=>'Maria Clara','grade'=>'11','strand'=>'ABM','block_section'=>'2','faculty'=>null],
    ['role'=>'teacher','lrn'=>null,'email'=>'mr.santos@example.com','password'=>'teacher123','full_name'=>'Mr. Santos','grade'=>'11','strand'=>'STEM','block_section'=>'1','faculty'=>'STEM'],
];

$stmt = $pdo->prepare('INSERT INTO users (role, code, lrn, email, password, full_name, grade, strand, block_section, faculty) VALUES (:role,:code,:lrn,:email,:password,:full_name,:grade,:strand,:block,:faculty)');
foreach($users as $u){
    $code = uniqid('u', true);
    $hash = password_hash($u['password'], PASSWORD_DEFAULT);
    $stmt->execute([
        ':role'=>$u['role'],
        ':code'=>$code,
        ':lrn'=>$u['lrn'],
        ':email'=>$u['email'],
        ':password'=>$hash,
        ':full_name'=>$u['full_name'],
        ':grade'=>$u['grade'],
        ':strand'=>$u['strand'],
        ':block'=>$u['block_section'],
        ':faculty'=>$u['faculty']
    ]);
}

echo "Seeded sample users.\n";
