<?php
session_start();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    // Simple hard-coded credentials
    if ($username === 'admin' && $password === 'password') {
        $_SESSION['logged_in'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = '用户名或密码错误';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - 幼儿园财务管理系统</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <h1 class="mb-4 text-center">登录</h1>
    <form method="post" class="mx-auto" style="max-width:300px;">
        <div class="mb-3">
            <label for="username" class="form-label">用户名</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">密码</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <button type="submit" class="btn btn-primary w-100">登录</button>
    </form>
</div>
</body>
</html>
