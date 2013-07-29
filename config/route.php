<?php
return array(
    'user' => array(
        'section' => 'front',
        'priority' => 100,

        'type' => 'Module\\User\\Route\\User',
        'options' => array(
            'prefix'    => '',
            'defaults'  => array(
                'module'        => 'user',
                'controller'    => 'register',
                'action'        => 'index'
            ),
        ),
    ),
);