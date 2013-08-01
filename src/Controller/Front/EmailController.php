<?php
/**
 * User system email controller
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
use Module\User\Form\EmailForm;
use Module\User\Form\EmailFilter;

/**
 * Feature list:
 * 1. Change email
 * 2. Send verify email code
 */
class EmailController extends ActionController
{
    /**
     * 1. Display change email form
     * 2. Send verify code
     * 3. Check verify code
     * 4. change email complete
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

        $email = Pi::service('user')->getUser()->email;

        // Set display account message
        $user = array(
            __('Username')  => $identity,
            __('Email')     => $email,
        );

        $form = new EmailForm('email-change');
        if ($this->request->isPost()) {
            // Process new email
            $data = $this->request->getPost();
            $form->setInputFilter(new EmailFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                // Generate token
                $uid    = Pi::service('user')->getUser()->id;
                $result = Pi::service('api')->user->setToken($uid, 'email');

                if (!$result['code']) {
                    $this->jump($this->url('default', array('controller' => 'email', 'action' => 'index')), __('Change email error'), 3);
                }

                // Send verify email
                $to = $values['email-new'];
                $baseLocation = Pi::host()->get('baseLocation');
                $link = $baseLocation . $this->url('default', array('controller' => 'email', 'action' => 'process', 'id'=> md5($uid), 'token' => $result['code'], 'new' => urlencode($values['email-new'])));
                $this->send($to, $link, $identity);

                $this->redirect('default',array('controller' => 'email', 'action' => 'sendComplete'));

            } else {
                $form->setData(array('identity' => $identity));
                $message = __('Invalid data, please check and re-submit.');
            }

        } else {
            $form->setData(array('identity' => $identity));
            $message = '';
        }

        $title = __('Change Email');
        $this->view()->assign(array(
            'title'   => $title,
            'form'    => $form,
            'message' => $message,
        ));
    }

    /**
     * 1. Verify token
     * 2. Change email
     * 3. Display result message
     */
    public function processAction()
    {
        $key      = $this->params('id', '');
        $token    = $this->params('token', '');
        $newEmail = urldecode($this->params('new', ''));

        $data = array(
            'message' => __('Change email fail!'),
            'status'  => 0,
        );

        // Assign title to template
        $this->view()->assign('title', __('Change email'));

        // Verify link invalid
        if (!key || !$token || !$newEmail) {
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
                    // Change email
                    $userRow->email = $newEmail;
                    $userRow->save();
                }

                // Delete change email verify link
                $this->getModel('token')->delete(array('id' => $rowset->id));

                $data['message'] = __('Change success');
                $data['status']  = 1;

                $this->view()->assign('data', $data);
            }
        }
    }

    /**
     * Display send verify mail message
     *
     */
    public function sendCompleteAction()
    {
        $changeEmailLink = $this->url('default', array('controller' => 'email', 'action' => 'index' ));
        $this->view()->assign('changeEmailLink', $changeEmailLink);
        $this->view()->setTemplate('change-email-success');
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
            'username'          => $username,
            'change_email_link' => $link,
            'sn'                => _date(),
        );

        // Load from HTML template
        $data = Pi::service('mail')->template('change-email-html', $params);
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
