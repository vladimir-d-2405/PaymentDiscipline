<?php
declare (strict_types = 1);
require_once 'lib/reconcile_invoices.php';
$plan = array (
    '16.04.2022' => 1300000,
    '16.05.2022' => 300000,
    '16.06.2022' => 300000
);
$fact = array (
    '15.04.2022' => 1300000,
    '25.04.2022' => 300000,
    '15.06.2022' => 1500000
);
print_r (reconcile_invoices($plan, $fact));
?>