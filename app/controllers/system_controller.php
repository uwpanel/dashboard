<?php
class SystemController extends AppController
{
    public function index()
    {
    }
    public function restart($param_hostname, $param_token)
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
            if (!empty($param_hostname)) {
                exec(VESTA_CMD . "v-restart-system yes", $output, $return_var);
                $_SESSION['error_msg'] = 'The system is going down for reboot NOW!';
            }
            unset($output);
        }

        header("Location: /server");
        exit;
    }
}
