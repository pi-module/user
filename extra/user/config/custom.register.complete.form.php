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
$config = array();

// Config category
$config['category'] = array(
    // Basic information.
    array(
        'name'    => 'basic',
        'title'   => _t('Basic information'),
    ),
);

// Config register form element
$config['item'] = array(

    // Config full name
    array(
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
                        'min'       => 6,
                        'max'       => 25,
                    ),
                ),
            ),
        ),

        // Set element category.
        'category' => 'basic',
    ),

    // Custom gender form element
    array(
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

        'category' => 'basic',
    ),

    // Custom bio form element
    array(
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

        'category' => 'basic',
    ),

    // Custom birthday form element
    array(
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

        'category' => 'basic',
    ),

    // Custom location form element
    array(
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

        'category' => 'basic',
    ),

    // Custom street form element
    array(
        'element' => array(
            'name'          => 'street',
            'options'       => array(
                'label' => __('Street'),
            ),
            'attributes'    => array(
                'type'  => 'text',
            )
        ),

        'filter' => array(
            'name'          => 'street',
            'required'      => true,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
        ),

        'category' => 'basic',
    ),

    // Custom zip code form element
    array(
        'element' => array(
            'name'          => 'zip',
            'options'       => array(
                'label' => __('Zip code'),
            ),
            'attributes'    => array(
                'type'  => 'text',
            )
        ),

        'filter' => array(
            'name'          => 'zip',
            'required'      => true,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
        ),

        'category' => 'basic',
    ),
);

return $config;