<?php

return [
    'form' => [
        'toolbar' => [
            'buttons' => [
                'save' => [
                    'label' => 'lang:igniter::admin.button_save',
                    'class' => 'btn btn-primary',
                    'data-request' => 'onSave',
                    'data-progress-indicator' => 'igniter::admin.text_saving',
                ],
            ],
        ],
        'fields' => [
            'quickstock_options' => [
                'label' => 'lang:cupnoodles.quickstock::default.label_show_options',
                'type' => 'switch',
                'default' => false,
                'comment' => 'lang:cupnoodles.quickstock::default.help_show_options',
            ],
            'order_alphabetical' => [
                'label' => 'lang:cupnoodles.quickstock::default.label_order_alphabetical',
                'type' => 'switch',
                'default' => false,
                'comment' => 'lang:cupnoodles.quickstock::default.help_order_alphabetical',
            ],
            'print_docket_names' => [
                'label' => 'lang:cupnoodles.quickstock::default.label_print_docket_names',
                'type' => 'switch',
                'default' => false,
                'comment' => 'lang:cupnoodles.quickstock::default.help_print_docket_names',
            ],
        ],
    ],
];
