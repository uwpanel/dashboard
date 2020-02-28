<?php

/**
 * @see Controller new controller
 */
require_once CORE_PATH . 'kumbia/controller.php';

/**
 * Auth controller inherited by the controllers
 *
 * All controllers inherit from this class at a higher level
 * therefore the methods defined here are available for
 * any controller
 *
 * @category Kumbia
 * @package Controller
 */
class AuthController extends Controller
{

    final protected function initialize()
    {
        View::template('auth');
    }

    final protected function finalize()
    {
    }
}
