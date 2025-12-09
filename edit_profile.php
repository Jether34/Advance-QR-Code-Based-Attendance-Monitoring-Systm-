<?php
// edit_profile.php - student profile editing
session_start();
require_once __DIR__ . '/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit;
}
$pdo = get_db();
$uid = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
$stmt->execute([':id' => $uid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) { echo 'User not found'; exit; }

$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $block = $_POST['block_section'] ?? '';
    $password = $_POST['password'] ?? '';
    if ($full_name && $email && $gender && $block) {
        $params = [
            ':full_name' => $full_name,
            ':email' => $email,
            ':gender' => $gender,
            ':block' => $block,
            ':id' => $uid
        ];
        $sql = 'UPDATE users SET full_name = :full_name, email = :email, gender = :gender, block_section = :block';
        if ($password) {
            $sql .= ', password = :password';
            $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
        }
        $sql .= ' WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $success = 'Profile updated!';
        // Refresh user data
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute([':id' => $uid]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $error = 'All fields except password are required.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navbar">
        <a href="student_dashboard.php">Dashboard</a>
        <a href="chatroom.php">Group Chatroom</a>
        <a href="edit_profile.php">Edit Profile</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="container" style="max-width:500px">
    <h1>Edit Profile</h1>
    <?php if($success): ?><div style="color:green"><?php echo $success; ?></div><?php endif; ?>
    <?php if($error): ?><div style="color:red"><?php echo $error; ?></div><?php endif; ?>
    <form method="post">
        <label>Full Name</label>
        <input name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required style="width:100%" />
        <label>Email</label>
        <input name="email" type="email" value="<?php echo htmlspecialchars($user['email']); ?>" required style="width:100%" />
        <label>Gender</label>
        <select name="gender" required>
            <option value="">Select gender</option>
            <option value="Male" <?php if($user['gender']==='Male') echo 'selected'; ?>>Male</option>
            <option value="Female" <?php if($user['gender']==='Female') echo 'selected'; ?>>Female</option>
        </select>
        <label>Block/Section</label>
        <input name="block_section" type="number" min="1" max="20" value="<?php echo htmlspecialchars($user['block_section']); ?>" required />
        <label>New Password (leave blank to keep current)</label>
        <input name="password" type="password" style="width:100%" />
        <p><button type="submit">Update Profile</button></p>
    </form>
    </div>
</body>
</html>
