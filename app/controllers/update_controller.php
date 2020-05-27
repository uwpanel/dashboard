<?php
class UpdateController extends AppController
{
    public function index($param_pkg,$param_token)
    {
        // Init
        error_reporting(NULL);
        ob_start();
        session_start();
        include(APP_PATH . 'libs/inc/main.php');

        // Check token
        if ((!isset($param_token)) || ($_SESSION['token'] != $param_token)) {
            header('location: /login');
            exit();
        }

        if ($_SESSION['user'] == 'admin') {
            if (!empty($param_pkg)) {
                $v_pkg = escapeshellarg($param_pkg);
                exec(VESTA_CMD . "v-update-sys-vesta " . $v_pkg, $output, $return_var);
            }

            if ($return_var != 0) {
                $error = implode('<br>', $output);
                if (empty($error)) $error = 'Error: ' . $v_pkg . ' update failed';
                $_SESSION['error_msg'] = $error;
            }else{
                $_SESSION['ok_msg'] = $v_pkg .' : Updated to Latest Version';
            }
            unset($output);
        }

        header("Location: /server/updates/");
        exit;
    }
}
