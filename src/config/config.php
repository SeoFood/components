<?php

return array(


	/**
	 * The Location where your Components are present, without Component Folder Name
	 */
	'location' => './',

	/**
	 * The component folder name
	 */
	'folderName' => 'Components',

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