<?php
/**
 * User module custom register complete form configs
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
$config = array(
    // Custom full name
    'name' => array(
        // Set element
        'element'  => array(
            'name'    => 'name',
            'options' => array(
                'label' => __('Full name'),
            ),
            'attributes'    => array(
                'type' => 'text',
            ),
        ),

        // Set element filter
        'filter'   => array(
            'name' => 'name',
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
                        'min'       => 1,
                        'max'       => 25,
                    ),
                ),
            ),
        ),
    ),

    // Custom gender
    'gender' => array(
        'element' => array(
            'name'          => 'gender',
            'options'       => array(
                'label' => __('Gender'),
            ),
            'attributes'    => array(
                'options' => array(
                    'm' => __('Male'),
                    'f' => __('Female'),
                ),
                'value'     => 'm',
            ),
            'type' => 'radio',

        ),

        'filter' => array(
            'name'          => 'gender',
            'required'      => true,
        ),
    ),

    // Custom bio
    'bio' =>array(
        'element' => array(
            'name'          => 'bio',
            'options'       => array(
                'label' => __('Bio'),
            ),
            'attributes'    => array(
                'type'      => 'textarea',
                'cols'      => 50,
                'rows'      => 5,
            ),
        ),

        'filter' =>array(
            'name'     => 'bio',
            'required' => true,
        ),
    ),

    // Custom birthday
    'birthday' => array(
        'element' => array(
            'name'          => 'birthday',
            'options'       => array(
                'label' => __('Birthday'),
            ),
            'attributes'    => array(
                'type'  => 'text',
                'id'    => 'birthday',
            )
        ),

        'filter' =>array(
            'name'     => 'birthday',
            'required' => true,
        ),
    ),

    // Custom location
    'location' => array(
        'element' => array(
            'name'          => 'location',
            'options'       => array(
                'label' => __('Location'),
            ),
            'attributes'    => array(
                'type'  => 'text',
            )
        ),

        'filter' => array(
            'name'          => 'location',
            'required'      => true,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
        ),
    ),

    // Custom extend profile field zip
    'zip'    => array(
        'name' => 'zip',
    ),

    // Custom extend profile field street
    'street' => array(
        'name' => 'street'
    ),
);

return $config;