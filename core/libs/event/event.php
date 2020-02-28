<?php

/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @category   Kumbia
 * @package    Event
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */
/**
 * @see Hook
 */
require CORE_PATH . 'libs/event/hook.php';

/**
 * Event handler
 *
 * @category   Kumbia
 * @package    Event
 */
class Event
{

    /**
     * Shared data
     *
     * @var mixed
     */
    public static $data = null;
    /**
     * Events
     *
     * @var array
     */
    protected static $_events = array();

    /**
     * Check if an event already has a handle
     *
     * @param string $event
     * @return boolean
     */
    public static function hasHandler($event)
    {
        return isset(self::$_events[$event]) && count(self::$_events[$event]);
    }

    /**
     * Link a handler with an event
     *
     * @param string $event event
     * @param mixed $handler call back
     */
    public static function bind($event, $handler)
    {
        self::setEvent($event);
        self::$_events[$event][] = $handler;
    }

    /**
     * Join the event handler2 before handler1
     *
     * @param string $event evento
     * @param mixed $handler1
     * @param mixed $handler2
     */
    public static function before($event, $handler1, $handler2)
    {
        self::setEvent($event);
        self::addHandler($event, $handler1, $handler2);
    }

    /**
     * Link the handler2 in the event after the handler1
     *
     * @param string $event evento
     * @param mixed $handler1
     * @param mixed $handler2
     */
    public static function after($event, $handler1, $handler2)
    {
        self::setEvent($event);
        self::addHandler($event, $handler1, $handler2, true);
    }

    /**
     * Unlink the handlers
     *
     * @param string $event event
     * @param mixed $handler handler
     */
    public static function unbind($event, $handler = false)
    {
        if ($handler && isset(self::$_events[$event])) {
            $i = array_search($handler, self::$_events[$event]);
            if ($i !== false) {
                unset(self::$_events[$event][$i]);
            }
        } else {
            self::$_events[$event] = array();
        }
    }

    /**
     * Replace one handler with another
     *
     * @param string $event event
     * @param mixed $handler1 handler to replace
     * @param mixed $handler2 new handler
     */
    public static function replace($event, $handler1, $handler2)
    {
        if (isset(self::$_events[$event])) {
            $i = array_search($handler1, self::$_events[$event]);
            if ($i !== false) {
                self::$_events[$event][$i] = $handler2;
                return true;
            }
        }
        return false;
    }

    /**
     * Run the handlers associated with the event
     *
     * @param string $event evento
     * @param array $args argumentos
     * @return mixed
     */
    public static function trigger($event, $args = array())
    {
        $value = false;
        if (isset(self::$_events[$event])) {
            foreach (self::$_events[$event] as $handler) {
                $value = call_user_func_array($handler, $args);
            }
        }

        self::$data = null;
        return $value;
    }

    /**
     * Create the event array if it does not exist
     *
     * @param string $event evento
     */
    private static function setEvent($event)
    {
        if (!isset(self::$_events[$event])) {
            self::$_events[$event] = array();
        }
    }

    /**
     * Add a handler
     *
     * @param string $event     event
     * @param mixed  $handler1
     * @param mixed  $handler2
     * @param bool   $after    Add before or after (default before)
     */
    private static function addHandler($event, $handler1, $handler2, $after = false)
    {
        $i = array_search($handler1, self::$_events[$event]);
        if ($i === false) {
            self::$_events[$event][] = $handler2;
            return;
        }
        if ($after) ++$i;

        array_splice(self::$_events[$event], $i, 0, $handler2);
    }
}
