<?php
/*
	Game Main Configuration File:
	> You can find various settings of the game that can help you adjust the game to
	your own desire. There are some settings that can be left by default but there are
	some which must be changed in order for the game to work properly.
*/
$AppConfig = array (
	'db' => array (
		'host'			=> 'localhost',	// Database Hostname
		'user'			=> 'root',	// Database Username
		'password'		=> '',		// Database Password
		'database'		=> 'travian'	// Database Name
	),
	'Game' 	=> array (
		'speed'	      		=> '3', 	// Game Speed
		'attack'        	=> '1.5', 	// Troops Speed
		'capacity'      	=> '2', 	// Capacity
		'carry'         	=> '1', 	// Carry
		'cranny'        	=> '2', 	// Cranny Capacity
		'market'        	=> '1', 	// Trade Capacity, Default: 1
        	'silver_coins'  	=> true,	// Silver Coins System? - True = Yes, False = No
        	'silver_coins_rate' 	=> '2',		// Silver Coins Rate - Default: 2
        	'map_size'		=> 400		// Server Maps Size - Default: 400
	),
	'page' 	=> array (
		'en_title'		=> 'Tatar war',	// English Title of the Game
		'meta-tag' 		=> '',		// Any tags like author, name, description etc. (HTML stuff)
		'asset_version'		=> 'c4b7c089def'// this is used to flush any old assets like css file or javascript
	),
	'system' => array (
		'lang'			=> 'en',	// Default language: English(en)									// this is the default language, ar = for arabic, en = for english
		'forum_url'		=> '#',		// Link to the Forum
		'social_url'		=> '#',		// Link to the Facebook/Twitter/Youtube/etc page of the Game.
		// Admin Account Information
		'adminName'		=> 'Multihunter',	// Admin Username
		'adminPassword'		=> '1',			// Admin Password
		'admin_email'		=> 'some@mail.com',	// The email for admin account (set it before setup)
		'email' 		=> '@.',		// The email for others (like activation, forget password, ..etc)
                'install_key' 		=> 'installMe',		// Create the server's database.
                'destroy' 		=> 'destroy_game',  	// Delete everything in the server.
                'securty_key' 		=> '666DAFEDuebw9je02jeeB666',
                'new_user_activaiton'	=> false 		// False: disable E-mail activation, True: enable E-mail activation
	),
	'plus'	=> array (
		'packages' => array (
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
				'serviceName' 		=> '#',
				'merchant_id'		=> '#',
				'key'			=> '#',
				'testKey'		=> '#',
				'returnKey'		=> '#',
				'currency'		=> 'usd'
			),
			'onecard'	=> array (
				'testMode'		=> FALSE,
				'name'			=> 'OneCard',
				'image'			=> 'onecard_logo.gif',
				'serviceName' 		=> '',
				'merchant_id'		=> '#',
				'key'			=> '#',
				'testKey'		=> '#',
				'returnKey'		=> '#',
				'currency'		=> 'usd'
			),
			'paypal'	=> array (
				'testMode'		=> false,
				'name'			=> 'PayPal',
				'image'			=> 'paypal_solution_graphic-US.gif',
				'merchant_id'		=> '#',//your paypal emaill
				'currency'		=> 'USD'
			)
		)
	)
);
