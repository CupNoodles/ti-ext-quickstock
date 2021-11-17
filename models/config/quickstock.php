<?php


$config['list']['columns'] = [
    'edit' => [
        'type' => 'button',
        'iconCssClass' => 'fa fa-pencil',
        'attributes' => [
            'class' => 'btn btn-edit',
            'href' => admin_url('menus/edit/{menu_id}'),
            'target' => '_blank'
        ],
    ]
];

return $config;