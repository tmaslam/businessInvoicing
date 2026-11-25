<?php

/*
|--------------------------------------------------------------------------
| Business Invoicing settings
|--------------------------------------------------------------------------
| Values carried over from the legacy application_top.php / common.php.
*/

return [

    'roles' => [
        'admin' => 1,
        'designer' => 2,
        'csr' => 3,
        'qa' => 4,
    ],

    'role_names' => [
        1 => 'Admin',
        2 => 'Designer',
        3 => 'CSR',
        4 => 'QA',
    ],

    'order_status' => [
        'new' => 'NEW',
        'edit' => 'In Progress',
        'completed' => 'Completed',
        'lock' => 'Lock',
        'archive' => 'Archive',
    ],

    // Website_ID => [name, customer/invoice code prefix]
    'websites' => [
        1 => ['name' => 'Digitizing Zone', 'prefix' => 'DZ'],
        2 => ['name' => '1Dollar Digitizing', 'prefix' => '1D'],
        3 => ['name' => 'Vogue Vesture', 'prefix' => 'VV'],
    ],

    'currencies' => [
        1 => 'Dollar',
        2 => 'Pound',
    ],

    'edit_code_types' => ['Designer', 'Customer', 'Other'],

    // Owner share of earned profit (percent) used on the Profit Distribution page.
    'owner_profit_percent' => 30,

    // Short labels shown in the designer order listing (User_Id => prefix).
    'designer_prefixes' => [
        52 => 'qam', 50 => 'sab', 49 => 'alh', 48 => 'skn', 47 => 'zoh',
        46 => 'sul', 40 => 'shn', 39 => 'zan', 38 => 'hsb', 33 => 'rid', 31 => 'kaz',
    ],

    'per_page' => [
        'orders' => 50,
        'customers' => 50,
        'users' => 50,
        'completed' => 300,
        'codes' => 25,
        'expenses' => 25,
    ],
];
