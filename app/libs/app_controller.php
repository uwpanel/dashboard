<?php

/**
 * @see Controller nuevo controller
 */
require_once CORE_PATH . 'kumbia/controller.php';

/**
 * Primary controller inherited by the controllers
 *
 * All controllers inherit from this class at a higher level
 * therefore the methods defined here are available for
 * any controller
 *
 * @category Kumbia
 * @package Controller
 */
class AppController extends Controller
{

    final protected function initialize()
    {
    }

    final protected function finalize()
    {
    }
}
