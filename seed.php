<?php
require_once __DIR__ . '/db.php';
$pdo = get_db();

$users = [
    ['role'=>'student','lrn'=>'20250001','email'=>'juan@example.com','full_name'=>'Juan Dela Cruz','grade'=>'11','strand'=>'STEM','block_section'=>'1','faculty'=>null],
    ['role'=>'student','lrn'=>'20250002','email'=>'maria@example.com','full_name'=>'Maria Clara','grade'=>'11','strand'=>'ABM','block_section'=>'2','faculty'=>null],
    ['role'=>'teacher','lrn'=>null,'email'=>'mr.santos@example.com','full_name'=>'Mr. Santos','grade'=>'11','strand'=>'STEM','block_section'=>'1','faculty'=>'STEM'],
];

$stmt = $pdo->prepare('INSERT INTO users (role, code, lrn, email, password, full_name, grade, strand, block_section, faculty) VALUES (:role,:code,:lrn,:email,:password,:full_name,:grade,:strand,:block,:faculty)');

$printed_credentials = [];
foreach($users as $u){
    $code = uniqid('u', true);
    // Generate a random temporary password for seeded accounts to avoid committing plaintext
    try {
        $plain = bin2hex(random_bytes(6)); // 12 hex chars (~48 bits)
    } catch (Exception $e) {
        // Fallback if random_bytes is unavailable
        $plain = 'changeme123';
    }
    $hash = password_hash($plain, PASSWORD_DEFAULT);
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
    $printed_credentials[$u['email']] = $plain;
}

echo "Seeded sample users. Temporary passwords (record these securely):\n";
foreach($printed_credentials as $email => $pw){
    echo " - $email : $pw\n";
}
