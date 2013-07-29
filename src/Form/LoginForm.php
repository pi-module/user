<?php
/**
 * Login form
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
 * @subpackage      Form
 * @version         $Id$
 */

namespace Module\User\Form;

use Pi;
use Pi\Form\Form as BaseForm;

class LoginForm extends BaseForm
{
    public function init()
    {
        // Get config data.
        $config = Pi::service('registry')->config->read('user', 'general');

        $this->add(array(
            'name'          => 'identity',
            'options'       => array(
                'label' => __('Username'),
            ),
            'attributes'    => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'name'          => 'credential',
            'options'       => array(
                'label' => __('Password'),
            ),
            'attributes'    => array(
                'type'  => 'password',
            )
        ));

        $this->add(array(
            'name'          => 'rememberme',
            'type'          => 'checkbox',
            'options'       => array(
                'label' => __('Remember me'),
            ),
            'attributes'    => array(
                'value'         => '1',
                'description'   => __('Keep me logged in.')
            )
        ));


        if ($config['login_captcha']) {
            $this->add(array(
                'name'          => 'captcha',
                'type'          => 'captcha',
                'options'       => array(
                    'label'     => __('Please type the word.'),
                    'separator'         => '<br />',
                )
            ));
        }

        $this->add(array(
            'name'  => 'security',
            'type'  => 'csrf',
        ));

        $request = Pi::engine()->application()->getRequest();
        $redirect = $request->getQuery('redirect');
        if (null === $redirect) {
            $redirect = $request->getServer('HTTP_REFERER') ?: $request->getRequestUri();
        }
        $redirect = $redirect ? urlencode($redirect) : '';
        $this->add(array(
            'name'  => 'redirect',
            'type'  => 'hidden',
            'attributes'    => array(
                'value' => $redirect,
            ),
        ));

        $this->add(array(
            'name'          => 'submit',
            'attributes'    => array(
                'type'  => 'submit',
                'value' => __('Login'),
                'class' => 'btn',
            )
        ));
    }
}