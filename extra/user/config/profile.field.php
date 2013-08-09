<?php

/**
 * Config user system extend profile field.
 * Custom all form need fetch to this config file
 * User system install or update will read this file and store config to database
 *
 */

return array(
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
    ),
);
