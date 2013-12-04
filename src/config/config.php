<?php

return array(


	/**
	 * The Location where your Components are present, without Component Folder Name
	 */
	'location' => './',

	/**
	 * The component folder name
	 */
	'folderName' => 'components',

	/**
	 * Here you can define files, that autoload in each Module
	 */
	'file_autoload' => array(
		'routes.php',
		'filters.php'
	)
);