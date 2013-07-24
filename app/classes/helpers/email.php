<?php
namespace helpers;

/**
 * Email Helper Class
 *
 * <example>
 *    $emailer = \helpers\Email::instance();
 *    $email = array(
 *         'subject' => 'test',
 *         'body' => 'poop'
 *    );
 *    $emailer->send($email);
 * </example>
 *
 * @package helpers
 * @author Vijay Mahrra <vijay.mahrra@gmail.com>
 * @copyright (c) Copyright 2013 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

class Email extends \Prefab {

    const MISSING_SUBJECT = 1;
    const MISSING_BODY = 2;
    const SENDING_FAILED = 3;

    /**
     * send an email
     *
     * @param array $params email params, to, from, subject, header etc
     * @return boolean success/failure
     */
    public static function send($params = array()) {
        $f3 = \Base::instance();
        $settings = $f3->get('email');

        $params = array_map('trim', $params); // trim whitespace

        if (!array_key_exists('to', $params) || empty($params['to'])) {
            $params['to'] = $settings['to'];
        }

        if (!array_key_exists('from', $params) || empty($params['from'])) {
            $params['from'] = $settings['from'];
        }

        if (array_key_exists('subject', $params) && !empty($params['subject'])) {
            $params['subject'] = $settings['subject'] . $params['subject'];
        } else {
            throw new \Exception("Missing email subject!", self::MISSING_SUBJECT);
        }

        if (array_key_exists('body', $params) && !empty($params['body'])) {
            $params['body'] = trim($params['body']);
        } else {
            throw new \Exception("Missing email body!", self::MISSING_BODY);
        }

        $params['header'] = sprintf("From: %s", $params['from']);

        if (!mail($params['to'], $params['subject'], $params['body'], $params['header'])) {
            throw new \Exception("Sending the email failed", self::SENDING_FAILED);
        }
        return true;
    }

}
