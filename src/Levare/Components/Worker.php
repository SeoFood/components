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
	private $rootPath;
	private $modPath;
	private $name;


	public function __construct(Application $app)
	{
		$this->app = $app;
		$this->rootPath = str_finish($this->app['components']->getPath(), '/');
	}

	/**
	 * Setup Name and Path
	 */
	public function beforeCreate($name, $path = false)
	{
		// $this->name = last(explode('_', $name));
		$this->name = $name;
		$path = ($path) ? str_finish($path, '/') : $this->rootPath;

		if(str_contains($name, '_'))
		{
			$name = last(explode('_', $name));
		}

		$this->modPath = ucfirst(str_finish($path.$name, '/'));  
	}

	/**
	 * Setup Name and Path before delete
	 */
	public function beforeDelete($name, $path)
	{
		$this->name = $name;
		$this->modPath = $path;
	}

	/**
	 * Call the Create Command to create a new Componente from CLI
	 *
	 * @deprecated This function remove with next Update
	 * 
	 * @param  string $name
	 */
	public function commandCreate()
	{
		$this->createFolder();
		$this->createFolders();
		$this->createRoutesFile();
		$this->createSettingsFile();
	}

	/**
	 * Erstellt die Ordner
	 */
	public function createFolder()
	{
		$this->app['files']->makeDirectory($this->modPath, 0755);
	}

	/**
	 * Erstellt die Unterordner
	 */
	public function createFolders()
	{
		foreach($this->app['config']->get('components::artisan_create_folders') as $folder)
		{
			$this->app['files']->makeDirectory($this->modPath.$folder, 0755);
		}
	}

	/**
	 * Erstellt die component.json
	 */
	public function createSettingsFile()
	{
		$settings = $this->app['files']->get(__DIR__.'/FileTemplates/component.json');

		$settings = str_replace('{{name}}', ucfirst($this->name), $settings);
		$settings = str_replace('{{slug}}', strtolower($this->name), $settings);
		$settings = str_replace('{{author}}', $this->app['config']->get('workbench.name'), $settings);
		$settings = str_replace('{{email}}', $this->app['config']->get('workbench.email'), $settings);

		$splits = explode('_', $this->name);
		$nSplits = count($splits);
		$modName = last($splits);
		if($nSplits > 1)
		{
			unset($splits[$nSplits -1]);
			$splits = implode($splits, '_');
			$this->addSubmodule($splits, $modName);
		}

		$this->app['files']->put($this->modPath.'component.json', $settings);
	}

	/**
	 * Erstellt die routes.php
	 */
	public function createRoutesFile()
	{
		$this->app['files']->put($this->modPath.'routes.php', '<?php //');
	}

	/**
	 * Fügt ein Submodule zur composer.json 
	 */
	public function addSubmodule($name, $moduleName)
	{
		$config = $this->app['components.jsonfileworker']->getSettingsFile($this->app['components']->components[$name]['path']);
		array_set($config, 'sub_modules', array_merge(array_get($config, 'sub_modules'), array($moduleName)));
		$this->app['files']->put($this->app['components']->components[$name]['path'].'/component.json', format_json($config));
	}

	/**
	 * Löscht ein Submodule aus der component.json des Hauptmodules
	 */
	public function removeSubmodule($name)
	{
		$splits = explode('_', $name);
		$subname = last($splits);
		$count = count($splits);
		unset($splits[$count-1]);
		$name = implode($splits, '_');
		$config = $this->app['components.jsonfileworker']->getSettingsFile($this->app['components']->components[$name]['path']);
		$submodule = $config['sub_modules'];

		foreach($submodule as $key => $value)
		{
			if($value === $subname)
			{
				unset($config['sub_modules'][$key]);
			}
		}

		$this->app['files']->put($this->app['components']->components[$name]['path'].'/component.json', format_json($config));
	}

	/**
	 * Löscht ein bestehendes Module und trägt es aus dem Main Module aus
	 */
	public function deleteModule()
	{
		$this->removeSubmodule($this->name);
		$this->app['files']->deleteDirectory($this->modPath);
	}

}