<?php
/**
 * User module API class
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

namespace Module\User\Api;

use Pi;
use Pi\Application\AbstractApi;
use Pi\Db\RowGateway\RowGateway;

class Api extends AbstractApi
{
    /**
     * Current module name
     *
     * @var string
     */
    protected $module = 'user';

    /**
     * Account columns
     *
     * @var array
     */
    protected $accountColumns = array(
        'id',
        'identity',
        'email',
        'status',
        'credential',
        'salt',
    );

    /**
     * Base profile columns
     *
     * @var array
     */
    protected $baseProfileColumns = array(
        'uid',
        'name',
        'birthday',
        'gender',
        'avatar',
        'bio',
        'location',
    );

    /**
     * Education columns array
     *
     * @var array
     */
    protected $educationColumns = array(
        'uid',
        'start_date',
        'end_date',
        'school',
        'degree',
        'major',
        'class',
    );

    /**
     * Role columns
     *
     * @var array
     */
    protected $roleColumns = array(
        'uid',
        'role',
    );

    /**
     * Canonize register information
     *
     * @param $data
     * @return array
     */
    protected  function canonize($data)
    {
        $account       = array();
        $role          = array();
        $baseProfile   = array();
        $extendProfile = array();
        $userData      = array();

        foreach (array_keys($data) as $key) {
            // Role
            if (in_array($key, $this->roleColumns)) {
                $role[$key] = $data[$key];

            }elseif (in_array($key, $this->accountColumns)) {
                // Account
                $account[$key] = $data[$key];
            }elseif (in_array($key, $this->baseProfileColumns)) {
                // Base profile
                $baseProfile[$key] = $data[$key];
            }else {
                // Extend profile
                $extendProfile[$key] = $data[$key];
            }
        }

        return array($account, $role, $baseProfile, $extendProfile);
    }

    /**
     * Add a new user with account and profile
     *
     * @param array $data
     * @return uid, status, message
     */
    public function addUser($data)
    {
        $return = array(
            'status'  => 0,
            'message' => '',
            'id'      => 0,
        );

        list($account, $role, $baseProfile, $extendProfile) = $this->canonize($data);
        // Add account.
        $uid = empty($account) ? 0 : $this->addAccount($account);
        if (!$uid) {
            $return['message'] = sprintf('User account "%s" is not created.', $data['identity']);
            return $return;
        }

        // Add user data
        $status = $this->addUserData($uid);
        if (!$status) {
            $return['message'] = sprintf('User account "%s" data is not created.', $data['identity']);
            return $return;
        }

        // Add role
        if (!empty($role['role'])) {
            $status = $this->setUserRole($uid, $role['role']);
            if (!$status) {
                $return['message'] = sprintf('User Role "%s" is not created.', $uid);
                return $return;
            }
        }

        // Add base profile
        if (!empty($baseProfile)) {
            $status = $this->addBaseProfile($baseProfile);
            if (!$status) {
                $return['message'] = sprintf('User "%s" base profile is not created.', $uid);
                return $return;
            }
        }

        // Add extend profile
        if (!empty($extendProfile)) {
            $status = $this->addExtendProfile($data);
            if (!$status) {
                $return['message'] = sprintf('User "%s" base profile is not created.', $uid);
                return $return;
            }
        }

        $return['status'] = 1;
        $return['id']     = $uid;
        return $return;
    }

    /**
     * set token for verify
     *
     * @param $uid user identify
     * @param $type
     */
    public function setToken($uid, $type)
    {
        $return = array(
            'status'  => 0,
            'message' => '',
            'code'    => '',
        );

        if (!$uid || !$type) {
            $return['message'] = 'Params uid or type is null';
        }

        $tokenModel = Pi::model('token', $this->module);
        $where = array(
            'uid'  => $uid,
            'type' => $type,
        );

        $select   = $tokenModel->select()->where($where);
        $tokenRow = $tokenModel->selectWith($select)->current();

        $time = time();
        $code = md5(sprintf('%s%s%s', $uid, $type, $time));

        if (!$tokenRow->id) {
            // Insert a new data
            $tokenRow = $tokenModel->createRow(array(
                'uid'  => $uid,
                'type' => $type,
                'code' => $code,
                'time' => $time,
            ));
        } else {
            // Update
            $tokenRow = Pi::model('token', $this->module)->find($tokenRow->id, 'id');
            $tokenRow->assign(array(
                'uid'  => $uid,
                'type' => $type,
                'code' => $code,
                'time' => $time,
            ));
        }

        try {
            $tokenRow->save();
        } catch(\Exception $e) {
            $return['message'] = 'Set token failed';
            return $return;
        }

        $return['message'] = 'success';
        $return['status']  = 1;
        $return['code']    = $code;

        return $return;
    }

    /**
     * Transform credential
     *
     * @param $credential
     * @param $salt
     * @return string
     */
    public function transformCredential($credential, $salt)
    {
        $credential = md5(sprintf('%s%s%s', $salt, $credential, Pi::config('salt')));
        return $credential;
    }

    /**
     * Get smtp option from smtp config
     *
     * @return smtp config
     */
    public function getSmtpOption()
    {
        $configFile = sprintf('%s/extra/%s/config/smtp.config.php', Pi::path('usr'), $this->getModule());
        $option = include $configFile;

        return $option;
    }

    /**
     * Add account data to account table
     *
     * @param $data
     * @return bool
     */
    protected function addAccount($data)
    {
        $row = Pi::model('account', $this->module)->prepare($data);
        $row->save();
        if ($row->id) {
            return $row->id;
        }

        return false;
    }

    /**
     * Add base profile data
     *
     * @param $data
     */
    protected function addBaseProfile($data)
    {

    }

    /**
     * Add extend profile data
     *
     * @param $data
     */
    protected function addExtendProfile($data)
    {

    }

    /**
     * Add user data
     *
     * @param $uid
     * @return bool
     */
    protected function addUserData($uid)
    {
        if (!$uid) {
            return false;
        }

        $model = Pi::model('user_data', $this->module);
        $userDataRow = $model->find($uid, 'uid');
        if (!$userDataRow) {
            $service = Pi::engine()->application()->getRequest()->getServer();
            $userDataRow = $model->createRow(array(
                'uid' => $uid,
                'time_register' => time(),
                'register_ip'   => $service['REMOTE_ADDR'],
            ));

            try {
                $userDataRow->save();

            } catch(\Exception $e) {
                return false;
            }
            return true;
        } else {
            return false;
        }

    }


    /**
     * Set user role
     *
     * @param $uid
     * @param $role
     * @return bool
     */
    protected function setUserRole($uid, $role)
    {
        return $this->setRole($uid, $role, 'user');
    }

    /**
     * Set back office role
     *
     * @param $uid
     * @param $role
     */
    protected function setStaffRole($uid, $role)
    {

    }

    /**
     * Set role
     *
     * @param $uid
     * @param $role
     * @param string $type
     * @return bool
     */
    protected function setRole($uid, $role, $type = 'user')
    {
        $model = ('staff' == $type) ? Pi::model('staff', $this->module) : Pi::model('role', $this->module);
        if (empty($role)) {
            $model->delete(array('uid' => $uid));
            return true;
        }
        $roleRow = $model->find($uid, 'uid');
        if (!$roleRow) {
            $roleRow = $model->createRow(array(
                'uid'  => $uid,
                'role' => $role,
            ));
        } else {
            $roleRow->assign(array(
                'uid'  => $uid,
                'role' => $role,
            ));
        }

        try {
            $roleRow->save();

        } catch(\Exception $e) {
            return false;
        }
        return true;
    }
}