<?php namespace Levare\Components;

use Illuminate\Foundation\Application;

class JsonFileWorker {

	private $app;

	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	/**
	 * Check composer.json has autoload parameter
	 */
	public function getComposerFile()
	{
		return parse_json_file('composer.json');		
	}

	/**
	 * Override the composer.json
	 */
	public function setComposerFile($data)
	{
		$path = str_finish(base_path(), '/').'composer.json';
		
		if($this->app['files']->isWritable($path))
		{
			return $this->app['files']->put($path, format_json($data));
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