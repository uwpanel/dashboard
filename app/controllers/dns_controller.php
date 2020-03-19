<?php

/**
 * Default controller if routes are not used
 *
 */
class DnsController extends AppController
{
    public function index()
    {
        error_reporting(NULL);
        $_SESSION['title'] = 'DNS';

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Data & Render page
        if (empty($_GET['domain'])) {
            exec(VESTA_CMD . "v-list-dns-domains $user json", $output, $return_var);

            $data = json_decode(implode('', $output), true);
            $this->data = array_reverse($data, true);
            unset($output);

            render_page($user, $TAB, 'list_dns');
        } else {
            exec(VESTA_CMD . "v-list-dns-records " . $user . " " . escapeshellarg($_GET['domain']) . " json", $output, $return_var);
            $data = json_decode(implode('', $output), true);
            $this->data = array_reverse($data, true);
            unset($output);

            render_page($user, $TAB, 'list_dns_rec');
        }

        // Back uri
        $_SESSION['back'] = $_SERVER['REQUEST_URI'];
    }
    public function add()
    {
    }
    public function edit()
    {
    }
}
