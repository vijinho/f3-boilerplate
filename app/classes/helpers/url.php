<?php
namespace helpers;

/**
 * URL Helper Class
 *
 * @package helpers
 * @author Vijay Mahrra <vijay.mahrra@gmail.com>
 * @copyright (c) Copyright 2013 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

class URL extends \Prefab {

	/**
	 * Get the base url of the website
	 *
	 * @return string $path the base website url
	 * @author Vijay Mahrra
     */
    public static function base()
    {
    	$f3 = \F3::instance();
        if (preg_match("/(^http[s]?:\/\/[^\/]+)/i", $f3->get('REALM'), $matches)) {
            return $matches[1];
        }
        return false;
    }

    /**
     * Get an i18n URL of the website in the format: http://example.org/en/gb/some_url
     *
     * @param string $url
     * @param string optional $language the language-country takes priority, eg. with 'sv-se' 'se' becomes the country
     * @param string optional $country
     * @return string $path the base website url
     * @author Vijay Mahrra
     */
    public static function i18n($url = '', $language = null, $country = null)
    {
        // get the user language if not set
        $language = substr((empty($language)) ? \F3::instance()->get('LANGUAGE') : $language, 0, 2);
        $country = (empty($country)) ? \F3::instance()->get('COUNTRY') : $country;
        return self::base() . '/' . $language . '/' . $country . '/' . $url;
    }
}
