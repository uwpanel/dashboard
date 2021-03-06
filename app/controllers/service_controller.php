<?php
class ServiceController extends AppController
{
    public function index()
    {
    }

    public function start($param_service)
    {
        // Init
        error_reporting(NULL);
        ob_start();
        session_start();
        include(APP_PATH . 'libs/inc/main.php');

        if ($_SESSION['user'] == 'admin') {
            if (!empty($param_service)) {
                if ($param_service == 'iptables') {
                    exec(VESTA_CMD . "v-update-firewall", $output, $return_var);
                } else {
                    $v_service = escapeshellarg($param_service);
                    exec(VESTA_CMD . "v-start-service " . $v_service, $output, $return_var);
                }
            }
            if ($return_var != 0) {
                $error = implode('<br>', $output);
                if (empty($error)) $error =  __('SERVICE_ACTION_FAILED', __('start'), $v_service);;
                $_SESSION['error_srv'] = $error;
            }
            unset($output);
        }

        header("Location: /server");
        exit;
    }

    public function restart($param_service, $param_token)
    {
        // Init
        error_reporting(NULL);
        ob_start();
        session_start();
        include(APP_PATH . 'libs/inc/main.php');

        // Check token
        if ((!isset($param_token)) || ($_SESSION['token'] != $param_token)) {
            header('location: /login/');
            exit();
        }

        if ($_SESSION['user'] == 'admin') {
            if (!empty($param_service)) {
                if ($param_service == 'iptables') {
                    exec(VESTA_CMD . "v-update-firewall", $output, $return_var);
                } else {
                    $v_service = escapeshellarg($param_service);
                    exec(VESTA_CMD . "v-restart-service " . $v_service, $output, $return_var);
                }
            }
            if ($return_var != 0) {
                $error = implode('<br>', $output);
                if (empty($error)) $error =  __('SERVICE_ACTION_FAILED', __('restart'), $v_service);
                $_SESSION['error_msg'] = $error;
            }
            unset($output);
        }

        header("Location: /server");
        exit;
    }

    public function stop($param_service)
    {
        // Init
        error_reporting(NULL);
        ob_start();
        session_start();
        include(APP_PATH . 'libs/inc/main.php');

        if ($_SESSION['user'] == 'admin') {
            if (!empty($param_service)) {
                if ($param_service == 'iptables') {
                    exec(VESTA_CMD . "v-stop-firewall", $output, $return_var);
                } else {
                    $v_service = escapeshellarg($param_service);
                    exec(VESTA_CMD . "v-stop-service " . $v_service, $output, $return_var);
                }
            }

            if ($return_var != 0) {
                $error = implode('<br>', $output);
                if (empty($error)) {
                    $error = __('SERVICE_ACTION_FAILED', __('stop'), $v_service);
                }

                $_SESSION['error_srv'] = $error;
            }
            unset($output);
        }

        header("Location: /server");
        exit;
    }
}
