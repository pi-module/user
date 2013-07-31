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
        // Get module configs
        $configs = Pi::service('registry')->config->read('user', 'general');
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
                $this->view()->assign(array('form' => $form));
                return;
            }


            $value = $form->getData();
            $identity = $value['identity'];
            $credential = $value['credential'];

            // Change email to identity
            $pattern = '/^[0-9a-z_][_.0-9a-z-]{0,31}@([0-9a-z][0-9a-z-]{0,30}\.){1,4}[a-z]{2,4}$/i';
            if (preg_match($pattern, $identity)) {
                $userRow = $this->getModel('account')->find($identity, 'email');
                if ($userRow) {
                    $identity = $userRow->identity;
                } else {
                    $this->view()->assign(array(
                        'message' => __('Invalid credentials provided, please try again.'),
                        'form'    => $form
                    ));
                    return;
                }
            }

            // Authentication identity and credential
            $result = Pi::service('user')->authenticate($identity, $credential);

            // Identity or password wrong
            if (!$result->isValid()) {
                $this->view()->assign(array(
                    'message' => __('Invalid credentials provided, please try again.'),
                    'form'    => $form
                ));
                return;
            }

            /**
             * Authentication success
             * Check remember
             */
            if ($configs['rememberme'] && $value['rememberme']) {
                Pi::service('session')->manager()->rememberme($configs['rememberme'] * 86400);
            }

            // Bind account
            Pi::service('user')->bind($result->getIdentity(), 'identity');

            if (empty($values['redirect'])) {
                $redirect = array('route' => 'home');
            } else {
                $redirect = urldecode($values['redirect']);
            }
            $this->jump($redirect, __('You have logged in successfully.'), 2);
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
     * Logout action
     *
     */
    public function logoutAction()
    {
        Pi::service('session')->manager()->destroy();
        $this->jump(array('route' => 'home'), __('You logged out successfully. Now go back to homepage.'));
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
