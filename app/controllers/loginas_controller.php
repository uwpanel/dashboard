<?php

class LoginasController extends AuthController
{
    public function index($param_user)
    {
        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Login as someone else
        if (isset($_SESSION['user'])) {
            if ($_SESSION['user'] == 'admin' && !empty($param_user)) {
                exec(VESTA_CMD . "v-list-user " . escapeshellarg($param_user) . " json", $output, $return_var);
                if ($return_var == 0) {
                    $data = json_decode(implode('', $output), true);
                    reset($data);
                    $_SESSION['look'] = key($data);
                    $_SESSION['look_alert'] = 'yes';
                    header("Location: /");
                    exit;
                }
            }
            header("Location: /user");
            exit;
        }
    }
}
