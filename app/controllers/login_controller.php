<?php

class LoginController extends AuthController
{
    public function index()
    {
        $_SESSION['title'] = 'Log in';

        // Generate CSRF token
        if (!isset($_SESSION['token'])) {

            // Session::set('token', md5(uniqid(mt_rand())));
            $_SESSION['token'] = md5(uniqid(mt_rand()));
        }
    }
    public function auth()
    {
        define('NO_AUTH_REQUIRED', true);

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Basic auth
        if (isset($_POST['user']) && isset($_POST['password'])) {

            if (isset($_SESSION['token']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['token']) {
                $v_user = escapeshellarg($_POST['user']);
                $v_ip = escapeshellarg($_SERVER['REMOTE_ADDR']);

                // Get user's salt
                $output = '';
                exec(VESTA_CMD . "v-get-user-salt " . $v_user . " " . $v_ip . " json", $output, $return_var);
                $pam = json_decode(implode('', $output), true);

                if ($return_var > 0) {
                    $_SESSION['error_msg'] = "Invalid Credentials";
                } else {

                    $user = $_POST['user'];
                    $password = $_POST['password'];
                    $salt = $pam[$user]['SALT'];
                    $method = $pam[$user]['METHOD'];

                    if ($method == 'md5') {
                        $hash = crypt($password, '$1$' . $salt . '$');
                    }
                    if ($method == 'sha-512') {
                        $hash = crypt($password, '$6$rounds=5000$' . $salt . '$');
                        $hash = str_replace('$rounds=5000', '', $hash);
                    }
                    if ($method == 'des') {
                        $hash = crypt($password, $salt);
                    }

                    // Send hash via tmp file
                    $v_hash = exec('mktemp -p /tmp');
                    $fp = fopen($v_hash, "w");
                    fwrite($fp, $hash . "\n");
                    fclose($fp);

                    // Check user hash
                    exec(VESTA_CMD . "v-check-user-hash " . $v_user . " " . $v_hash . " " . $v_ip,  $output, $return_var);
                    unset($output);

                    // Remove tmp file
                    unlink($v_hash);



                    // Check API answer
                    if ($return_var > 0) {
                        $_SESSION['error_msg'] = "Invalid Credentials";
                    } else {

                        // Make root admin user
                        if ($_POST['user'] == 'root') $v_user = 'admin';

                        // Get user speciefic parameters
                        exec(VESTA_CMD . "v-list-user " . $v_user . " json", $output, $return_var);
                        $data = json_decode(implode('', $output), true);
                        unset($output);

                        // Define session user
                        $_SESSION['user'] = key($data);
                        $_SESSION['user_email'] = $data[key($data)]['CONTACT'];
                        $v_user = $_SESSION['user'];

                        // Check system configuration
                        exec(VESTA_CMD . "v-list-sys-config json", $output, $return_var);
                        $data = json_decode(implode('', $output), true);
                        unset($output);

                        $sys_arr = $data['config'];
                        foreach ($sys_arr as $key => $value) {
                            $_SESSION[$key] = $value;
                            echo "<br>", print_r($value);
                        }

                        echo "<pre>", print_r($_SESSION);


                        // Define language
                        $output = '';
                        exec(VESTA_CMD . "v-list-sys-languages json", $output, $return_var);
                        $languages = json_decode(implode('', $output), true);
                        if (in_array($data[$v_user]['LANGUAGE'], $languages)) {
                            $_SESSION['language'] = $data[$v_user]['LANGUAGE'];
                        } else {
                            $_SESSION['language'] = 'en';
                        }

                        // Regenerate session id to prevent session fixation
                        session_regenerate_id();
                    }
                }
                header("Location: /");
                unset($_SESSION['request_uri']);
            }
        }
    }
}
