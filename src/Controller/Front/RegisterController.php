<?php
/**
 * User module register controller
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
use Pi;
use Pi\Acl\Acl;
use Module\User\Form\CustomForm;
use Module\User\Form\CustomFilter;
/**
 * Register controller for user.
 */
class RegisterController extends ActionController
{
    /**
     * Register.
     *
     * @return array|void
     */
    public function indexAction()
    {
        // Get custom configs from config file.
        $configFile = sprintf('%s/extra/%s/config/custom.register.form.php', Pi::path('usr'), $this->getModule());
        $configs = include $configFile;

        $baseColumns = $this->getBaseFieldColumns();
        $formElement = array();
        foreach ($configs as $config) {
            if (isset($config['name'])) {
                $row = $this->getModel('profile_config')->find($config['name'], 'name');
                if ($row) {
                    $formElement[] = array(
                        'element' => unserialize($row->element),
                        'filter'  => $row->filter ? unserialize($row->filter) : array(),

                    );
                }
            } elseif (isset($config['element']) && isset($config['filter'])) {
                if (in_array($config['element']['name'], $baseColumns)) {
                    $formElement[] = $config;
                }
            }
        }

        if (!empty($formElement)) {
            $action = $this->url('default', array('controller' => 'register', 'action' => 'index'));
            $form = $this->getForm('register', $formElement, $action);
        }

        if ($this->request->isPost()) {
            // Process register request
            $post = $this->request->getPost();

            foreach ($formElement as $item) {
                $filters[] = $item['filter'];
            }

            $form->setInputFilter(new CustomFilter($filters));
            $form->setData($post);

            if ($form->isValid()) {
                $values = $form->getData();

                // Unset useless data
                if ($values['credential-confirm']) {
                    unset($values['credential-confirm']);
                }
                if ($values['captcha']) {
                    unset($values['captcha']);
                }
                if ($values['submit']) {
                    unset($values['submit']);
                }

                $values['role'] = Acl::MEMBER;

                // Create user.
                $result = Pi::service('api')->user->addUser($values);

                if (!$result['id']) {
                    $this->jump($this->url('default', array('controller' => 'register', 'action' => 'index')), __('Register error'), 3);
                }

                // Set token
                $uid = $result['id'];
                unset($result);
                $result = Pi::service('api')->user->setToken($uid, 'activity');

                if (!$result['code']) {
                    $this->jump($this->url('default', array('controller' => 'register', 'action' => 'index')), __('Register error'), 3);
                }

                // Send active email
                $to           = $values['email'];
                $baseLocation = Pi::host()->get('baseLocation');
                $link = $baseLocation . $this->url('default', array('controller' => 'register', 'action' => 'activity', 'id'=> md5($uid), 'token' => $result['code']));
                $this->send($to, $link, $values['username']);

                $this->view()->setTemplate('register-success');
                return;
            }
        }
        $this->view()->assign('form', $form);
    }

    /**
     * Activity user account.
     */
    public function activityAction()
    {
        $key   = $this->params('id', '');
        $token = $this->params('token', '');

        $data = array(
            'message' => __('Activity fail!'),
            'status'  => 0,
        );

        // Activity link invalid
        if (!$key || !$token) {
            $this->view()->assign('data', $data);
            return;
        }

        $rowset = $this->getModel('token')->find($token, 'code');
        if ($rowset) {
            $hashUid = md5($rowset->uid);
            $userRow = $this->getModel('account')->find($rowset->uid, 'id');

            if ($userRow && $hashUid == $key) {
                $expire  = $rowset->time + 24 * 3600;
                $current = time();
                if ($current < $expire) {
                    // User not activity.
                    if (0 == $userRow->active) {
                        // Active user
                        $userRow->active = 1;
                        $userRow->save();
                    }

                    // Delete activity token.
                    $this->getModel('token')->delete(array('id' => $rowset->id));

                    $data['message'] = __('Activity success');
                    $data['status']  = 1;
                    $this->view()->assign('data', $data);
                }
            }
        }

        $this->view()->assign('data', $data);
    }

