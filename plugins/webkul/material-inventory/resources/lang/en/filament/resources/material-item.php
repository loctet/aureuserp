<?php

return [
    'title'  => 'Material item',
    'titles' => [
        'list'   => 'Material inventory',
        'create' => 'Create material item',
        'view'   => 'View material item',
        'edit'   => 'Edit material item',
    ],
    'navigation' => [
        'label'     => 'Material inventory',
        'group'     => 'Material inventory',
        'title'     => 'Material inventory',
    ],
    'form' => [
        'sections' => [
            'identity' => [
                'title'  => 'Inventory ID (Excel sheet)',
                'fields' => [
                    'inventory_number'        => 'Inventory ID',
                    'inventory_number_locked' => 'Locked after issue',
                    'progressive_asset_number'=> 'Asset number (progressive)',
                ],
            ],
            'asset' => [
                'title'  => 'Asset details',
                'fields' => [
                    'category'         => 'Category',
                    'acquisition_date' => 'Acquisition date',
                    'name'             => 'Asset description',
                    'manufacturer'     => 'Brand',
                    'model'            => 'Model',
                    'serial_number'    => 'Serial number',
                    'supplier'         => 'Supplier',
                    'sheet_status'     => 'Status (New/Used/Broken/In use)',
                    'is_functional'    => 'Functional',
                    'storage_location' => 'Location',
                    'images'           => 'Images',
                ],
            ],
            'custody' => [
                'title'  => 'Custody & project',
                'helper' => 'Use the actions “Check out” / “Check in” on the view page to register assignment in the history.',
                'fields' => [
                    'current_custodian_employee_id' => 'Current assignee',
                    'assignment_date'               => 'Assignment date',
                    'expected_return_date'          => 'Expected return',
                    'project_id'                    => 'Project',
                    'acquisition_cost'              => 'Acquisition cost',
                    'is_free'                       => 'Free item (no budget charge)',
                    'notes'                         => 'Notes',
                ],
            ],
        ],
    ],
    'table' => [
        'columns' => [
            'inventory_number'         => 'Inventory ID',
            'progressive_asset_number' => 'Asset number',
            'category'                 => 'Category',
            'acquisition_date'         => 'Acquisition date',
            'name'                     => 'Description',
            'manufacturer'             => 'Brand',
            'model'                    => 'Model',
            'sheet_status'             => 'Status',
            'is_functional'            => 'Functional',
            'storage_location'         => 'Location',
            'custodian'                => 'Assignee',
            'assignment_date'          => 'Assignment date',
            'project'                  => 'Project',
        ],
        'export' => [
            'label' => 'Export Excel (sheet layout)',
        ],
    ],
    'actions' => [
        'issue_formal_id' => [
            'label' => 'Issue formal inventory ID',
            'body'  => 'Assigns {prefix}-{sequence:4}-{year} and locks the ID (per your Excel rules).',
        ],
        'check_out' => [
            'label' => 'Check out to employee',
        ],
        'check_in' => [
            'label'       => 'Check in (return)',
            'return_date' => 'Return date',
        ],
        'send_repair' => [
            'label' => 'Send to repair',
        ],
        'return_repair' => [
            'label' => 'Return from repair',
        ],
        'images' => 'Images (optional)',
        'repair' => [
            'cost'                => 'Repair cost',
            'assignment'          => 'Repair cost assignment',
            'assignment_internal' => 'Internal',
            'assignment_project'  => 'Project',
            'project'             => 'Charge project',
            'start_date'          => 'Repair start date',
            'end_date'            => 'Repair end date',
            'functional_after'    => 'Functional after repair',
        ],
    ],
    'notifications' => [
        'issue_ok'               => 'Formal inventory ID issued.',
        'budget'                 => 'This project does not have enough budget to assign this item (or set the item as free).',
        'already_assigned'       => 'Item is already assigned. Return it first before assigning to another employee.',
        'not_assigned'           => 'Item is not currently assigned to any employee.',
        'under_repair'           => 'Item is under repair and cannot be assigned.',
        'non_functional'         => 'Non-functional item cannot be assigned to an employee.',
        'repair_requires_return' => 'Return the item from employee first, then send it to repair.',
    ],
    'relation-managers' => [
        'transactions' => [
            'title' => 'History',
        ],
        'employee-items' => [
            'title' => 'Material inventory (in custody)',
        ],
        'employee-received' => [
            'title' => 'Inventory events (received)',
        ],
        'employee-sent' => [
            'title' => 'Inventory events (handed over)',
        ],
        'project-items' => [
            'title' => 'Material assigned to project',
        ],
    ],
];
