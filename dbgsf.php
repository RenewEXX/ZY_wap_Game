<?php
// 6) 70-100新图：先看sforest块精确文本
require __DIR__ . '/includes/bootstrap.php';
$all = locations();
echo json_encode($all['sforest'], JSON_UNESCAPED_UNICODE), "\n";
echo json_encode($all['manor'], JSON_UNESCAPED_UNICODE), "\n";
