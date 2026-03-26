<?php

return [
    'title' => 'Manage Material Inventory',

    'form' => [
        'categories'                          => 'Category list',
        'categories-helper'                   => 'One category per line. Example: N-Notebook',
        'statuses'                            => 'Status list',
        'statuses-helper'                     => 'One status per line. Example: in_use, broken, available',
        'default-status'                      => 'Default status on creation',
        'status-in-use'                       => 'Status used when checking out',
        'status-under-repair'                 => 'Status used when sent to repair',
        'enforce-project-budget'              => 'Enforce project budget',
        'enforce-project-budget-helper'       => 'If enabled, assignment to a project checks available project budget.',
        'default-expected-return-days'        => 'Default expected return days',
        'default-expected-return-days-helper' => 'Used as default when checking out an item.',
    ],
];
