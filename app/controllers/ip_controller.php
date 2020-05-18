<?php

class IpController extends AppController
{

    public function index()
    {
        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        $_SESSION['title'] = "IP";
    }

    public function add()
    {
        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        $_SESSION['title'] = "Adding IP Address";

        // Check user
        if ($_SESSION['user'] != 'admin' || isset($_SESSION['look'])) {
            header("Location: /");
            exit;
        }

        // Check POST request
        if (!empty($_POST['ok'])) {

            // Check token
            if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                header('location: /login');
                exit();
            }

            // Check empty fields
            if (empty($_POST['v_ip'])) $errors[] = __('ip address');
            if (empty($_POST['v_netmask'])) $errors[] = __('netmask');
            if (!empty($errors[0])) {
                foreach ($errors as $i => $error) {
                    if ($i == 0) {
                        $error_msg = $error;
                    } else {
                        $error_msg = $error_msg . ", " . $error;
                    }
                }
                $_SESSION['error_msg'] = __('Field "%s" can not be blank.', $error_msg);
            }

            // Protect input
            $v_ip = escapeshellarg($_POST['v_ip']);
            $v_netmask = escapeshellarg($_POST['v_netmask']);
            $v_name = escapeshellarg($_POST['v_name']);
            $v_nat = escapeshellarg($_POST['v_nat']);
            $v_interface = escapeshellarg($_POST['v_interface']);
            $v_owner = escapeshellarg($_POST['v_owner']);
            $v_shared = $_POST['v_shared'];

            // Check shared checkmark
            if ($v_shared == 'on') {
                $ip_status = 'shared';
            } else {
                $ip_status = 'dedicated';
                $v_dedicated = 'yes';
            }

            // Add IP
            if (empty($_SESSION['error_msg'])) {
                exec(VESTA_CMD . "v-add-sys-ip " . $v_ip . " " . $v_netmask . " " . $v_interface . "  " . $v_owner . " " . $ip_status . " " . $v_name . " " . $v_nat, $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
                $v_owner = $_POST['v_owner'];
                $v_interface = $_POST['v_interface'];
            }

            // Flush field values on success
            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = __('IP_CREATED_OK', htmlentities($_POST['v_ip']), htmlentities($_POST['v_ip']));
                unset($v_ip);
                unset($v_netmask);
                unset($v_name);
                unset($v_nat);
            }

            header('Location: /server/ip');
        }

        // List network interfaces
        exec(VESTA_CMD . "v-list-sys-interfaces json", $output, $return_var);
        $this->interfaces = json_decode(implode('', $output), true);
        unset($output);

        // List users
        exec(VESTA_CMD . "v-list-sys-users json", $output, $return_var);
        $this->users = json_decode(implode('', $output), true);
        unset($output);
    }

    public function edit($param_ip)
    {
        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        $_SESSION['title'] = "Editing IP Address";

        // Check user
        if ($_SESSION['user'] != 'admin' || isset($_SESSION['look'])) {
            header("Location: /");
            exit;
        }

        // Check ip argument
        if (isset($param_ip) == '') {
            header("Location: /");
            exit;
        }

        // List ip
        $v_ip = escapeshellarg($param_ip);
        exec(VESTA_CMD . "v-list-sys-ip " . $v_ip . " json", $output, $return_var);
        check_return_code($return_var, $output);
        $data = json_decode(implode('', $output), true);
        unset($output);

        // Parse ip
        $v_username = $user;
        $this->v_ip = $param_ip;
        $this->v_netmask = $data[$this->v_ip]['NETMASK'];
        $this->v_interace = $data[$this->v_ip]['INTERFACE'];
        $this->v_name = $data[$this->v_ip]['NAME'];
        $this->v_nat = $data[$this->v_ip]['NAT'];
        $this->v_ipstatus = $data[$this->v_ip]['STATUS'];
        if ($v_ipstatus == 'dedicated') $v_dedicated = 'yes';
        $v_owner = $data[$this->v_ip]['OWNER'];
        $v_date = $data[$this->v_ip]['DATE'];
        $v_time = $data[$this->v_ip]['TIME'];
        $v_suspended = $data[$this->v_ip]['SUSPENDED'];
        if ($v_suspended == 'yes') {
            $v_status =  'suspended';
        } else {
            $v_status =  'active';
        }

        // List users
        exec(VESTA_CMD . "v-list-sys-users json", $output, $return_var);
        $users = json_decode(implode('', $output), true);
        unset($output);

        // Check POST request
        if (!empty($_POST['save'])) {
            $this->v_ip = escapeshellarg($_POST['v_ip']);

            // Change Status
            if (($v_ipstatus == 'shared') && (empty($_POST['v_shared'])) && (empty($_SESSION['error_msg']))) {
                exec(VESTA_CMD . "v-change-sys-ip-status " . $this->v_ip . " dedicated", $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
                $v_dedicated = 'yes';
            }
            if (($v_ipstatus == 'dedicated') && (!empty($_POST['v_shared'])) && (empty($_SESSION['error_msg']))) {
                exec(VESTA_CMD . "v-change-sys-ip-status " . $this->v_ip . " shared", $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
                unset($v_dedicated);
            }

            // Change owner
            if (($v_owner != $_POST['v_owner']) && (empty($_SESSION['error_msg']))) {
                $v_owner = escapeshellarg($_POST['v_owner']);
                exec(VESTA_CMD . "v-change-sys-ip-owner " . $this->v_ip . " " . $v_owner, $output, $return_var);
                check_return_code($return_var, $output);
                $v_owner = $_POST['v_owner'];
                unset($output);
            }

            // Change associated domain
            if (($v_name != $_POST['v_name']) && (empty($_SESSION['error_msg']))) {
                $v_name = escapeshellarg($_POST['v_name']);
                exec(VESTA_CMD . "v-change-sys-ip-name " . $this->v_ip . " " . $v_name, $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
            }

            // Change NAT address
            if (($v_nat != $_POST['v_nat']) && (empty($_SESSION['error_msg']))) {
                $v_nat = escapeshellarg($_POST['v_nat']);
                exec(VESTA_CMD . "v-change-sys-ip-nat " . $this->v_ip . " " . $v_nat, $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
            }

            // Set success message
            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = __('Changes has been saved.');
            }
        }

        // Flush session messages
        unset($_SESSION['error_msg']);
        unset($_SESSION['ok_msg']);
    }

    public function delete($param_ip,$param_token)
    {
        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        $_SESSION['title'] = "Delete - IP";

        // Check token
        if ((!isset($param_token)) || ($_SESSION['token'] != $param_token)) {
            header('location: /login');
            exit();
        }

        if ($_SESSION['user'] == 'admin' ) {
            if (!empty($param_ip)) {
                $v_ip = escapeshellarg($param_ip);
                exec(VESTA_CMD . "v-delete-sys-ip " . $v_ip, $output, $return_var);
            }
            check_return_code($return_var, $output);
            unset($output);
        }

        header("Location: /server/ip");
        exit;
    }
}
