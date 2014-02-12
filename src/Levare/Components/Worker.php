<?php namespace Levare\Components;

/**
 * This Class handles all Component Workings
 *
 * @todo  Write Documentation, write better method comments
 *
 * @package levare\components
 * @author Florian Uhlrich <f.uhlrich@levare-cms.de>
 * @copyright Copyright (c) 2013 by Levare Project Team
 * @version 1.0alpha
 * @access public
 */


use Illuminate\Foundation\Application;

class Worker {

	/**
	 * Contains app Ioc container
	 * @var Illuminate\Foundation\Application
	 */
	private $app;

	/**
	 * Contains rootpath
	 * @var string
	 */
	private $rootPath;
	
	/**
	 * Contains componentpath
	 * @var string
	 */
	private $modPath;
	
	/**
	 * Contains component name
	 * @var string
	 */
	private $name;

	/**
	 * Create a new class instance
	 * @param Application $app Contains the app Ioc Container
	 */
	public function __construct(Application $app)
	{
		// Make IOC Container available on class
		$this->app = $app;

		// Setup components root path
		$this->rootPath = str_finish($this->app['components']->getPath(), '/');
	}

	/**
	 * Fire before components is create with artisan
	 * @param  string  $name
	 * @param  boolean $path
	 * @return void
	 */
	public function beforeCreate($name, $path = false)
	{
		$this->name = $name;
		$path = ($path) ? str_finish($path, '/') : $this->rootPath;

		if(str_contains($name, '_'))
		{
			$name = last(explode('_', $name));
		}

		$this->modPath = ucfirst(str_finish($path.$name, '/'));  
	}

	/**
	 * Fire before component is deleted with artisan
	 * @param  string $name
	 * @param  string $path
	 * @return void
	 */
	public function beforeDelete($name, $path)
	{
		$this->name = $name;
		$this->modPath = $path;
	}

	/**
	 * Call the Create Command to create a new Componente from CLI
	 *
	 * @deprecated This function is remove with next Update
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
	 * Creates the component folder
	 * @return void
	 */
	public function createFolder()
	{
		$this->app['files']->makeDirectory($this->modPath, 0755);
	}

	/**
	 * Creates all required subfolders of component
	 * @return void
	 */
	public function createFolders()
	{
		foreach($this->app['config']->get('components::artisan_create_folders') as $folder)
		{
			$this->app['files']->makeDirectory($this->modPath.$folder, 0755);
		}
	}

	/**
	 * Creates the component.json file
	 * @return void
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
	 * Creates the route.php file
	 * @return void
	 */
	public function createRoutesFile()
	{
		$this->app['files']->put($this->modPath.'routes.php', '<?php //');
	}

	/**
	 * Add a new submodule to mainmodule composer.json
	 * @param string $name Name of Submodule
	 * @param string $moduleName Name of MainModule
	 */
	public function addSubmodule($name, $moduleName)
	{
		$config = $this->app['components.jsonfileworker']
						->getSettingsFile($this->app['components']
						->components[$name]['path']);

		array_set($config, 'sub_modules', array_merge(array_get($config, 'sub_modules'), array($moduleName)));

		$this->app['files']
			->put($this->app['components']->components[$name]['path'].'/component.json', json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
	}

	/**
	 * Delete a submodule from mainmodule component.json file
	 * @param  string $name
	 * @return void
	 */
	public function removeSubmodule($name)
	{
		$splits = explode('_', $name);
		$subname = last($splits);
		$count = count($splits);

		if($count > 1)
		{
			unset($splits[$count-1]);
		}

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

		$this->app['files']->put($this->app['components']->components[$name]['path'].'/component.json', json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
	}

	/**
	 * Delete a module and fire submodule delete
	 * @return void
	 */
	public function deleteModule()
	{
		// Start Submodule
		$this->removeSubmodule($this->name);

		// Remove component folder
		$this->app['files']->deleteDirectory($this->modPath);
	}

}