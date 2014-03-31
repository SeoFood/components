<?php

return array(


	/**
	 * The Location where your Components are present, without Component Folder Name
	 */
	'location' => 'Modules',

	/**
	 * Setup your loading
	 *
	 * namespace, folder
	 */
	'type' => 'namespace',

	/**
	 * The component folder or Namespace name
	 *
	 * PSR-4 Name must endig with Trailing Slash
	 */
	'name' => 'Modules\\',

	/**
	 * Here you can define files, that autoload in each Module
	 */
	'file_autoload' => array(
		'routes.php',
		'filters.php'
	),

	/**
	 * Folder would be created with artisan command
	 */
	'artisan_create_folders' => array(
		'Controllers', // ucfirst for namespace on osx, linux and windows
		'Models', // ucfirst for namespace on osx, linux and windows
		'Models'.DIRECTORY_SEPARATOR.'Eloquent',
		'views',
		'lang',
		'config'
	),

	/**
	 * Files would be created with artisan command
	 * This Files are automatic created without asking
	 * route.php must not defined
	 */
	'artisan_create_files' => array(
		'filters.php'
	),
);