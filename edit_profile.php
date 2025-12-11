<?php
// edit_profile.php - student profile editing
require_once __DIR__ . '/bootstrap.php';
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Palawan National School</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Manrope', 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            background:
                radial-gradient(circle at 18% 18%, rgba(34, 211, 238, 0.12), transparent 38%),
                radial-gradient(circle at 78% -8%, rgba(34, 197, 94, 0.1), transparent 42%),
                linear-gradient(140deg, #0c1426 0%, #102035 50%, #0c2841 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background: linear-gradient(120deg, #0ea5e9 0%, #0d95d7 42%, #0fb38f 100%);
            padding: 0;
            box-shadow: 0 14px 36px rgba(6, 182, 212, 0.28);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            border-radius: 0 0 16px 16px;
        }
        .navbar a {
            color: #fff;
            padding: 18px 26px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.25s ease;
            border-bottom: 3px solid transparent;
        }
        .navbar a:hover {
            background: rgba(255,255,255,0.12);
            border-bottom-color: #e0f2fe;
        }
        .container {
            max-width: 600px;
            margin: 32px auto;
            padding: 0 24px;
        }
        .profile-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 24px 70px rgba(8, 47, 73, 0.18);
            border: 1px solid #e2e8f0;
        }
        h1 {
            color: #0f172a;
            font-size: 2em;
            font-weight: 800;
            margin: 0 0 24px 0;
            padding-bottom: 16px;
            border-bottom: 3px solid #e0f2fe;
        }
        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #0f172a;
            font-weight: 700;
            letter-spacing: 0.01em;
            font-size: 0.95em;
        }
        input, select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d7e0eb;
            border-radius: 10px;
            font-size: 1em;
            font-family: inherit;
            transition: all 0.2s ease;
            background: #f7f9fc;
            box-sizing: border-box;
        }
        input:focus, select:focus {
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.16);
            background: #ffffff;
            outline: none;
        }
        button[type="submit"] {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 50%, #0ea5e9 100%);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 1.05em;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 10px;
        }
        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 32px rgba(14, 165, 233, 0.28);
        }
        @media (max-width: 768px) {
            .container {
                padding: 0 16px;
                margin: 20px auto;
            }
            .profile-card {
                padding: 28px 24px;
            }
            h1 {
                font-size: 1.6em;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="student_dashboard.php">📊 Dashboard</a>
        <a href="chatroom.php">💬 Chatroom</a>
        <a href="edit_profile.php">✏️ Edit Profile</a>
        <a href="logout.php">🚪 Logout</a>
    </div>
    <div class="container">
        <div class="profile-card">
            <h1>✏️ Edit Profile</h1>
            <?php if($success): ?>
                <div class="alert alert-success">✅ <?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="alert alert-error">❌ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="post">
                <div>
                    <label>Full Name</label>
                    <input name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required />
                </div>
                <div>
                    <label>Email Address</label>
                    <input name="email" type="email" value="<?php echo htmlspecialchars($user['email']); ?>" required />
                </div>
                <div>
                    <label>Gender</label>
                    <select name="gender" required>
                        <option value="">Select gender</option>
                        <option value="Male" <?php if($user['gender']==='Male') echo 'selected'; ?>>Male</option>
                        <option value="Female" <?php if($user['gender']==='Female') echo 'selected'; ?>>Female</option>
                    </select>
                </div>
                <div>
                    <label>Block/Section</label>
                    <input name="block_section" type="number" min="1" max="20" value="<?php echo htmlspecialchars($user['block_section']); ?>" required />
                </div>
                <div>
                    <label>New Password <span style="font-weight: 400; color: #64748b;">(leave blank to keep current)</span></label>
                    <input name="password" type="password" placeholder="Enter new password (optional)" />
                </div>
                <button type="submit">💾 Update Profile</button>
            </form>
        </div>
    </div>
</body>
</html>
