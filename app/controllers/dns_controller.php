<?php

class DnsController extends AppController
{
    public function index()
    {
        error_reporting(NULL);
        $_SESSION['title'] = 'DNS';

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Data & Render page
        exec(VESTA_CMD . "v-list-dns-domains $user json", $output, $return_var);
        $data = json_decode(implode('', $output), true);
        $this->data = array_reverse($data, true);
        unset($output);

        // Back uri
        $_SESSION['back'] = $_SERVER['REQUEST_URI'];
    }
    public function listrecord($param_domain)
    {
        error_reporting(NULL);
        $_SESSION['title'] = 'DNS';

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Data & Render page
        if (isset($param_domain)) {
            exec(VESTA_CMD . "v-list-dns-records " . $user . " " . escapeshellarg($param_domain) . " json", $output, $return_var);
            $data = json_decode(implode('', $output), true);
            $this->data = array_reverse($data, true);
            unset($output);
        }
        // Back uri
        $_SESSION['back'] = $_SERVER['REQUEST_URI'];
    }
    public function add()
    {
        error_reporting(NULL);
        ob_start();

        // Main include
        include(APP_PATH . 'libs/inc/main.php');
        // Check POST request for dns domain
        if (!empty($_POST['ok'])) {

            // Check token
            if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                header('location: /login/');
                exit();
            }

            // Check empty fields
            if (empty($_POST['v_domain'])) $errors[] = __('domain');
            if (empty($_POST['v_ip'])) $errors[] = __('ip');
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
            $v_domain = preg_replace("/^www./i", "", $_POST['v_domain']);
            $v_domain = escapeshellarg($v_domain);
            $v_domain = strtolower($v_domain);
            $v_ip = escapeshellarg($_POST['v_ip']);
            $v_ns1 = escapeshellarg($_POST['v_ns1']);
            $v_ns2 = escapeshellarg($_POST['v_ns2']);
            $v_ns3 = escapeshellarg($_POST['v_ns3']);
            $v_ns4 = escapeshellarg($_POST['v_ns4']);
            $v_ns5 = escapeshellarg($_POST['v_ns5']);
            $v_ns6 = escapeshellarg($_POST['v_ns6']);
            $v_ns7 = escapeshellarg($_POST['v_ns7']);
            $v_ns8 = escapeshellarg($_POST['v_ns8']);

            // Add dns domain
            if (empty($_SESSION['error_msg'])) {
                exec(VESTA_CMD . "v-add-dns-domain " . $user . " " . $v_domain . " " . $v_ip . " " . $v_ns1 . " " . $v_ns2 . " " . $v_ns3 . " " . $v_ns4 . " " . $v_ns5 . "  " . $v_ns6 . "  " . $v_ns7 . " " . $v_ns8 . " no", $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
            }


            // Set expiriation date
            if (empty($_SESSION['error_msg'])) {
                if ((!empty($_POST['v_exp'])) && ($_POST['v_exp'] != date('Y-m-d', strtotime('+1 year')))) {
                    $v_exp = escapeshellarg($_POST['v_exp']);
                    exec(VESTA_CMD . "v-change-dns-domain-exp " . $user . " " . $v_domain . " " . $v_exp . " no", $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                }
            }

            // Set ttl
            if (empty($_SESSION['error_msg'])) {
                if ((!empty($_POST['v_ttl'])) && ($_POST['v_ttl'] != '14400') && (empty($_SESSION['error_msg']))) {
                    $v_ttl = escapeshellarg($_POST['v_ttl']);
                    exec(VESTA_CMD . "v-change-dns-domain-ttl " . $user . " " . $v_domain . " " . $v_ttl . " no", $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                }
            }

            // Restart dns server
            if (empty($_SESSION['error_msg'])) {
                exec(VESTA_CMD . "v-restart-dns", $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
            }

            // Flush field values on success
            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = __('DNS_DOMAIN_CREATED_OK', htmlentities($_POST[v_domain]), htmlentities($_POST[v_domain]));
                unset($v_domain);
            }

            header('location: /dns');
        }
        $v_ns1 = str_replace("'", "", $v_ns1);
        $v_ns2 = str_replace("'", "", $v_ns2);
        $v_ns3 = str_replace("'", "", $v_ns3);
        $v_ns4 = str_replace("'", "", $v_ns4);
        $v_ns5 = str_replace("'", "", $v_ns5);
        $v_ns6 = str_replace("'", "", $v_ns6);
        $v_ns7 = str_replace("'", "", $v_ns7);
        $v_ns8 = str_replace("'", "", $v_ns8);
    }

    public function addrecord($param_domain)
    {
        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        $this->domain = $param_domain;

        // Check POST request for dns record
        if (!empty($_POST['ok_rec'])) {

            // Check token
            if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                header('location: /login/');
                exit();
            }

            // Check empty fields
            if (empty($_POST['v_domain'])) $errors[] = 'domain';
            if (empty($_POST['v_rec'])) $errors[] = 'record';
            if (empty($_POST['v_type'])) $errors[] = 'type';
            if (empty($_POST['v_val'])) $errors[] = 'value';
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
            $v_domain = escapeshellarg($_POST['v_domain']);
            $v_rec = escapeshellarg($_POST['v_rec']);
            $v_type = escapeshellarg($_POST['v_type']);
            $v_val = escapeshellarg($_POST['v_val']);
            $v_priority = escapeshellarg($_POST['v_priority']);

            // Add dns record
            if (empty($_SESSION['error_msg'])) {
                exec(VESTA_CMD . "v-add-dns-record " . $user . " " . $v_domain . " " . $v_rec . " " . $v_type . " " . $v_val . " " . $v_priority, $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
                $v_type = $_POST['v_type'];
            }

            // Flush field values on success
            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = __('DNS_RECORD_CREATED_OK', htmlentities($_POST[v_rec]), htmlentities($_POST[v_domain]));
                unset($v_domain);
                unset($v_rec);
                unset($v_val);
                unset($v_priority);
            }

            header('location: /dns/listrecord/' . $param_domain);
        }


        $v_ns1 = str_replace("'", "", $v_ns1);
        $v_ns2 = str_replace("'", "", $v_ns2);
        $v_ns3 = str_replace("'", "", $v_ns3);
        $v_ns4 = str_replace("'", "", $v_ns4);
        $v_ns5 = str_replace("'", "", $v_ns5);
        $v_ns6 = str_replace("'", "", $v_ns6);
        $v_ns7 = str_replace("'", "", $v_ns7);
        $v_ns8 = str_replace("'", "", $v_ns8);
    }

    public function edit($param_user, $param_domain)
    {
        error_reporting(NULL);
        ob_start();
        $TAB = 'DNS';

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Check domain name
        if (empty($param_domain)) {
            header("Location: /dns");
            exit;
        }

        // Edit as someone else?
        if (($_SESSION['user'] == 'admin') && (!empty($param_domain))) {
            $user = escapeshellarg($param_user);
        }

        // List dns domain
        if ((!empty($param_domain)) && (empty($param_record_id))) {
            $v_domain = escapeshellarg($param_domain);
            exec(VESTA_CMD . "v-list-dns-domain " . $user . " " . $v_domain . " json", $output, $return_var);
            check_return_code($return_var, $output);
            $data = json_decode(implode('', $output), true);
            unset($output);

            // Parse dns domain
            $v_username = $user;
            $this->v_domain = $param_domain;
            $this->v_ip = $data[$this->v_domain]['IP'];
            $this->v_template = $data[$this->v_domain]['TPL'];
            $this->v_ttl = $data[$this->v_domain]['TTL'];
            $this->v_exp = $data[$this->v_domain]['EXP'];
            $this->v_soa = $data[$this->v_domain]['SOA'];
            $this->v_date = $data[$this->v_domain]['DATE'];
            $this->v_time = $data[$this->v_domain]['TIME'];
            $v_suspended = $data[$this->v_domain]['SUSPENDED'];
            if ($v_suspended == 'yes') {
                $this->v_status =  'suspended';
            } else {
                $this->v_status =  'active';
            }

            // List dns templates
            exec(VESTA_CMD . "v-list-dns-templates json", $output, $return_var);
            $this->templates = json_decode(implode('', $output), true);
            unset($output);
        }

        // Check POST request for dns domain
        if ((!empty($_POST['save'])) && (!empty($param_domain)) && (empty($param_record_id))) {
            $v_domain = escapeshellarg($_POST['v_domain']);

            // Check token
            if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                header('location: /login/');
                exit();
            }

            // Change domain IP
            if (($v_ip != $_POST['v_ip']) && (empty($_SESSION['error_msg']))) {
                $v_ip = escapeshellarg($_POST['v_ip']);
                exec(VESTA_CMD . "v-change-dns-domain-ip " . $v_username . " " . $v_domain . " " . $v_ip . " no", $output, $return_var);
                check_return_code($return_var, $output);
                $restart_dns = 'yes';
                unset($output);
            }

            // Change domain template
            if (($v_template != $_POST['v_template']) && (empty($_SESSION['error_msg']))) {
                $v_template = escapeshellarg($_POST['v_template']);
                exec(VESTA_CMD . "v-change-dns-domain-tpl " . $v_username . " " . $v_domain . " " . $v_template . " no", $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
                $restart_dns = 'yes';
            }

            // Change SOA record
            if (($v_soa != $_POST['v_soa']) && (empty($_SESSION['error_msg']))) {
                $v_soa = escapeshellarg($_POST['v_soa']);
                exec(VESTA_CMD . "v-change-dns-domain-soa " . $v_username . " " . $v_domain . " " . $v_soa . " no", $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
                $restart_dns = 'yes';
            }

            // Change expiriation date
            if (($v_exp != $_POST['v_exp']) && (empty($_SESSION['error_msg']))) {
                $v_exp = escapeshellarg($_POST['v_exp']);
                exec(VESTA_CMD . "v-change-dns-domain-exp " . $v_username . " " . $v_domain . " " . $v_exp . " no", $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
            }

            // Change domain ttl
            if (($v_ttl != $_POST['v_ttl']) && (empty($_SESSION['error_msg']))) {
                $v_ttl = escapeshellarg($_POST['v_ttl']);
                exec(VESTA_CMD . "v-change-dns-domain-ttl " . $v_username . " " . $v_domain . " " . $v_ttl . " no", $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
                $restart_dns = 'yes';
            }

            // Restart dns server
            if (!empty($restart_dns) && (empty($_SESSION['error_msg']))) {
                exec(VESTA_CMD . "v-restart-dns", $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
            }

            // Set success message
            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = __('Changes has been saved.');
            }
            header('Location: /dns');
        }
    }

    public function editrecord($param_user, $param_domain, $param_record_id)
    {
        error_reporting(NULL);
        ob_start();
        $TAB = 'DNS';

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Check domain name
        if (empty($param_domain)) {
            header("Location: /dns");
            exit;
        }

        // Edit as someone else?
        if (($_SESSION['user'] == 'admin') && (!empty($param_domain))) {
            $user = escapeshellarg($param_user);
        }

        // List dns record
        if ((!empty($param_domain)) && (!empty($param_record_id))) {
            $v_domain = escapeshellarg($param_domain);
            $v_record_id = escapeshellarg($param_record_id);
            exec(VESTA_CMD . "v-list-dns-records " . $user . " " . $v_domain . " json", $output, $return_var);
            check_return_code($return_var, $output);
            $data = json_decode(implode('', $output), true);
            unset($output);

            // Parse dns record
            $v_username = $user;
            $this->v_domain = $param_domain;
            $this->v_record_id = $param_record_id;
            $this->v_rec = $data[$this->v_record_id]['RECORD'];
            $this->v_type = $data[$this->v_record_id]['TYPE'];
            $this->v_val = $data[$this->v_record_id]['VALUE'];
            $this->v_priority = $data[$this->v_record_id]['PRIORITY'];
            $this->v_suspended = $data[$this->v_record_id]['SUSPENDED'];
            if ($v_suspended == 'yes') {
                $this->v_status =  'suspended';
            } else {
                $this->v_status =  'active';
            }
            $this->v_date = $data[$this->v_record_id]['DATE'];
            $this->v_time = $data[$this->v_record_id]['TIME'];
        }

        // Check POST request for dns record
        if ((!empty($_POST['save'])) && (!empty($param_domain)) && (!empty($param_record_id))) {

            // Check token
            if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                header('location: /login/');
                exit();
            }

            // Protect input
            $v_domain = escapeshellarg($_POST['v_domain']);
            $v_record_id = escapeshellarg($_POST['v_record_id']);

            // Change dns record
            if (($v_val != $_POST['v_val']) || ($v_priority != $_POST['v_priority']) && (empty($_SESSION['error_msg']))) {
                $v_val = escapeshellarg($_POST['v_val']);
                $v_priority = escapeshellarg($_POST['v_priority']);
                exec(VESTA_CMD . "v-change-dns-record " . $v_username . " " . $v_domain . " " . $v_record_id . " " . $v_val . " " . $v_priority, $output, $return_var);
                check_return_code($return_var, $output);
                $v_val = $_POST['v_val'];
                unset($output);
                $restart_dns = 'yes';
            }

            // Change dns record id
            if (($param_record_id != $_POST['v_record_id']) && (empty($_SESSION['error_msg']))) {
                $v_old_record_id = escapeshellarg($param_record_id);
                exec(VESTA_CMD . "v-change-dns-record-id " . $v_username . " " . $v_domain . " " . $v_old_record_id . " " . $v_record_id, $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
                $restart_dns = 'yes';
            }

            // Restart dns server
            if (!empty($restart_dns) && (empty($_SESSION['error_msg']))) {
                exec(VESTA_CMD . "v-restart-dns", $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
            }

            // Set success message
            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = __('Changes has been saved.');
            }

            // Change url if record id was changed
            if ((empty($_SESSION['error_msg'])) && ($param_record_id != $_POST['v_record_id'])) {
                header("Location: /edit/dns/" . $param_domain . "/" . $_POST['v_record_id']);
                exit;
            }
        }
    }

    public function deletedomain($param_user, $param_domain, $param_token)
    {
        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Delete as someone else?
        if (($_SESSION['user'] == 'admin') && (!empty($param_user))) {
            $user = $param_user;
        }

        // Check token
        if ((!isset($param_token)) || ($_SESSION['token'] != $param_token)) {
            header('location: /login/');
            exit();
        }


        // DNS domain
        if (isset($param_domain)) {
            $v_username = escapeshellarg($user);
            $v_domain = escapeshellarg($param_domain);
            exec(VESTA_CMD . "v-delete-dns-domain " . $v_username . " " . $v_domain, $output, $return_var);
            check_return_code($return_var, $output);
            unset($output);

            $back = $_SESSION['back'];
            if (!empty($back)) {
                header("Location: " . $back);
                exit;
            }
            header('location: /dns');
            exit;
        }
    }

    public function deleterecord($param_user, $param_domain, $param_record_id, $param_token)
    {
        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Delete as someone else?
        if (($_SESSION['user'] == 'admin') && (!empty($param_user))) {
            $user = $param_user;
        }

        // Check token
        if ((!isset($param_token)) || ($_SESSION['token'] != $param_token)) {
            header('location: /login/');
            exit();
        }

        // DNS record
        if ((!empty($param_domain)) && (!empty($param_record_id))) {
            $v_username = escapeshellarg($user);
            $v_domain = escapeshellarg($param_domain);
            $v_record_id = escapeshellarg($param_record_id);
            exec(VESTA_CMD . "v-delete-dns-record " . $v_username . " " . $v_domain . " " . $v_record_id, $output, $return_var);
            check_return_code($return_var, $output);
            unset($output);
            $back = $_SESSION['back'];
            if (!empty($back)) {
                header("Location: " . $back);
                exit;
            }
            header('Location: /dns/listrecord/' . $param_domain);
            exit;
        }
    }
}
