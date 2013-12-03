<?php namespace Levare\Components;

/**
 * This class handle the component registration without ServiceProvider
 * 
 * @package Levare\Components;
 * @author Florian Uhlrich <f.uhlrich@levare-cms.de>
 * @copyright Copyright (c) 2013 by Levare Project Team
 * @version 1.0
 * @access public
 */

use Illuminate\Foundation\Application;
use Levare\Components\JsonFileWorker;
use Illuminate\Support\ClassLoader;

class Components {

	/**
	 * Contains Application
	 *
	 * @var Illuminate\Filessystem\Filesystem
	 */
	public $files;

	private $jsonFileWorker;

	/**
	 * Contains all Components
	 *
	 * @var array
	 */
	public $components = array();

	/**
	 * Create a new Instance of this Class
	 * 
	 * @param Application $app
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;
		$this->jsonFileWorker = $app['components.jsonfileworker'];

		if(!$this->checkPath())
		{
			$this->createFolder();
			return false;
		}

		$this->registerComponents();
		
	}

	/**
	 * Create the components folder
	 * @return void
	 */
	public function createFolder()
	{
		$method = $this->app['request']->server('REQUEST_METHOD');

		if($method == 'POST')
		{
			$location = $this->app['config']->get('components::location');
			$folderName = $this->app['config']->get('components::folderName');

			if(str_contains($location, './'))
			{
				$path = str_finish(base_path(), '/').$folderName;
			}
			else
			{
				$path = str_finish(base_path(), '/').$location.$folderName;
			}
			
			$this->app['files']->makeDirectory($path);

			header('LOCATION: /');
			exit();
		}
		else
		{
			// Display view to create the components folder
			echo $this->app['view']->make('components::create_folder');
		}
	}

	/**
	 * Check if components Path exists
	 * @return boolean
	 */
	public function checkPath()
	{
		$location = $this->app['config']->get('components::location');
		$folderName = $this->app['config']->get('components::folderName');

		if(str_contains($location, './'))
		{
			$path = str_finish(base_path(), '/').$folderName;
		}
		else
		{
			$path = str_finish(base_path(), '/').$location.$folderName;
		}		

		// Get Composer File
		$composer = json_decode($this->jsonFileWorker->getComposerFile(), true);

		// Write to composer.json if key and value not exists
		if(!array_key_exists($folderName, array_get($composer, 'autoload.psr-0', array())) || !in_array($location, array_get($composer, 'autoload.psr-0')))
		{
			if(!array_get($composer, 'autoload.psr-0.'.$folderName, false))
			{
				array_set($composer, 'autoload.psr-0', array(ucfirst($folderName) => $location));
			}
			else
			{
				array_set($composer, 'autoload.psr-0.'.$folderName, $location);
			}

			$this->jsonFileWorker->setComposerFile($composer);
		}

		// Check if components path is writable
		return is_writable($path);
	}

	/**
	 * Load all necessary Files before other Action
	 * @return void
	 */
	public function before()
	{

	}

	/**
	 * Parse a component
	 * @return void
	 */
	private function parse($component)
	{
		return $component;
	}

	/**
	 *	Load all required Files
	 * 
	 * @param  string $json
	 * @return void
	 */
	public function loadRequiredFiles($component)
	{
		// Laden aller als required definierten Dateien
		$config = $this->app['config']->get('components::autoload_files');
		$compAutoload = $this->jsonFileWorker->getSettingsFile($component['path'], 'autoload_files', array());

		$autoload = array_merge($compAutoload, $config);
		$autoload = array_unique($autoload);
		
		foreach($autoload as $file)
		{
			if($this->app['files']->isWritable(str_finish($component['path'], '/').$file))
			{
				require_once str_finish($component['path'], '/').$file;
			}
		}
		
	}

	/**
	 * Register all necessary needs at global namespace
	 * 
	 * @param  string $json
	 * @return void
	 */
	public function registerGlobalNamespace($component)
	{
		$json = $this->jsonFileWorker->getSettingsFile($component['path']);
		$directories = array();

		if(array_key_exists('global', $json))
		{
			foreach($json['global'] as $glob)
			{
				$directories[] = str_finish($component, '/').$glob;
			}
		}

		ClassLoader::addDirectories($directories);
	}

	/**
	 * Register all necessary folders at laravel
	 * 
	 * @return void
	 */
	private function registerFolders($component)
	{
		$json = $this->jsonFileWorker->getSettingsFile($component['path']);
		$path = str_finish($component['path'], '/');
		
		if(array_key_exists('folders', $json))
		{
			if(in_array('views', $json['folders']))
			{
				if($this->app['files']->isWritable($viewPath = $path.'views'))
				{
					$this->app['view']->addNamespace($component['slug'], $viewPath);
				}

			}

			if(in_array('lang', $json['folders']))
			{
				if($this->app['files']->isWritable($langPath = $path.'lang'))
				{
					$this->app['translator']->addNamespace($component['slug'], $langPath);
				}
			}

			if(in_array('config', $json['folders']))
			{
				if($this->app['files']->isWritable($configPath = $path.'config'))
				{
					$this->app['config']->addNamespace($component['slug'], $configPath);
				}
			}
		}
	}

	/**
	 * Find all components
	 *
	 * @return array
	 */
	private function findAllComponents()
	{
		return $this->app['files']->directories(component_path());
	}

	/**
	 * Register all components
	 *
	 * @return void
	 */
	private function registerComponents()
	{
		$components = $this->findAllComponents();

		foreach($components as $comp)
		{
			$json = $this->jsonFileWorker->getSettingsFile($comp);
			$componentName = last(explode('/', str_replace('\\', '/', $comp)));
			
			$this->components[$componentName] = array(
				'path' => $comp,
				'name' => $componentName,
				'slug' => $json['slug']
			);

			if(!is_null($json) && array_key_exists('enabled', $json))
			{
				array_set($this->components[$componentName], 'enabled', true);
				$this->loadRequiredFiles($this->components[$componentName]);
				$this->registerFolders($this->components[$componentName]);
				$this->registerGlobalNamespace($this->components[$componentName]);		
			}
			else
			{
				array_set($this->components[$componentName], 'enabled', false);	
			}

		}
	}

	/**
	 * Get Component Status
	 */
	public function isActive($component)
	{
		return (array_key_exists($component, $this->components)) ? $this->components[$component]['enabled'] : false;
	}
}