    /**
     * Last step register
     * Complete profile information
     */
    public function completeAction()
    {
        $uid = Pi::service('user')->getUser()->id;
        $redirect = Pi::engine()->application()->getRequest()->getRequestUri();
        if (!$uid) {
            $this->redirect('default', array('controller' => 'login', 'action' => 'index', 'redirect' => $redirect));
        }

        $configFile = sprintf('%s/extra/%s/config/custom.register.complete.form.php', Pi::path('usr'), $this->getModule());
        $configs = include $configFile;
        $baseColumns = $this->getBaseFieldColumns();
        $formElement = array();
        foreach ($configs as $config) {
            if (isset($config['name'])) {
                $row = $this->getModel('profile_config')->find($config['name'], 'name');
                if ($row) {
                    $formElement[] = array(
                        'element' => unserialize($row->element),
                        'filter'  => $row->filter ? unserialize($row->filter) : array(),

                    );
                }
            } elseif (isset($config['element']) && isset($config['filter'])) {
                if (in_array($config['element']['name'], $baseColumns)) {
                    $formElement[] = $config;
                }
            }
        }

        if (!empty($formElement)) {
            $action = $this->url('default', array('controller' => 'register', 'action' => 'complete'));
            $form = $this->getForm('register.complete', $formElement, $action);
        }

        if ($this->request->isPost()) {
            $post = $this->request->getPost();

            // Get custom filter
            foreach ($formElement as $item) {
                $filters[] = $item['filter'];
            }

            $form->setInputFilter(new CustomFilter($filters));
            $form->setData($post);
            if ($form->isValid()) {
                $values = $form->getData();
                unset($values['submit']);
                // Canonize form data
                list(, , $profile, $education, $extendProfile) = Pi::service('api')->user->canonize($values);
                if (!empty($profile)) {
                    $profile['uid'] = $uid;

                    // Transform sting to int
                    if (isset($profile['birthday'])) {
                        $profile['birthday'] = strtotime($profile['birthday']);
                    }
                    $this->setProfile($profile);
                }

                if (!empty($education)) {
                    $education['uid'] = $uid;

                    // Transform string to int
                    if (isset($education['start_date'])) {
                        $education['start_date'] = strtotime($education['start_date']);
                    }
                    if (isset($education['end_date'])) {
                        $education['end_date'] = strtotime($education['end_date']);
                    }

                    $this->setEducation($education);
                }

                if (!empty($extendProfile)) {
                    $extendProfile['uid'] = $uid;
                    $this->setExtendProfile($extendProfile);
                }

                // Set user data
                $userDataRow = $this->getModel('user_data')->find($uid, 'uid');
                if ($userDataRow) {
                    $userDataRow->time_update = time();
                    $userDataRow->save();
                }

                // Redirect profile page
                //$this->redirect()->toUrl($this->url('default', array('controller' => 'profile', 'action' =>'index')));
                //$this->redirect()->toRoute();
                $this->jump(array('controller' => 'profile', 'action' => 'index'), __('Complete successfully'));
            }
        }

        $this->view()->assign(array(
            'form' => $form,
        ));
    }

    /**
     * Generate register form
     *
     * @param $configs
     * @return \Module\User\Form\CustomForm
     */
    protected function getForm($name, $configs, $action)
    {
        $form = new CustomForm($name, $configs);
        $form->setAttribute('action', $action);
        return $form;
    }

    /**
     * Send activity mail
     *
     * @param $to User email
     * @param $link activity link
     */
    protected function send($to, $link, $username)
    {
        $option = Pi::service('api')->user->getSmtpOption();

        $params = array(
            'username'      => $username,
            'activity_link' => $link,
            'sn'            => _date(),
        );

        // Load from HTML template
        $data = Pi::service('mail')->template('activity-mail-html', $params);
        // Set subject and body
        $subject = $data['subject'];
        $body = $data['body'];
        $type = $data['format'];
        $message = Pi::service('mail')->message($subject, $body, $type);
        $message->addTo($to);

        $transport = Pi::service('mail')->loadTransport('smtp', $option);
        $transport->send($message);
    }

    /**
     * Set profile for register
     *
     * @param $profile
     * @return int
     */
    protected function setProfile($profile)
    {
        if (empty($profile)) {
            return 0;
        }

        $profileModel = $this->getModel('profile');
        $row = $profileModel->find($profile['uid'], 'uid');
        if (!$row) {
            $row = $profileModel->createRow($profile);
            $row->save();
        } else {
            foreach ($profile as $key => $value) {
                $row->$key = $value;
            }
            $row->save();
        }

        return $row->id ?: 0;
    }

    /**
     * Set extend profile for register
     *
     * @param $exProfile
     * @return bool
     */
    protected function setExtendProfile($exProfile)
    {

        if (empty($exProfile)) {
            return true;
        }

        $uid = $exProfile['uid'];
        unset($exProfile['uid']);

        $exProfileModel = $this->getModel('extend_profile');

        foreach ($exProfile as $name => $value) {
            $select = $exProfileModel->select()->where(array('uid' => $uid, 'name' => $name));
            $row = $exProfileModel->selectWith($select)->current();

            if ($row) {
                $exProfileModel->update(
                    array('name' => $name, 'value' => $value),
                    array('id'=> $row->id)
                );
            } else {
                $row = $exProfileModel->createRow(array(
                    'uid' => $uid,
                    'name' => $name,
                    'value' => $value
                ));
                $row->save();
            }
        }
        return false;
    }

    /**
     * Set education for register
     *
     * @param $education
     * @return int
     */
    protected function setEducation($education)
    {
        if (empty($education)) {
            return 0;
        }

        $educationModel = $this->getModel('education');
        $row = $educationModel->find($education['uid'], 'uid');

        if (!$row) {
            $row = $educationModel->createRow($education);
            $row->save();
        } else {
            foreach ($education as $key => $value) {
                $row->$key = $value;
            }
            $row->save();
        }

        return $row->id ?: 0;
    }

    /**
     * Get base field columns from config file
     * @return mixed
     */
    protected function getBaseFieldColumns()
    {
        $file = sprintf('%s/extra/%s/config/base.field.columns.php', Pi::path('usr'), $this->getModule());
        $columns = include $file;

        return $columns;
    }
}
