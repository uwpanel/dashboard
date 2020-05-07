<?php

if (isset($_SESSION['look'])) {
    $user = $_SESSION['look'];
} else {
    $user = $_SESSION['user'];
}

exec(VESTA_CMD . "v-list-user " . $user . " json", $output, $return_var);
$stats_data = json_decode(implode('', $output), true);
$stats_result = array_reverse($stats_data, true);
unset($output);
