<?php
$AppConfig = array (
	'db' 					=> array (
		'host'				=> 'localhost',
		'user'				=> 'root',
		'password'			=> '',
		'database'			=> 'travian'
	),
	'Game' 	=> array (
		'speed'	      	=> '3', //Game Speed
		'attack'        => '1.5', // Troops Speed
		'capacity'      => '2', // capacity
		'carry'         => '1', // carry
		'cranny'        => '2', // Cranny Capacity
		'market'        => '1', // for trader 1 is normaly
        'silver_coins'  => true,
        'silver_coins_rate' => '2',
	),
	'page' 		=> array (
		'en_title'			=> 'Tatar war',
		'meta-tag' 			=> '',
		'asset_version'		=> 'c4b7c089def'						// this is used to flush any old assets like css file or javascript
	),
	'system' 	=> array (
		'lang'				=> 'en',										// this is the default language, ar = for arabic, en = for english
		'forum_url'			=> '#',
		'social_url'		=> '#',
		// admin account info
		'adminName'			=> 'Multihunter',
		'adminPassword'		=> '1',
		'admin_email'		=> 'some@mail.com',			// the email for admin account (set it before setup)
		'email' 			=> '@.',			// the email for others (like activation, forget password, ..etc)
                'install_key' => 'installMe',
                'destroy' => 'destroy_game',  //delete everything in the server
                'securty_key' => '666DAFEDuebw9je02jeeB666',
                'new_user_activaiton'=> false // false to disabled email confirmation for new players , true to enable
	),
	'plus'			=> array (
		'packages'	=> array (
			array ( 
				'name'		=> 'Package A',
				'gold'		=> 30,
				'cost'		=> 1.49,
				'currency'	=> 'usd',
				'image'		=> 'package_a.jpg'
			),
			array ( 
				'name'		=> 'Package B',
				'gold'		=> 100,
				'cost'		=> 3.99,
				'currency'	=> 'usd',
				'image'		=> 'package_b.jpg'
			),
			array ( 
				'name'		=> 'Package C',
				'gold'		=> 250,
				'cost'		=> 7.99,
				'currency'	=> 'usd',
				'image'		=> 'package_c.jpg'
			),
			array ( 
				'name'		=> 'Package D',
				'gold'		=> 600,
				'cost'		=> 15.99,
				'currency'	=> 'usd',
				'image'		=> 'package_d.jpg'
			),
		),
		'payments' => array (
			'cashu'	=> array (
				'testMode'		=> FALSE,
				'name'			=> 'CashU',
				'image'			=> 'cashu.gif',
				'serviceName' 	=> '#',
				'merchant_id'	=> '#',
				'key'			=> '#',
				'testKey'		=> '#',
				'returnKey'		=> '#',
				'currency'		=> 'usd'
			),
			'onecard'	=> array (
				'testMode'		=> FALSE,
				'name'			=> 'OneCard',
				'image'			=> 'onecard_logo.gif',
				'serviceName' 	=> '',
				'merchant_id'	=> '#',
				'key'			=> '#',
				'testKey'		=> '#',
				'returnKey'		=> '#',
				'currency'		=> 'usd'
			),
			'paypal'	=> array (
				'testMode'		=> false,
				'name'			=> 'PayPal',
				'image'			=> 'paypal_solution_graphic-US.gif',
				'merchant_id'	=> '#',//your paypal emaill
				'currency'		=> 'USD'
			)
		)
	)
);





