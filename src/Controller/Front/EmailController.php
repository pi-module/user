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
                $uid = Pi::service('user')->getUser()->id
                $result = Pi::service('api')->user->setToken($uid, 'activity');


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

    public function sendAction()
    {
        $title = __('Send activation');
        $this->view()->assign(array(
            'title' => $title,
        ));
        $this->view()->setTemplate('email-send');
    }
}
