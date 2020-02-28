<?php

class LoginController extends AuthController
{
    public function index()
    {
        if (Input::hasPost("username", "password")) {
            $pwd  = Input::post("password");
            $user = Input::post("username");

            $auth  =  new Auth("model",  "class: users",  "login: $user ",  "password: $pwd ");

            if ($auth->authenticate()) {
                Redirect::to("index/index");
            } else {
                Flash::error("Failed");
            }
        }
    }
    public function logout()
    {
        Auth::destroy_identity();
    }
}
