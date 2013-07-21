<?php
namespace controllers;

/**
 * Base Controller Class
 *
 * @package controllers
 * @author Vijay Mahrra <vijay.mahrra@gmail.com>
 * @copyright (c) Copyright 2013 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

abstract class Base extends \Prefab {

    /**
    * f3 instance
    *
    * @var f3
    */
    protected $f3;

    /**
    * f3 logger instance
    *
    * @var logger
    */
    protected $logger;

    /**
    * initialize controller
    *
    * @return void
    */
    public function __construct() {
        $this->f3 = \Base::instance();
        $this->logger = \Registry::get('logger');
    }

    /**
     * set the language/country from the url
     */
    public function beforeRoute($f3, $params){
        // i18n - get/set the user language/country
        if (array_key_exists('language', $params)) {
            $language = trim(strip_tags(strtolower($params['language'])));
            $matches = preg_split("/-/", $language); // split language if format en-gb
            if (count($matches) == 2) {
                $country = $matches[1];
                $language = $matches[0];
                if (!empty($country)) {
                    $f3->set('SESSION.country', $country);
                    $f3->set('COUNTRY', $country);
                }
                if (!empty($language)) {
                    $f3->set('SESSION.language', $language);
                    $f3->set('LANGUAGE', $language . '-' . $country);
                }
            } else {
                $f3->set('SESSION.language', $language);
                $f3->set('LANGUAGE', $language);
            }
        }

        if (array_key_exists('country', $params)) {
            $country = trim(strip_tags(strtolower($params['country'])));
            if (!empty($country)) {
                $f3->set('SESSION.country', $country);
                $f3->set('COUNTRY', $country);
            }
        }
    }
}
