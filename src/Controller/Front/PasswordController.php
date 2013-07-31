<?php
/**
 * User system password controller
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
use Module\User\Form\PasswordForm;
use Module\User\Form\PasswordFilter;
use Module\User\Form\FindPasswordForm;
use Module\User\Form\FindPasswordFilter;

/**
 * Feature list:
 * 1. Change passwrod
 * 2. Find password
 */
class PasswordController extends ActionController
{
    /**
     * Change password for current user
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

        $form = new PasswordForm('password-change');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new PasswordFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();

                // Set new password
                $row = $this->getModel('account')->find($identity, 'identity');
                $credentialNew = Pi::service('api')->user->transformCredential($values['credential-new'], $row->salt);
                $row->credential = $credentialNew;
                $row->save();

                if ($row->id) {
                    $message = __('Password changed successfully.');
                    $this->redirect()->toRoute('', array('controller' => 'account', 'action' => 'index'));
                    return;
                } else {
                    $message = __('Password not changed.');
                }
            } else {
                $message = __('Invalid data, please check and re-submit.');
            }
        } else {
            $form->setData(array('identity' => $identity));
            $message = '';
        }

        $title = __('Change password');
        $this->view()->assign(array(
            'title'     => $title,
            'form'      => $form,
            'message'   => $message,
        ));
    }

    /**
     * 1. Display find password form
     * 2. Verify email
     * 3. Send verify email
     *
     */
    public function findAction()
    {
        $title = __('Find password');
        $this->view()->assign(array(
            'title' => $title,
        ));

        $form = new FindPasswordForm('find-password');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new FindPasswordFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $value = $form->getData();

                // Check email is  exist
                $userRow = $this->getModel('account')->find($value['email'], 'email');
                if (!$userRow) {
                    $this->view()->assign(array(
                        'message' => __('Find password fail, please try again later'),
                        'form'    => $form,
                    ));
                    return;
                }

                // Send verify email
                $result = Pi::service('api')->user->setToken($userRow->id, 'find-password');
                if (!$result['code']) {
                    $this->view()->assign(array(
                        'message' => __('Find password fail, please try again later'),
                        'form'    => $form,
                    ));
                    return;
                }

                $to  = $value['email'];
                $uid = $userRow->id;
                $baseLocation = Pi::host()->get('baseLocation');
                $link = $baseLocation . $this->url('default', array('controller' => 'password', 'action' => 'process', 'id'=> md5($uid), 'token' => $result['code']));
                $this->send($to, $link, $userRow->identity);
                $this->redirect('default', array('controller' => 'password', 'action' => 'sendComplete'));
            }
        }

        $this->view()->assign('form', $form);
        $this->view()->setTemplate('password-find');
    }

    public function processAction()
    {
        $key      = $this->params('id', '');
        $token    = $this->params('token', '');

        $data = array(
            'status'  => 0,
        );

        // Assign title to template
        $this->view()->assign('title', __('Find password'));
        // Verify link invalid
        if (!key || !$token) {
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

                // Valid verify link
                if ($current < $expire) {
                    // Display reset password form
                    $identity = $userRow->identity;
                    $form     = new PasswordForm('find-password', 'find');
                    if ($this->request->isPost()) {
                        $data = $this->request->getPost();
                        $form->setInputFilter(new PasswordFilter('find'));
                        $form->setData($data);

                        if ($form->isValid()) {
                            $values = $form->getData();
                            $salt = uniqid(mt_rand(), 1);
                            $credential = md5(sprintf('%s%s%s', $salt, $values['credential-new'], Pi::config('salt')));

                            $userRow->salt       = $salt;
                            $userRow->credential = $credential;
                            $userRow->save();

                            // Delete find password verify token
                            $this->getModel('token')->delete(array('id' => $rowset->id));

                            $data['status'] = 1;
                        } else {
                            $data['status'] = 1;
                            $this->view()->assign(array(
                                'form'    => $form,
                                'message' => __('Input is invalid, please try again later'),
                            ));
                        }

                    } else {
                        $form->setData(array('identity', $identity));
                        $this->view()->assign('form', $form);
                        $data['status'] = 1;
                    }
                }
            }
        }

        $this->view()->assign(array(
            'data' => $data,
        ));
    }

    /**
     * Display find password verify mail status
     *
     */
    public function sendCompleteAction()
    {
        $title = __('Find password');
        $this->view()->assign(array(
            'title' => $title,
        ));

        $this->view()->setTemplate('password-send-complete');
    }

    /**
     * Send change email verify  mail
     *
     * @param $to User email
     * @param $link verify  link
     */
    protected function send($to, $link, $username)
    {
        $option = Pi::service('api')->user->getSmtpOption();
        $params = array(
            'username'           => $username,
            'find_password_link' => $link,
            'sn'                 => _date(),
        );

        // Load from HTML template
        $data = Pi::service('mail')->template('find-password-mail-html', $params);
        // Set subject and body
        $subject = $data['subject'];
        $body = $data['body'];
        $type = $data['format'];
        $message = Pi::service('mail')->message($subject, $body, $type);
        $message->addTo($to);

        $transport = Pi::service('mail')->loadTransport('smtp', $option);
        $transport->send($message);
    }
}
