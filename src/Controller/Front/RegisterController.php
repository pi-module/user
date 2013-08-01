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
        $groups = array();
        foreach ($configs['category'] as $category) {
            $groups[$category['name']] = array(
                'label'    => $category['title'],
                'elements' => array(),
            );
        }

        foreach ($configs['item'] as $item) {
            $groups[$item['category']]['elements'][] = $item['element']['name'];
        }

        $action = $this->url('default', array('controller' => 'register', 'action' => 'index'));
        $form = $this->getForm('register', $configs['item'], $action);
        $form->setGroups($groups);

        if ($this->request->isPost()) {
            // Process register request
            $post = $this->request->getPost();

            foreach ($configs['item'] as $item) {
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
                $values['status'] = 0;

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
                    if (0 == $userRow->status || 2 == $userRow->status) {
                        // Active user
                        $userRow->status = $userRow->status + 1;
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
    }

    /**
     * Last step register
     * Complete profile information
     */
    public function completeAction()
    {
        // Get custom configs from config file.
        $configFile = sprintf('%s/extra/%s/config/custom.register.complete.form.php', Pi::path('usr'), $this->getModule());
        $configs = include $configFile;
        if (!empty($configs)) {

            // Get group.
            $groups = array();
            foreach ($configs['category'] as $category) {
                $groups[$category['name']] = array(
                    'label'    => $category['title'],
                    'elements' => array(),
                );
            }

            foreach ($configs['item'] as $item) {
                $groups[$item['category']]['elements'][] = $item['element']['name'];
            }

            $action = $this->url('default', array('controller' => 'register', 'action' => 'complete'));
            $form = $this->getForm('registerComplete', $configs['item'], $action);
            $form->setGroups($groups);
        }

        if ($this->request->isPost()) {
            $post = $this->request->getPost();

            // Get custom filter
            foreach ($configs['item'] as $item) {
                $filters[] = $item['filter'];
            }

            $form->setInputFilter(new CustomFilter($filters));
            $form->setData($post);
            if ($form->isValid()) {
                $values = $form->getData();

                $categoryMap = $this->getCategoryMap($configs['item']);

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
     * Get category map from custom config
     *
     * @param $items
     * @return array
     */
    protected function getCategoryMap($items)
    {
        foreach ($items as $item) {
            $category[$item['element']['name']] = $item['category'];
        }

        return $category;
    }

    protected function getTableColumsName($tableName)
    {

        $row = $this->getModel($tableName)->getColumnsName();
    }

    /**
     * Just for test.
     *
     */
    public  function testAction()
    {
////        $config = Pi::service('registry')->config->read('user', 'general');
////        d($config);
////        $id = Pi::service('user')->getUser();
////        d($id);
//        //$id = Pi::service('user');
////        d($id);
//        //d($this->getSmtpOption());
//        //$this->view()->setTemplate(false);
//        $row = $this->getModel('profile')->getColumnsName();
//
//        foreach ($row as $r) {
//            d($r['name']);
//        }
//
//        d($_POST['bit']);
    }
}
