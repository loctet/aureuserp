<?php

use Webkul\MaterialInventory\Filament\Resources\MaterialItemResource;

$basic = ['view_any', 'view', 'create', 'update'];
$delete = ['delete', 'delete_any'];
$forceDelete = ['force_delete', 'force_delete_any'];
$restore = ['restore', 'restore_any'];

return [
    'resources' => [
        'manage' => [
            MaterialItemResource::class => [...$basic, ...$delete, ...$restore, ...$forceDelete],
        ],
        'exclude' => [],
    ],

    'pages' => [
        'exclude' => [],
    ],
];
