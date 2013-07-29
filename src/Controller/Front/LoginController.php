<?php
/**
 * User module Login controller
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Liu Chuang <liuchuang@eefocus.com>
 * @since           1.0
 * @package         Module\User
 */

namespace Module\User\Controller\Front;

use Pi\Mvc\Controller\ActionController;
use Module\User\Form\LoginForm;
use Module\User\Form\LoginFilter;
use Pi;
use Pi\Acl\Acl;

/**
 * Login controller for user.
 */
class LoginController extends ActionController
{
    // If already logged in
    public function indexAction()
    {
        $login = Pi::service('user')->getuser()->id;
        d($login);
    }



}
