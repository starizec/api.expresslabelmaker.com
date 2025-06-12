<?php

return (function () {
    $env = env('APP_ENV', 'development');

    $environments = [
        'production' => [
            'hr' => [
                'dpd' => 'https://easyship.hr/api',
                'overseas' => 'https://api.overseas.hr',
                'hp-auth' => 'https://dxwebapi.posta.hr:9000/api',
                'hp' => 'https://dxwebapi.posta.hr:9020/api',
                'gls' => 'https://api.test.mygls.hr'
            ],
            'si' => [
                'dpd' => 'https://easyship.si/api'
            ]
        ],
        'development' => [
            'hr' => [
                'dpd' => 'https://easyship.hr/api', 
                'overseas' => 'https://apitest.overseas.hr',
                'hp-auth' => 'https://dxwebapit.posta.hr:9000/api',
                'hp' => 'https://dxwebapit.posta.hr:9020/api',
                'gls' => 'https://api.test.mygls.hr'
            ],
            'si' => [
                'dpd' => 'https://easyship.si/api'
            ]
        ],
    ];
    return $environments[$env] ?? $environments['production'];
})();