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
 * @copyright       Copyright (c) Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Liu Chuang <liuchuang@eefocus.com>
 * @since           1.0
 * @package         Module\User
 */

namespace Module\User\Controller\Admin;

use Pi\Mvc\Controller\ActionController;
use Zend\Db\Sql\Predicate\Expression;
use Pi;
use Pi\Paginator\Paginator;

class AccountController extends ActionController
{
    /**
     * User account list
     *
     * @return array|void
     */
    public function indexAction()
    {
        $page = $this->params('p', 1);
        $limit = 20;
        $offset = (int) ($page -1) * $limit;

        // Get usr list
        $accountModel = $this->getModel('account');
        $where = array(
            'active'  => 1,
            'ban'     => 0,
            'visible' => 1,
        );
        $select = $accountModel->select()->where($where)->order('id')->offset($offset)->limit($limit);
        $accountRow = $accountModel->selectWith($select);

        $users = array();
        // Get user account info
        foreach ($accountRow as $row) {
            $users[$row->id] = array(
                'id' => $row->id,
                'identity' => $row->identity,
                'email'    => $row->email,
            );
        }

        // Total count
        $select = $accountModel->select()->columns(array('count' => new Expression('count(*)')))->where($where);
        $count  = $accountModel->selectWith($select)->current()->count;

        // Get user data
        $model = $this->getModel('user_data');
        $rowset = $model->select(array('uid' => array_keys($users)));
        foreach ($rowset as $row) {
            $users[$row->uid]['time_register'] = $row->time_register;
            $users[$row->uid]['register_ip'] = $row->register_ip;
        }


        // Get user role
        $roleList = array();
        $model  = $this->getModel('role');
        $rowset = $model->select(array('uid' => array_keys($users)));
        foreach ($rowset as $row) {
            $users[$row->uid]['role'] = $row->role;
            $roleList[$row->role] = '';
        }

        $model  = $this->getModel('staff');
        $rowset = $model->select(array('uid' => array_keys($users)));
        foreach ($rowset as $row) {
            $users[$row->uid]['roll_staff'] = $row->role;
            $roleList[$row->role] = '';
        }

        $roles = $this->getRoles();
        foreach (array_keys($roleList) as $name) {
            $roleList[$name] = $roles[$name];
        }

        foreach ($users as $id => &$user) {
            $user['role'] = $roleList[$user['role']];
            $user['role_staff'] = isset($user['role_staff']) ? $roleList[$user['role_staff']] : '';
        }

        // Set paginator
        $paginator = Paginator::factory(intval($count));
        $paginator->setItemCountPerPage($limit);
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(array(
            // Use router to build URL for each page
            'pageParam'     => 'p',
            'totalParam'    => 't',
            'router'        => $this->getEvent()->getRouter(),
            'route'         => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            'params'        => array(
                'module'        => $this->getModule(),
                'controller'    => 'account',
                'action'        => 'index',
            ),
        ));

        $this->view()->assign(array(
            'paginator' => $paginator,
            'users'     => $users,
        ));
        $this->view()->assign('paginator', $paginator);
    }

    /**
     * Get system roles
     * @return mixed
     */
    protected function getRoles()
    {
        $model = Pi::model('acl_role');
        $rowset = $model->select(array());
        foreach ($rowset as $row) {
            $roles[$row->name] = __($row->title);
        }

        return $roles;
    }
}