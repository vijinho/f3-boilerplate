<?php

namespace FFMVC\Helpers;

/**
 * Messages Helper Class.
 *
 * @author Vijay Mahrra <vijay.mahrra@gmail.com>
 * @copyright (c) Copyright 2006-2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Messages extends \Prefab
{
    public static $TYPES = array(
        'success',
        'error',
        'warning',
        'message');

    final public static function init($sessionify = false)
    {
        $f3 = \Base::instance();
        $cli = (PHP_SAPI == 'cli');
        $messages = $f3->get('messages');
        if (empty($messages) && !$cli) {
            $messages = $f3->get('SESSION.messages');
        }
        if (empty($messages)) {
            $messages = array();
        }
        foreach (self::$TYPES as $type) {
            if (!array_key_exists($type, $messages)) {
                $messages[$type] = array();
            }
        }
        $f3->set('messages', $messages);
        if (!$cli) {
            $f3->set('sessionify_messages', $sessionify); // save messages in session?
        }
    }


    final public static function sessionify($boolean)
    {
        if (PHP_SAPI !== 'cli') {
            $f3 = \Base::instance();
            $f3->set('sessionify_messages', $boolean);
            return true;
        } else {
            return false;
        }
    }


    public function __destruct()
    {
        if (PHP_SAPI !== 'cli') {
            $f3 = \Base::instance();
            // save persistent messages
            $sessionify = $f3->get('sessionify_messages');
            $messages = $f3->get('messages');
            $f3->set('SESSION.messages',
                empty($sessionify) ? null : $messages);
        }
    }


    // add a message, default type is message
    final public static function add($message, $type = null)
    {
        $f3 = \Base::instance();
        $messages = $f3->get('messages');
        $type     = (empty($type) || !in_array($type,
                                               self::$TYPES)) ? 'message' : $type;
        // don't repeat messages!
        if (!in_array($message, $messages[$type]) && is_string($message)) {
            $messages[$type][] = $message;
        }
        $f3->set('messages', $messages);
    }

    // return messages of given type or all TYPES, return false if none
    final public static function sum($type = null)
    {
        $f3 = \Base::instance();
        $messages = $f3->get('messages');
        if (!empty($type)) {
            if (in_array($type, self::$TYPES)) {
                $i = count($messages[$type]);
                return $i;
            } else {
                return false;
            }
        }
        $i = 0;
        foreach (self::$TYPES as $type) {
            $i += count($messages[$type]);
        }
        return $i;
    }

    // return messages of given type or all TYPES, return false if none, clearing stack
    final public static function get($type = null)
    {
        $f3 = \Base::instance();
        $messages = $f3->get('messages');
        if (!empty($type)) {
            if (in_array($type, self::$TYPES)) {
                $i = count($messages[$type]);
                if ($i > 0) {
                    return $messages[$type];
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        // return false if there actually are no messages in the session
        $i = 0;
        foreach (self::$TYPES as $type) {
            $i += count($messages[$type]);
        }
        if ($i == 0) {
            return false;
        }

        // order return by order of type array above
        // i.e. success, error, warning and then informational messages last
        foreach (self::$TYPES as $type) {
            $return[$type] = $messages[$type];
        }
        return $return;
    }
}
