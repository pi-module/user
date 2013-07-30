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

    public function findAction()
    {
        $title = __('Find password');
        $this->view()->assign(array(
            'title' => $title,
        ));
        $this->view()->setTemplate('password-find');
    }

    public function testAction()
    {
        $user = Pi::service('user')->getUser()->identity;
        d($user);
        $this->view()->setTemplate(false);
    }
}
