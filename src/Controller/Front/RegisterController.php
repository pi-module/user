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

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Acl\Acl;
use Module\User\Form\RegisterForm;
use Module\User\Form\RegisterFilter;


/**
 * Register controller for user
 *
 * Tasks:
 *
 * 1. Register form
 * 2. Send email
 * 3. Add a new user
 * 4. Complete register
 *
 */
class RegisterController extends ActionController
{
    /**
     * Display register form
     *
     * @return array|void
     */
    public function indexAction()
    {
        list($fields, $filters) = $this->canonizeForm('register.form');
        $form = $this->getForm($fields);

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setInputFilter(new RegisterFilter($filters));
            $form->setData($post);
            if ($form->isValid()) {
                $values     = $form->getData();
                $salt       = Pi::api('user', 'password')->createSalt();
                $credential = Pi::api('user', 'password')
                    ->transformCredential($values['credential'], $salt);

                $data = array(
                    'identity'   => $values['identity'],
                    'name'       => $values['name'],
                    'email'      => $values['email'],
                    'salt'       => $values['salt'],
                    'credential' => $values['credential'],
                );

                $uid = Pi::api('user', 'user')->addUser($data);
                // Set role
                $role = Acl::MEMBER;
                // To to
                // Insert role




            }

        }

        $this->view()->assign(array(
            'form' => $form,
        ));
    }

    /**
     * Get register form
     *
     * @return \Module\User\Form\RegisterForm
     */
    protected function getForm($fields)
    {
        $form = new RegisterForm('register', $fields);

        $form->setAttribute(
            'action',
            $this->url('', array('action' => 'index'))
        );

        return $form;
    }

    /**
     * Canonize data to element
     *
     * @param $data
     * @return array
     */
    protected function canonizeForm($file)
    {
        $elements = array();
        $filters  = array();

        $configFile = sprintf(
            '%s/extra/%s/config/%s.php',
            Pi::path('usr'),
            $this->getModule(),
            $file
        );

        if (!file_exists($configFile)) {
            return;
        }
        $data = include $configFile;

        foreach ($data as $value) {
            if ($value['element']) {
                $elements[] = $value['element'];
            }

            if ($value['filter']) {
                $filters[] = $value['filter'];
            }
        }

        return array($elements, $filters);
    }

    public function testAction()
    {
        $salt = Pi::api('user', 'password')->createSalt();
        d($salt);
        $this->view()->setTemplate(false);
    }
}
