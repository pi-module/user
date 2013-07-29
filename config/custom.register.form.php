<?php
/**
 * User module custom register form configs
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) http://www.eefocus.com
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Liu Chuang <liuchuag@eefocus.com>
 * @since           1.0
 * @package         Module\User
 */

$config = array();

// Config register form category
$config['category'] = array(
    // General information.
    array(
        'name'    => 'general',
        'title'   => _t('General'),
    ),

    // You can define more category
);

// Config register form element
$config['item'] = array(

    // Config username.
    array(
        // Set element.
        'element'  => array(
            'name'    => 'identity',
            'options' => array(
                'label' => __('Username'),
            ),
            'attributes'    => array(
                'type' => 'text',
            ),
        ),

        // Set element filter.
        'filter'   => array(
            'name' => 'identity',
            'required' => true,
            'filters'  => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),

            // Set element validators
            'validators'    => array(
                array(
                    'name'      => 'StringLength',
                    'options'   => array(
                        'encoding'  => 'UTF-8',
                        'min'       => 6,
                        'max'       => 25,
                    ),
                ),

                // Set custom verify class.
                new \Module\System\Validator\UserName(array(
                    'format'            => 'strict',
                    'backlist'          => 'webmaster|^pi|^admin',
                    'checkDuplication'  => true,
                )),
            ),
        ),

        // Set element category.
        'category' => 'general',
    ),

    // Config Email.
    array(
        'element' => array(
            'name'          => 'email',
            'options'       => array(
                'label' => __('Email address'),
            ),
            'attributes'    => array(
                'type'  => 'text',
            )
        ),

        'filter' => array(
            'name'          => 'email',
            'required'      => true,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
            'validators'    => array(
                array(
                    'name'      => 'EmailAddress',
                    'options'   => array(
                        'useMxCheck'        => false,
                        'useDeepMxCheck'    => false,
                        'useDomainCheck'    => false,
                    ),
                ),
                new \Module\User\Validator\UserEmail(array(
                    'backlist'          => 'pi-engine.org$',
                    'checkDuplication'  => true,
                )),
            ),
        ),

        'category' => 'general',
    ),

    // Custom password form element.
    array(
        'element' => array(
            'name'          => 'credential',
            'options'       => array(
                'label' => __('Password'),
            ),
            'attributes'    => array(
                'type'  => 'password',
            )
        ),

        'filter' =>array(
            'name'          => 'credential',
            'required'      => true,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
            'validators'    => array(
                array(
                    'name'      => 'StringLength',
                    'options'   => array(
                        'encoding'  => 'UTF-8',
                        'min'       => $config['password_min'],
                        'max'       => $config['password_max'],
                    ),
                ),
            ),
        ),

        'category' => 'general',
    ),

    // Custom password-confirm form element.
    array(
        'element' => array(
            'name'          => 'credential-confirm',
            'options'       => array(
                'label' => __('Confirm password'),
            ),
            'attributes'    => array(
                'type'  => 'password',
            )
        ),

        'filter' => array(
            'name'          => 'credential-confirm',
            'required'      => true,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
            'validators'    => array(
                array(
                    'name'      => 'Identical',
                    'options'   => array(
                        'token'     => 'credential',
                        'strict'    => true,
                    ),
                ),
            ),
        ),

        'category' => 'general',
    ),

    // Custom captcha
    array(
        'element' => array(
            'name'          => 'captcha',
            'type'          => 'captcha',
            'options'       => array(
                'label'     => __('Please type the word.'),
                'separator'         => '<br />',
                'captcha_position'  => 'append',
            )
        ),
        'filter'  => array(),
        'category' => 'general',
    ),
);

return $config;