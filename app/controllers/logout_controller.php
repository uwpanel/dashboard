<?php

class LogoutController extends AuthController
{
    public function index()
    {
        if (!empty($_SESSION['look'])) {
            unset($_SESSION['look']);
        } else {
            session_destroy();
        }
        header("Location: /user");
        exit;
    }
}
