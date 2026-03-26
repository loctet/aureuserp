<?php

return [
    'title' => 'Material inventory settings',

    'navigation' => [
        'label' => 'Settings',
    ],

    'sections' => [
        'statuses' => [
            'title'       => 'Status list',
            'description' => 'Select and rename available material statuses.',
            'fields'      => [
                'status_list' => 'Statuses',
                'value'       => 'Status key',
                'label'       => 'Status label',
            ],
        ],
        'categories' => [
            'title'  => 'Category list',
            'fields' => [
                'category_list' => 'Categories',
                'value'         => 'Category',
            ],
        ],
        'other' => [
            'title'  => 'Other settings',
            'fields' => [
                'default_storage_location'                => 'Default storage location',
                'require_expected_return_date_on_checkout' => 'Require expected return date on check out',
            ],
        ],
    ],
];
