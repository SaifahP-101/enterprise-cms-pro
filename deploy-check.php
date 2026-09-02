<?php
/**
 * Enterprise CMS - Server Compatibility Checker
 * Target Framework: Laravel ^8.75 (PHP ^7.3 | ^8.0)
 */

$requiredPhpVersion = '7.3.0';
$maxPhpVersion = '8.0.99';
$requiredExtensions = [
    'bcmath', 'ctype', 'fileinfo', 'json', 
    'mbstring', 'openssl', 'pdo', 'pdo_mysql', 
    'tokenizer', 'xml'
];

$writablePaths = [
    '../storage',
    '../storage/app/secure_docs',
    '../bootstrap/cache',
];

$results = [];
$passed = true;

// 1. Check PHP Version
$currentPhp = PHP_VERSION;
if (version_compare($currentPhp, $requiredPhpVersion, '>=') && version_compare($currentPhp, '8.1.0', '<')) {
    $results['PHP Version'] = ['status' => 'OK', 'msg' => "PHP {$currentPhp} (Compatible)"];
} else {
    $results['PHP Version'] = ['status' => 'FAIL', 'msg' => "PHP {$currentPhp} (Requires ^7.3 or ^8.0)"];
    $passed = false;
}

// 2. Check Extensions
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        $results["Ext: {$ext}"] = ['status' => 'OK', 'msg' => 'Loaded'];
    } else {
        $results["Ext: {$ext}"] = ['status' => 'FAIL', 'msg' => 'Missing'];
        $passed = false;
    }
}

// 3. Check Directory Permissions
foreach ($writablePaths as $path) {
    $isWritable = is_writable(__DIR__ . '/' . $path);
    $results["Path: {$path}"] = [
        'status' => $isWritable ? 'OK' : 'FAIL',
        'msg' => $isWritable ? 'Writable' : 'Permission Denied (Require 775/777)'
    ];
    if (!$isWritable) $passed = false;
}

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Server Readiness Check - Enterprise CMS</title>
    <style>
        body { font-family: sans-serif; background: #f4f6f9; padding: 20px; }
        .card { max-width: 700px; margin: 0 auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { border-bottom: 2px solid #eee; padding-bottom: 10px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        .badge-ok { background: #28a745; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .badge-fail { background: #dc3545; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .summary { margin-top: 20px; padding: 15px; border-radius: 6px; font-weight: bold; text-align: center; }
        .pass { background: #d4edda; color: #155724; }
        .fail { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<div class="card">
    <h2>🚀 Server Environment Readiness</h2>
    <table>
        <thead>
            <tr><th>Requirement</th><th>Status</th><th>Details</th></tr>
        </thead>
        <tbody>
            <?php foreach ($results as $item => $res): ?>
            <tr>
                <td><strong><?= htmlspecialchars($item) ?></strong></td>
                <td><span class="<?= $res['status'] === 'OK' ? 'badge-ok' : 'badge-fail' ?>"><?= $res['status'] ?></span></td>
                <td><?= htmlspecialchars($res['msg']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="summary <?= $passed ? 'pass' : 'fail' ?>">
        <?= $passed ? '✅ Server พร้อมสำหรับการ Deploy ระบบ Enterprise CMS' : '❌ Server ยังไม่พร้อม โปรดแก้ไขรายการที่แจ้งเตือนด้านบน' ?>
    </div>
</div>
</body>
</html>