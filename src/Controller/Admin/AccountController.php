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
        $limit = 5;
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
            $users[$row->uid]['role_staff'] = $row->role;
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
        $paginatorOption = array(
            'count' => $count,
            'limit' => $limit,
            'page'  => $page,
            'controller' => 'account',
            'action'     => 'index'
        );
        $paginator = $this->setPaginator($paginatorOption);

        $this->view()->assign(array(
            'paginator' => $paginator,
            'users'     => $users,
        ));
        $this->view()->assign('paginator', $paginator);
    }

    /**
     * Manage user
     * 1. Ban
     * 2. Delete
     * 3. Assign role
     */
    public function manageAction()
    {
        $type     = trim(_post('type'));
        $ids      = trim(_post('ids'));
        $redirect = trim(_post('redirect'));
        $ids      = array_filter(explode(',', substr($ids, 0, strlen($ids) -1)));

        if (empty($ids)) {
            if (!$redirect) {
                $this->redirect()->toUrl($redirect);
            } else {
                $this->redirect()->toRoute('admin', array('controller' => 'account', 'action' => 'index'));
            }
        }

        switch ($type) {
            case 'ban':
                $this->banUser($ids);
                break;
            case 'delete':
                $this->deleteUser($ids);
                break;
            case 'active':
                $this->activeUser($ids);
                break;
            case 'unban':
                $this->unbanUser($ids);
                break;
            case 'role':
                break;
        }

        if ($redirect) {
            return $this->redirect()->toUrl($redirect);
        }

        $this->redirect()->toRoute('admin', array('controller' => 'account', 'action' => 'index'));
        $this->view()->setTemplate(false);
    }

    public function pendingAction()
    {
        $page = $this->params('p', 1);
        $limit = 5;
        $offset = (int) ($page -1) * $limit;

        // Get usr list
        $accountModel = $this->getModel('account');
        $where = array(
            'active'  => 0,
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

        if ($users) {
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
                $users[$row->uid]['role_staff'] = $row->role;
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
        }

        // Set paginator
        $paginatorOption = array(
            'count' => $count,
            'limit' => $limit,
            'page'  => $page,
            'controller' => 'account',
            'action'     => 'pending'
        );
        $paginator = $this->setPaginator($paginatorOption);

        $this->view()->assign(array(
            'paginator' => $paginator,
            'users'     => $users,
        ));
        $this->view()->assign('paginator', $paginator);
    }

    /**
     * List ban users
     * Operator to user
     */
    public function bannedAction()
    {
        $page = $this->params('p', 1);
        $limit = 5;
        $offset = (int) ($page -1) * $limit;

        // Get usr list
        $accountModel = $this->getModel('account');
        $where = array(
            'ban'     => 1,
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


        if ($users) {
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
                $users[$row->uid]['role_staff'] = $row->role;
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
        }


        // Set paginator
        $paginatorOption = array(
            'count' => $count,
            'limit' => $limit,
            'page'  => $page,
            'controller' => 'account',
            'action'     => 'banned'
        );
        $paginator = $this->setPaginator($paginatorOption);

        $this->view()->assign(array(
            'paginator' => $paginator,
            'users'     => $users,
        ));
        $this->view()->assign('paginator', $paginator);

    }

    /**
     * List all deleted users
     */
    public function deletedAction()
    {
        $page = $this->params('p', 1);
        $limit = 5;
        $offset = (int) ($page -1) * $limit;

        // Get usr list
        $accountModel = $this->getModel('account');
        $where = array(
            'visible' => 0,
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


        if ($users) {
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
                $users[$row->uid]['role_staff'] = $row->role;
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
        }


        // Set paginator
        $paginatorOption = array(
            'count' => $count,
            'limit' => $limit,
            'page'  => $page,
            'controller' => 'account',
            'action'     => 'delete'
        );
        $paginator = $this->setPaginator($paginatorOption);

        $this->view()->assign(array(
            'paginator' => $paginator,
            'users'     => $users,
        ));
        $this->view()->assign('paginator', $paginator);
    }

    public function clearAction()
    {
        $id = $this->params('id', '');
        $errorMessage = '';
        $uids = array();
        $model = $this->getModel('account');
        if ($id) {
            $row = $model->find($id, 'id');
            if ($row && !$row->visible) {
                $uids[] = $row->id;
            }else {
                $errorMessage = __('Error occur');
            }
        }else {
            $select = $model->select(array('visible' => 0));
            $rowset = $model->selectWith($select);
            foreach ($rowset as $row) {
                $uids[] = $row->id;
            }
        }

        // Delete account
        $model->delete(array('id' => $uids));

        // Delete  baser profile
        $baseProfileModel = $this->getModel('profile');
        //$select = $baseProfileModel->select(array('id' => $uids))->columns(array())
    }

    /**
     * Ban a user
     *
     * @param $ids
     */
    protected function banUser($ids)
    {
        $model = $this->getModel('account');
        foreach ($ids as $id) {
            $row = $model->find($id, 'id');
            if ($row && !$row->ban) {
                $row->ban = 1;
                $row->save();
            }
        }
    }

    /**
     * Delete a user
     *
     * @param $ids
     */
    protected function deleteUser($ids)
    {
        $model = $this->getModel('account');
        foreach ($ids as $id) {
            $row = $model->find($id, 'id');
            if ($row && $row->visible) {
                $row->visible = 0;
                $row->save();
            }
        }
    }

    protected function activeUser($ids)
    {
        $model = $this->getModel('account');
        foreach ($ids as $id) {
            $row = $model->find($id, 'id');
            if ($row && !$row->active) {
                $row->active = 1;
                $row->save();
            }
        }
    }

    protected function unbanUser($ids)
    {
        $model = $this->getModel('account');
        foreach ($ids as $id) {
            $row = $model->find($id, 'id');
            if ($row && $row->ban) {
                $row->ban = 0;
                $row->save();
            }
        }
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

    /**
     * Set paginator
     *
     * @param $option
     * @return \Pi\Paginator\Paginator
     */
    protected function setPaginator($option)
    {
        $paginator = Paginator::factory(intval($option['count']));
        $paginator->setItemCountPerPage($option['limit']);
        $paginator->setCurrentPageNumber($option['page']);
        $paginator->setUrlOptions(array(
            // Use router to build URL for each page
            'pageParam'     => 'p',
            'totalParam'    => 't',
            'router'        => $this->getEvent()->getRouter(),
            'route'         => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            'params'        => array(
                'module'        => $this->getModule(),
                'controller'    => $option['controller'],
                'action'        => $option['action'],
            ),
        ));

        return $paginator;
    }
}