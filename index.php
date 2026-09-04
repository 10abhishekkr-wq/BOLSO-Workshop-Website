<?php
declare(strict_types=1);

// Redirect root repository requests to the BOLSO workshop application
$target = 'artifacts/bolso-workshop/';
if (isset($_SERVER['REQUEST_URI']) && str_contains($_SERVER['REQUEST_URI'], 'BOLSO-Workshop-Website')) {
    header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/artifacts/bolso-workshop/');
} else {
    header('Location: ' . $target);
}
exit;
