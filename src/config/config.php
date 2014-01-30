<?php

return array(


	/**
	 * The Location where your Components are present, without Component Folder Name
	 */
	'location' => '/',

	/**
	 * Setup your loading
	 *
	 * namespace, folder
	 */
	'type' => 'folder',

	/**
	 * The component folder or Namespace name
	 */
	'name' => 'Modules',

	/**
	 * Here you can define files, that autoload in each Module
	 */
	'file_autoload' => array(
		'routes.php',
		'filters.php'
	),

	/**
	 * File would be created with artisan command
	 */
	'artisan_create_folders' => array(
		'Controllers', // ucfirst for namespace on osx, linux and windows
		'Models', // ucfirst for namespace on osx, linux and windows
		'views',
		'lang',
		'config'
	)
);