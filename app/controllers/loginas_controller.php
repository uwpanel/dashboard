<?php

class LoginasController extends AuthController
{
    public function index()
    {
        // Main include
        include(APP_PATH . 'libs/inc/main.php');



        // Login as someone else
        if (isset($_SESSION['user'])) {
            if ($_SESSION['user'] == 'admin') { // && !empty($_GET['loginas'])) {
                // exec(VESTA_CMD . "v-list-user " . escapeshellarg($_GET['loginas']) . " json", $output, $return_var);
                // if ($return_var == 0) {
                // $_SESSION['look'] = $_GET['loginas'];
                // $data = json_decode(implode('', $output), true);
                // print_r(key($data));
                // die();
                // reset($data);
                $_SESSION['look'] = 'php'; //key($data);
                $_SESSION['look_alert'] = 'yes';
                // }
            }
            header("Location: / ");
            exit;
        }
    }
}
