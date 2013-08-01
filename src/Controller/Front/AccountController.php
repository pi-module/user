<?php
/**
 * User system account controller
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Liu Chuang <liuchuang@eefocus.com>
 * @since           3.0
 * @package         Module\User
 * @version         $Id$
 */

namespace Module\User\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Feature list:
 * 1. Basic information current account
 * 2. Entries to operate account action
 */
class AccountController extends ActionController
{
    /**
     * Display basic information about current account
     * Entries to operate account action
     *
     * @return array|void
     */
    public function indexAction()
    {
        $identity = Pi::service('user')->getUser()->identity;
        // Redirect login page if not logged in
        if (!$identity) {
            $this->redirect()->toRoute('', array('controller' => 'login'));
            return;
        }

        $row = $this->getModel('account')->find($identity, 'identity');
        $role = $this->getModel('role')->find($row->id, 'uid')->role;
        $roleRow = Pi::model('acl_role')->find($role, 'name');
        $user = array(
            __('ID')        => $row->id,
            __('Identity')  => $row->identity,
            __('Email')     => $row->email,
            __('Role')      => __($roleRow->title),
        );

        $title = __('Basic settings');
        $this->view()->assign(array(
            'title' => $title,
            'user'  => $user,
        ));

        $this->view()->assign('title', __('Basic info'));
    }
}