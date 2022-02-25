<?php

/**
 * Model configuration options for settings model.
 */

return [
    'form' => [
        'toolbar' => [
            'buttons' => [
                'save' => ['label' => 'lang:admin::lang.button_save', 'class' => 'btn btn-primary', 'data-request' => 'onSave'],
                'saveClose' => [
                    'label' => 'lang:admin::lang.button_save_close',
                    'class' => 'btn btn-default',
                    'data-request' => 'onSave',
                    'data-request-data' => 'close:1',
                ],
            ],
        ],
        'fields' => [
            'print_docket_names' => [
                'label' => 'lang:cupnoodles.quickstock::default.print_docket_names',
                'type' => 'switch',
                'default' => 'false',
            ],
            'order_alphabetical' => [
                'label' => 'lang:cupnoodles.quickstock::default.order_alphabetical',
                'type' => 'switch',
                'default' => 'false',
            ],
            'quickstock_options' => [
                'label' => 'lang:cupnoodles.quickstock::default.quickstock_setting_options',
                'type' => 'switch',
                'default' => 'false',
            ],
        ],
        'rules' => [

        ],
    ],
];
