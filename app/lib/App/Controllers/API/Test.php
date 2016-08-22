<?php

namespace App\Controllers\API;

/**
 * API Test Controller Class.
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Test extends API
{
    /**
     * @param \Base $f3
     * @param array $params
     * @return void
     */
    public function request($f3, array $params)
    {
        if (empty($this->validateAccess())) {
            //return;
        }
        $this->data += [
            'name'        => 'globals',
            'description' => 'Global Variables',
            'globals'     => [
                'SERVER'  => $f3->get('SERVER'),
                'ENV'     => $f3->get('ENV'),
                'COOKIE'  => $f3->get('COOKIE'),
                'SESSION' => $f3->get('SESSION'),
                'REQUEST' => $f3->get('REQUEST'),
                'GET'     => $f3->get('GET'),
                'POST'    => $f3->get('POST'),
                'FILES'   => $f3->get('FILES'),
            ],
        ];
    }
}
