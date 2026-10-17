<?php
require __DIR__ . '/includes/bootstrap.php';
$all = locations();
echo json_encode($all['sforest']['exits'], JSON_UNESCAPED_UNICODE), "\n";
