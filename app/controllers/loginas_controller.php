<?php

class LoginasController extends AuthController
{
    public function index()
    {
        // Generate CSRF token
        if (!isset($_SESSION['token'])) {

            // Session::set('token', md5(uniqid(mt_rand())));
            $_SESSION['token'] = md5(uniqid(mt_rand()));
        }
    }
    public function auth()
    {
        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        

        // Login as someone else
        if (isset($_SESSION['user'])) {
            if ($_SESSION['user'] == 'admin' && !empty($_SESSION['loginas'])) {
                exec(VESTA_CMD . "v-list-user " . escapeshellarg($_SESSION['loginas']) . " json", $output, $return_var);
                if ($return_var == 0) {
                    $data = json_decode(implode('', $output), true);
                    print_r($data);
                    die();
                    reset($data);
                    $_SESSION['look'] = key($data);
                    $_SESSION['look_alert'] = 'yes';
                }
            }
            header("Location: /");
            exit;
        }
    }
}
