<?php
/**
 * User module Login controller
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
use Module\User\Form\LoginForm;
use Module\User\Form\LoginFilter;
use Pi;
use Pi\Acl\Acl;
use Module\User\Authentication\Adapter\DbTable;

/**
 * Login controller for user.
 */
class LoginController extends ActionController
{
    /**
     * Login action.
     *
     * @return array|void
     */
    public function indexAction()
    {
        // If already logged in, redirect home page
        if (Pi::service('user')->getuser()->id) {
            $this->jump(array('route' => 'home'), __('You already logged in. Now go back to homepage.'));
        }

        // Display login Form or process login request.
        $form = $this->getForm();
        if ($this->request->isPost()) {
            if (!$this->request->isPost()) {
                $this->jump(array('action' => 'index'), __('Invalid request'));
                return;
            }

            // Get user submit information
            $post = $this->request->getPost();
            $form->setData($post);
            $form->setInputFilter(new LoginFilter());

            if (!$form->isValid()) {
                return;
            }


            $value = $form->getData();
            $identity = $value['identity'];
            $credential = $value['credential'];

            // Authentication identity and credential
            $adapter = new DbTable;
            $result = Pi::service('authentication')->authenticate($identity, $credential, $adapter);
            d($result);
        }



        // Display login Form
        $redirect = $this->params('redirect');
        if (null === $redirect) {
            $redirect = $this->request->getServer('HTTP_REFERER');
        }
        if (null !== $redirect) {
            $redirect = $redirect ? urlencode($redirect) : '';
            $form->setData(array('redirect' => $redirect));
        }

        // Allocation form to template
        $this->view()->assign('form', $form);

    }

    /**
     * Get login form
     *
     * @return \Module\User\Form\LoginForm
     */
    protected function getForm()
    {
        $form = new LoginForm('login');
        $form->setAttribute('action', $this->url('', array('controller' => 'login', 'action' => 'index')));
        return $form;
    }
}
