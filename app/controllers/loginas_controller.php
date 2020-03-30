<?php

class LoginasController extends AuthController
{
    public function index($var)
    {
        // Main include
        include(APP_PATH . 'libs/inc/main.php');


        $uri = explode('/', $_SERVER['REQUEST_URI']);


        // Login as someone else
        if (isset($_SESSION['user'])) {
            if ($_SESSION['user'] == 'admin' && !empty($uri[3])) {
                exec(VESTA_CMD . "v-list-user " . escapeshellarg($uri[3]) . " json", $output, $return_var);
                if ($return_var == 0) {
                    $data = json_decode(implode('', $output), true);
                    reset($data);
                    $_SESSION['look'] = key($data);
                    $_SESSION['look_alert'] = 'yes';
                }
            }
            header("Location: /user ");
            exit;
        }
    }
}
