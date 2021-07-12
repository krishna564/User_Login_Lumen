<?php

return[

	'defaults' =>[
		'gaurd' => 'api',
		'passwords' => 'users',
	],

	'gaurds' =>[
		'api' => [
	        'driver' => 'jwt',
	        'provider' => 'users',
	    ],
	],

	'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
    ],

];
