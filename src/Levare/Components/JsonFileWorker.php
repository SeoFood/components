<?php namespace Levare\Components;

/**
 * This Class worked with component.json Files
 *
 * @todo Write Documentation, write better method comments
 *
 * @package 
 * @author Florian Uhlrich <f.uhlrich@levare-cms.de>
 * @copyright Copyright (c) 2013 by Levare Project Team
 * @version 1.0
 * @access public
 */

use Illuminate\Foundation\Application;

class JsonFileWorker {

	/**
	 * Contains app Ioc container
	 * @var Illuminate\Foundation\Application
	 */
	private $app;

	public function __construct(Application $app)
	{
		// Make IOC Container available on class
		$this->app = $app;
	}

	/**
	 * Check composer.json has autoload parameter
	 */
	public function getComposerFile($path = false)
	{
		return parse_json_file('composer.json', $path);		
	}

	/**
	 * Override the composer.json
	 */
	public function setComposerFile($data)
	{
		$path = str_finish(base_path(), '/').'composer.json';
		
		if($this->app['files']->isWritable($path))
		{
			return $this->app['files']->put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
		}
		else
		{
			return App::abort('403', "Please set writable permissions to composer.json \n");
		}
	}

	/**
	 * Load a specific Settings Element 
	 * 
	 * @param  string $json
	 * @param string $param
	 * @return string|array
	 */
	public function loadSettings($json, $param = false)
	{
		$jsonConf = json_decode(parse_json_file($json, true), true);
		return (!$param) ? $jsonConf : array_get($jsonConf, $param, false);
	}

	/**
	 * Load settings file from component
	 * 
	 * @param  string $component
	 * @return array
	 */
	public function getSettingsFile($component, $param = false)
	{
		$jsonFile = str_finish($component, '/').'component.json';

		if($this->app['files']->isWritable($jsonFile))
		{
			return $this->loadSettings($jsonFile, $param);
		}
	}

	/**
	 * Create a settings file
	 * 
	 * @param  string $component
	 * @return void
	 */
	public function createSettingsFile($component)
	{
		// @ToDo
	}


}