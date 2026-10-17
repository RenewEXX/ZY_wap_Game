<?php
$p = __DIR__ . '/includes/game.php';
$lines = file($p);
foreach ($lines as $i => $l) {
    if (strpos($l, "\$b['splash'] = (int) ((\$b['splash'] ?? 0) + \$pd * \$splashRate);") !== false) {
        echo ($i + 1), ': ', trim($l), "\n";
    }
    if (strpos($l, "if (!empty(\$b['frost'])) {") !== false) {
        echo ($i + 1), ' frost: ', trim($l), "\n";
    }
}
