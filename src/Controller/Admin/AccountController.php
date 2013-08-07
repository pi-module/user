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
use Module\User\Form\EditRoleForm;
use Module\User\Form\EditRoleFilter;

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
     * Delete user
     *
     * @return \Zend\Http\Response
     */
    public function deleteAction()
    {
        $redirect = urldecode(_get('redirect'));
        $id       = _get('id');
        if ($redirect && $id) {
            $this->deleteUser(array($id));
            return $this->redirect()->toUrl($redirect);
        }
        $this->view()->setTemplate(false);
    }

    public function banAction()
    {
        $redirect = urldecode(_get('redirect'));
        $id       = _get('id');
        if ($redirect && $id) {
            $this->banUser(array($id));
            return $this->redirect()->toUrl($redirect);
        }
        $this->view()->setTemplate(false);
    }

    /**
     * List all deleted users
     */
    public function deletedAction()
    {
        $page = $this->params('p', 1);
        $limit = 5;
        $offset = (int) ($page -1) * $limit;
        $status = $this->params('status', 1);

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
            'action'     => 'delete'
        );
        $paginator = $this->setPaginator($paginatorOption);

        $this->view()->assign(array(
            'paginator' => $paginator,
            'users'     => $users,
            'status'    => $status,
        ));
        $this->view()->assign('paginator', $paginator);
    }

    public function clearAction()
    {
        $id = $this->params('id', '');
        $status = 1;

        // Get uids
        $uids = array();
        $model = $this->getModel('account');
        if ($id) {
            $row = $model->find($id, 'id');
            if ($row && !$row->visible) {
                $uids[] = $row->id;
            }else {
                $status = 0;
            }
        }else {
            $rowset = $model->select(array('visible' => 0));
            //$rowset = $model->selectWith($select);
            foreach ($rowset as $row) {
                $uids[] = $row->id;
            }
        }

        // Delete account
        $model->delete(array('id' => $uids));
        // Delete  base profile
        $rowsetProfile = $this->getModel('profile')->select(array('uid' => $uids));
        foreach ($rowsetProfile as $row) {
            try {
                $row->delete();
            } catch (\Exception $e) {
                $status = 0;
                $this->redirect()->toRoute('admin', array('controller' => 'account', 'action' => 'deleted', 'status' => $status));
            }
        }
        // Delete extend profile
        $rowsetExProfile = $this->getModel('extend_profile')->select(array('uid' => $uids));
        foreach ($rowsetExProfile as $row) {
            try {
                $row->delete();
            } catch (\Exception $e) {
                $status = 0;
                $this->redirect()->toRoute('admin', array('controller' => 'account', 'action' => 'deleted', 'status' => $status));
            }
        }
        // Delete user education
        $rowsetEducation = $this->getModel('education')->select(array('uid' => $uids));
        foreach ($rowsetEducation as $row) {
            try {
                $row->delete();
            } catch (\Exception $e) {
                $status = 0;
                $this->redirect()->toRoute('admin', array('controller' => 'account', 'action' => 'deleted', 'status' => $status));
            }
        }
        // Delete user data
        $rowsetUserData = $this->getModel('user_data')->select(array('uid' => $uids));
        foreach ($rowsetUserData as $row) {
            try {
                $row->delete();
            } catch (\Exception $e) {
                $status = 0;
                $this->redirect()->toRoute('admin', array('controller' => 'account', 'action' => 'deleted', 'status' => $status));
            }
        }
        // Delete user time line
        $rowsetTimeLine = $this->getModel('time_line')->select(array('uid' => $uids));
        foreach ($rowsetTimeLine as $row) {
            try {
                $row->delete();
            } catch (\Exception $e) {
                $status = 0;
                $this->redirect()->toRoute('admin', array('controller' => 'account', 'action' => 'deleted', 'status' => $status));
            }
        }
        // Delete data user privacy
        // Delete role
        $rowsetRole = $this->getModel('role')->select(array('uid' => $uids));
        foreach ($rowsetRole as $row) {
            try {
                $row->delete();
            } catch (\Exception $e) {
                $status = 0;
                $this->redirect()->toRoute('admin', array('controller' => 'account', 'action' => 'deleted', 'status' => $status));
            }
        }
        // Delete staff
        $rowsetStaff = $this->getModel('staff')->select(array('uid' => $uids));
        foreach ($rowsetStaff as $row) {
            try {
                $row->delete();
            } catch (\Exception $e) {
                $status = 0;
                $this->redirect()->toRoute('admin', array('controller' => 'account', 'action' => 'deleted', 'status' => $status));
            }
        }
        // Delete other relate user.
        $this->redirect()->toRoute('admin', array('controller' => 'account', 'action' => 'deleted', 'status' => $status));
    }

    public function roleAction()
    {
        $id = $this->params('id', '');
        $message = '';

        if (!$id) {
            $this->jump(array('action' => 'index'), __('The user is not found'));
        }

        $row = $this->getModel('account')->find($id);
        if (!$row) {
            $this->jump(array('action' => 'index'), __('The user is not found'));
        }

        $user['id'] = $id;

        $role = $this->getModel('role')->find($id, 'uid');
        if ($role) {
            $user['role'] = $role->role;
        }

        $roleStaff = $this->getModel('staff')->find($id, 'uid');
        if ($roleStaff) {
            $user['role_staff'] = $roleStaff->role;
        }

        $form = new EditRoleForm('account', $user);
        $form->setAttribute('action', $this->url('admin', array('controller' => 'account', 'action' => 'role')));

        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $filter = new EditRoleFilter();
            $form->setInputFilter($filter);
            $form->setData($data);

            if ($form->isValid()) {
                $values = $form->getData();

                // Update to role
                $rowset = $this->getModel('role')->find($values['id'], 'uid');
                if ($rowset) {
                    $rowset->role = $values['role'];
                    $rowset->save();

                    // Update to staff
                    $rowset = $this->getModel('staff')->find($values['id'], 'uid');
                    if ($rowset) {
                        $rowset->role = $values['role'];

                        $message = __('User role saved successfully');
                     }
                } else {
                    $message = __('User role not saved');
                }
            } else {
                $message = $form->getMessage() ?: __('Invalid data, please check and re-submit');
            }
        } else {
            $message = '';
        }

        $title = __('Edit member');
        $this->view()->assign(array(
            'title'   => $title,
            'form'    => $form,
            'message' => $message,
        ));
    }

//    public function editAction()
//    {
//        $id
//
//    }

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

    protected function setRoles($user)
    {
        $return = array(
            'status'  => 0,
            'message' => '',
        );
        
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