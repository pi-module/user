<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Api;

use Pi;
use Pi\Application\AbstractApi;

/**
 * User password manipulation APIs
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class Form extends AbstractApi
{
    /**
     * Create salt
     *
     * @return string
     */
    public function createSalt()
    {
        return uniqid(mt_rand(), 1);
    }

    /**
     * Transform credential
     *
     * @param $credential raw credential
     * @param $salt
     * @return $credential string hash credential
     */
    public function transformCredential($credential, $salt)
    {
        $credential = md5(sprintf('%s%s%s', $salt, $credential));
        return $credential;
    }
}
