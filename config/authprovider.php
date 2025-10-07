<?php

return [
    'password_policy' => [
        'min_length' => 12,
        'require_numbers' => true,
        'require_symbols' => true,
        'require_mixed_case' => true,
    ],
    'default_roles' => [
        'employee' => 'employee',
        'manager' => 'manager',
    ],
    'mfa' => [
        'enforced_departments' => ['security'],
    ],
];
