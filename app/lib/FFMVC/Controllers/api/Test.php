<?php

namespace FFMVC\Controllers\api;

/**
 * Api Test Controller Class.
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Test extends Api
{
    /**
     * authorize all incoming requests, redirect to unknown if not production.
     */
    public function beforeRoute($f3, $params)
    {
        parent::beforeRoute($f3, $params);
        if ($f3->get('application.environment') == 'production') {
            $f3->reroute('/api/errors/unknown');
        }
    }

// route /api
    public function request($f3, $params)
    {
        $this->params['http_methods'] = 'GET,HEAD';
        $this->data += array(
            'name' => 'globals',
            'description' => 'Global Variables',
            'globals' => array(
                'SERVER' => $f3->get('SERVER'),
                'ENV' => $f3->get('ENV'),
                'COOKIE' => $f3->get('COOKIE'),
                'SESSION' => $f3->get('SESSION'),
                'REQUEST' => $f3->get('REQUEST'),
                'GET' => $f3->get('GET'),
                'POST' => $f3->get('POST'),
                'FILES' => $f3->get('FILES'),
            ),
        );
    }
}
