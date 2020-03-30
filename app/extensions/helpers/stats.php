<?php

// Main include
include(APP_PATH . 'libs/inc/main.php');

$user = $_SESSION['user'];

// Data
if ($user == 'admin') {
    if (empty($_GET['user'])) {
        exec(VESTA_CMD . "v-list-users-stats json", $output, $return_var);
        $data = json_decode(implode('', $output), true);
        $data = array_reverse($data, true);
        unset($output);
    } else {
        $v_user = escapeshellarg($_GET['user']);
        exec(VESTA_CMD . "v-list-user-stats $v_user json", $output, $return_var);
        $data = json_decode(implode('', $output), true);
        $data = array_reverse($data, true);
        unset($output);
    }

    exec(VESTA_CMD . "v-list-sys-users json", $output, $return_var);
    $users = json_decode(implode('', $output), true);
    unset($output);
} else {
    exec(VESTA_CMD . "v-list-user-stats $user json", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    $data = array_reverse($data, true);
    unset($output);
}
