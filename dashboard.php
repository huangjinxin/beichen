<?php
require 'auth.php';
require 'db.php';

$editing = false;
$editTransaction = null;

// Handle add/update/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $stmt = $db->prepare('DELETE FROM transactions WHERE id = ?');
        $stmt->execute([$_POST['delete']]);
    } else {
        $date = $_POST['date'];
        $desc = $_POST['description'];
        $amount = $_POST['amount'];
        $type = $_POST['type'];
        if (!empty($_POST['id'])) {
            $stmt = $db->prepare('UPDATE transactions SET date=?, description=?, amount=?, type=? WHERE id=?');
            $stmt->execute([$date, $desc, $amount, $type, $_POST['id']]);
        } else {
            $stmt = $db->prepare('INSERT INTO transactions (date, description, amount, type) VALUES (?, ?, ?, ?)');
            $stmt->execute([$date, $desc, $amount, $type]);
        }
    }
}

if (isset($_GET['edit'])) {
    $stmt = $db->prepare('SELECT * FROM transactions WHERE id=?');
    $stmt->execute([$_GET['edit']]);
    $editTransaction = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($editTransaction) {
        $editing = true;
    }
}

$search = $_GET['search'] ?? '';
$typeFilter = $_GET['filter_type'] ?? '';

$query = 'SELECT * FROM transactions WHERE 1';
$params = [];
if ($search !== '') {
    $query .= ' AND description LIKE :search';
    $params[':search'] = '%' . $search . '%';
}
if ($typeFilter === 'income' || $typeFilter === 'expense') {
    $query .= ' AND type=:type';
    $params[':type'] = $typeFilter;
}
$query .= ' ORDER BY date DESC, id DESC';
$stmt = $db->prepare($query);
$stmt->execute($params);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalIncome = $db->query("SELECT IFNULL(SUM(amount),0) FROM transactions WHERE type='income'")->fetchColumn();
$totalExpense = $db->query("SELECT IFNULL(SUM(amount),0) FROM transactions WHERE type='expense'")->fetchColumn();
$balance = $totalIncome - $totalExpense;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>幼儿园财务管理系统</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">财务管理</a>
        <div class="d-flex">
            <a href="logout.php" class="btn btn-outline-secondary">退出</a>
        </div>
    </div>
</nav>
<div class="container">
    <div class="row mb-3">
        <div class="col-md-8">
            <form class="row row-cols-lg-auto g-2" method="get">
                <div class="col-12">
                    <input type="text" class="form-control" name="search" placeholder="搜索描述" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-12">
                    <select name="filter_type" class="form-select">
                        <option value="">全部类型</option>
                        <option value="income" <?php if($typeFilter==='income') echo 'selected'; ?>>收入</option>
                        <option value="expense" <?php if($typeFilter==='expense') echo 'selected'; ?>>支出</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">筛选</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">总收入</h5>
                    <p class="card-text fs-4"><?php echo number_format($totalIncome,2); ?> 元</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title">总支出</h5>
                    <p class="card-text fs-4"><?php echo number_format($totalExpense,2); ?> 元</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-bg-info mb-3">
                <div class="card-body">
                    <h5 class="card-title">余额</h5>
                    <p class="card-text fs-4"><?php echo number_format($balance,2); ?> 元</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <h2><?php echo $editing ? '编辑记录' : '新增记录'; ?></h2>
            <form method="post" class="row g-3">
                <input type="hidden" name="id" value="<?php echo $editTransaction['id'] ?? ''; ?>">
                <div class="col-md-6">
                    <label for="date" class="form-label">日期</label>
                    <input type="date" class="form-control" id="date" name="date" value="<?php echo $editTransaction['date'] ?? ''; ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="type" class="form-label">类型</label>
                    <select id="type" name="type" class="form-select">
                        <option value="income" <?php if(($editTransaction['type'] ?? '')==='income') echo 'selected'; ?>>收入</option>
                        <option value="expense" <?php if(($editTransaction['type'] ?? '')==='expense') echo 'selected'; ?>>支出</option>
                    </select>
                </div>
                <div class="col-12">
                    <label for="description" class="form-label">描述</label>
                    <input type="text" class="form-control" id="description" name="description" value="<?php echo htmlspecialchars($editTransaction['description'] ?? ''); ?>" required>
                </div>
                <div class="col-12">
                    <label for="amount" class="form-label">金额</label>
                    <input type="number" step="0.01" class="form-control" id="amount" name="amount" value="<?php echo $editTransaction['amount'] ?? ''; ?>" required>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-success"><?php echo $editing ? '更新' : '新增'; ?></button>
                    <?php if($editing): ?>
                        <a href="dashboard.php" class="btn btn-secondary">取消</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <div class="col-md-6">
            <h2>记录列表</h2>
            <div class="table-responsive" style="max-height:400px; overflow:auto;">
                <table class="table table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>日期</th>
                            <th>描述</th>
                            <th>金额</th>
                            <th>类型</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($t['date']); ?></td>
                            <td><?php echo htmlspecialchars($t['description']); ?></td>
                            <td><?php echo number_format($t['amount'],2); ?></td>
                            <td><?php echo $t['type'] === 'income' ? '收入' : '支出'; ?></td>
                            <td>
                                <a href="dashboard.php?edit=<?php echo $t['id']; ?>" class="btn btn-sm btn-primary">编辑</a>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="delete" value="<?php echo $t['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('确认删除?');">删除</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
