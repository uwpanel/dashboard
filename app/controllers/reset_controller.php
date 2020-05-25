<?php

class ResetController extends AuthController
{

    public function index()
    {
        $_SESSION['title'] = 'Reset Password';

        define('NO_AUTH_REQUIRED', true);

        //If user is already logged in
        if (isset($_SESSION['user'])) {
            header('Location: https://' . $_SERVER['HTTP_HOST']);
        }

        // Main include
        include(APP_PATH . 'libs/inc/main.php');
    }

    public function r1()
    {
        $_SESSION['title'] = 'Reset Password';

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        if ((!empty($_POST['user'])) && (empty($_POST['code']))) {
            $v_user = escapeshellarg($_POST['user']);
            $user = $_POST['user'];
            $cmd = "/usr/bin/sudo /usr/local/vesta/bin/v-list-user";
            exec($cmd . " " . $v_user . " json", $output, $return_var);
            if ($return_var == 0) {
                $data = json_decode(implode('', $output), true);
                $rkey = $data[$user]['RKEY'];
                $fname = $data[$user]['FNAME'];
                $lname = $data[$user]['LNAME'];
                $contact = $data[$user]['CONTACT'];
                $to = $data[$user]['CONTACT'];
                $fetched_user = explode('"', $output[1])[1];
                if (!empty($fetched_user)) {
                    $_SESSION['fetched_user'] = $fetched_user;
                }
                // $subject = __('MAIL_RESET_SUBJECT', date("Y-m-d H:i:s"));
                // $hostname = exec('hostname');
                // $from = __('MAIL_FROM', $hostname);
                //         if (!empty($fname)) {
                //             $mailtext = __('GREETINGS_GORDON_FREEMAN', $fname, $lname);
                //         } else {
                //             $mailtext = __('GREETINGS');
                //         }
                //         $mailtext .= __('PASSWORD_RESET_REQUEST', $_SERVER['HTTP_HOST'], $user, $rkey, $_SERVER['HTTP_HOST'], $user, $rkey);
                //         if (!empty($rkey)) send_email($to, $subject, $mailtext, $from);
                // unset($output);
            }
            //     header("Location: https://" . $_SERVER['HTTP_HOST'] . "/reset/r1?action=code&user=" . $_POST['user']);
            //     exit;
        }
    }

    public function r2()
    {
        $_SESSION['title'] = 'Reset Password';
        // Main include
        // include(APP_PATH . 'libs/inc/main.php');

        if ((!empty($_SESSION['fetched_user'])) && (!empty($_SESSION['fetched_code'])) && (!empty($_POST['password']))) {

            if ($_POST['password'] == $_POST['password_confirm']) {

                // echo "<pre>", $_SESSION['fetched_user'], "\n", $_SESSION['fetched_code'], "\n", $_POST['password'];
                // die();
                $v_user = escapeshellarg($_SESSION['fetched_user']);
                $user = $_SESSION['fetched_user'];
                $cmd = "/usr/bin/sudo /usr/local/vesta/bin/v-list-user";
                exec($cmd . " " . $v_user . " json", $output, $return_var);

                if ($return_var == 0) {
                    $data = json_decode(implode('', $output), true);
                    $rkey = $data[$user]['RKEY'];
                    if (hash_equals($rkey, $_POST['code'])) {
                        // $v_password = tempnam("/tmp", "vst");
                        // $fp = fopen($v_password, "w");
                        // fwrite($fp, $_POST['password'] . "\n");
                        // fclose($fp);
                        // $cmd = "/usr/bin/sudo /usr/local/vesta/bin/v-change-user-password";
                        // exec($cmd . " " . $v_user . " " . $v_password, $output, $return_var);
                        // unlink($v_password);
                        // if ($return_var > 0) {
                        //     $ERROR = "<a class=\"error\">" . __('An internal error occurred') . "</a>";
                        // } else {
                        //     $_SESSION['user'] = $_POST['user'];
                        //     header('Location: https://' . $_SERVER['HTTP_HOST']);
                        //     exit;
                        // }
                    } else {
                        $_SESSION['error_msg'] = 'Invalid username or code';
                    }
                } else {
                    $_SESSION['error_msg'] = 'Invalid username or code';
                }
            } else {
                $_SESSION['error_msg'] = 'Passwords not match';
            }
        }

        //this else runs for the form submit event of reset/r1 (enter code from)
        else {
            $_SESSION['fetched_code'] = $_POST['code'];
        }
    }
}
