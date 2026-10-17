<?php
$p = 'includes/wap.php';
$t = file_get_contents($p);
$a = <<<'EOT'
    if (function_exists('war_banner')) {
        try {
            $wb = war_banner(isset($_SESSION['uid']) ? (int) $_SESSION['uid'] : null);
            if ($wb !== '') {
                echo '<div class="warn">' . h($wb) . '</div>';
            }
        } catch (Throwable $e) {
        }
    }
EOT;
if (strpos($t, $a) === false) { echo "MISS\n"; exit(1); }
$b = <<<'EOT'
    if (function_exists('war_banner') && !empty($_SESSION['uid'])) {
        try {
            $wb = war_banner((int) $_SESSION['uid']);
            if ($wb !== '') {
                echo '<div class="warn">' . h($wb) . '</div>';
            }
        } catch (Throwable $e) {
        }
    }
EOT;
$t = str_replace($a, $b, $t);
file_put_contents($p, $t);
echo "OK\n";
