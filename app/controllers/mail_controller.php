<?php

/**
 * Default controller if routes are not used
 *
 */
class MailController extends AppController
{
    public function index()
    {
        error_reporting(NULL);
        $_SESSION['title'] = 'MAIL';

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Data & Render page
        if (empty($_GET['domain'])) {
            exec(VESTA_CMD . "v-list-mail-domains $user json", $output, $return_var);

            $data = json_decode(implode('', $output), true);
            $this->data = array_reverse($data, true);
            unset($output);

            render_page($user, $TAB, 'list_mail');
        } else {
            exec(VESTA_CMD . "v-list-mail-accounts " . $user . " " . escapeshellarg($_GET['domain']) . " json", $output, $return_var);
            $data = json_decode(implode('', $output), true);
            $this->data = array_reverse($data, true);
            unset($output);

            render_page($user, $TAB, 'list_mail_acc');
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
