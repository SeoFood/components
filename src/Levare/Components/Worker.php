<?php namespace Levare\Components;

/**
 * This Class handles all Component Workings
 *
 * @todo  Write Documentation
 *
 * @package levare\components
 * @author Florian Uhlrich <f.uhlrich@levare-cms.de>
 * @copyright Copyright (c) 2013 by Levare Project Team
 * @version 1.0alpha
 * @access public
 */


use Illuminate\Foundation\Application;

class Worker {

	private $app;
	private $path;


	public function __construct(Application $app)
	{
		$this->app = $app;
		$this->path = $this->app['components']->getPath();
	}


	public function writeTest()
	{
		return "test";
	}

	/**
	 * Call the Create Command to create a new Componente from CLI
	 * @param  string $name
	 */
	public function commandCreate($name)
	{
		$this->createFolder($name);
		$this->createRoutesFile($name);
		$this->createSettingsFile($name);
	}

	/**
	 * Erstellt die Ordner
	 */
	private function createFolder($name)
	{
		$root = ucfirst(str_finish($this->path.$name, '/'));

		$this->app['files']->makeDirectory($root, 0755);

		foreach($this->app['config']->get('components::artisan_create_folders') as $folder)
		{
			$this->app['files']->makeDirectory($root.$folder, 0755);
		}
	}

	/**
	 * Erstellt die component.json
	 */
	private function createSettingsFile($name)
	{
		$path = str_finish($this->path.$name, '/');
		$settings = $this->app['files']->get(__DIR__.'/FileTemplates/component.json');

		$settings = str_replace('{{name}}', ucfirst($name), $settings);
		$settings = str_replace('{{slug}}', strtolower($name), $settings);
		$settings = str_replace('{{author}}', $this->app['config']->get('workbench.name'), $settings);
		$settings = str_replace('{{email}}', $this->app['config']->get('workbench.email'), $settings);

		$this->app['files']->put($path.'component.json', $settings);
	}

	/**
	 * Erstellt die routes.php
	 */
	private function createRoutesFile($name)
	{
		$path = str_finish($this->path.$name, '/');
		$this->app['files']->put($path.'routes.php', '<?php //');
	}

}