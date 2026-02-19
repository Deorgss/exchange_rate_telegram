<?php
$config = require __DIR__ . '/config.php';
require __DIR__ . '/exchange.php';

$monitor = new ExchangeMonitor($config);
$monitor->process();

echo "Success: Rates processed at " . date('Y-m-d H:i:s') . "\n";

?>
