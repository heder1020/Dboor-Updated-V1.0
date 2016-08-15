<?php
$AppConfig = array (
	'db' 					=> array (
		'host'				=> 'localhost', // host
		'user'				=> 'root', // user
		'password'			=> '', // password
		'database'			=> 'travian' // database
	),
	'Game' 	=> array (
		'speed'	      		=> 1, //Game Speed
		'attack'        	=> 1, // Troops Speed
		'capacity'      	=> 1, // capacity
		'carry'         	=> 1, // carry
		'cranny'        	=> 1, // Cranny Capacity
		'market'        	=> 1, // for trader 1 is normaly
        'silver_coins'  	=> true, // true to enable  / false to disable silver coins
        'silver_coins_rate' => 2, // silver coins ration for players actions
		'map_size'			=> 400, // size of map 
		'natar_rise'		=> 60 * 60 * 24 * 30, // 1 month
		'cp_for_new_village' => 2000, // CP for new village, Formula : speed / 10 * cp_for_new_village 
		'player_protection_period' => 60 * 60 * 24 * 2, // 48 hours
	),
	'page' 		=> array (
		'en_title'			=> 'Vitrex Dboor',
		'meta-tag' 			=> '',
		'asset_version'		=> time(),	// i like to use timestamp value to flush all old css html and JS chache
	),
	'system' 	=> array (
		'lang'				=> 'en', // this is the default language en = for english
		'forum_url'			=> '#',
		'social_url'		=> '#',
		'adminName'			=> 'Multihunter', // Administrator account name
		'adminPassword'		=> '1', // Administrator Password
		'admin_email'		=> 'b.expire@gmail.com', // the email for admin account (set it before setup)
		'email' 			=> '@.', // the email for others (like activation, forget password, ..etc)
        'install_key' 		=> '123',
        'destroy' 			=> '123456',  //delete everything in the server
        'securty_key'		=> '1234',
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
				'merchant_id'	=> 'b.expire@gmail.com',//your paypal emaill
				'currency'		=> 'USD'
			)
		)
	)
);





