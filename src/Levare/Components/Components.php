<?php namespace Levare\Components;

/**
 * This class handle the component registration without ServiceProvider
 *
 * @todo Write Documentation, write better method comments
 * 
 * @package Levare\Components;
 * @author Florian Uhlrich <f.uhlrich@levare-cms.de>
 * @copyright Copyright (c) 2013 by Levare Project Team
 * @version 1.0alpha
 * @access public
 */

use Illuminate\Foundation\Application;
use Levare\Components\JsonFileWorker;
use Illuminate\Support\ClassLoader;

use Levare\Components\Exceptions\ComponentsException;

class Components {

	/**
	 * Contains Application
	 *
	 * @var Illuminate\Filessystem\Filesystem
	 */
	public $files;

	/**
	 * Contains Components/JsonFileWorker Class
	 * @var Levare\Components\JsonFileWorker
	 */
	private $jsonFileWorker;

	/**
	 * Contains Component Path
	 * @var string
	 */
	private $path;

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
		// Make IOC Container available on class
		$this->app = $app;

		// Make Json Fileworker available on class
		$this->jsonFileWorker = $app['components.jsonfileworker'];

		// Check if component path exist
		if(!$this->checkPath())
		{
			if(php_sapi_name() != 'cli')
			{
				// create component folder if not exists
				$this->createFolder();
			}
			return false;
		}

		// Register all components
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
			$path = $this->cleanPath($this->app['config']);

			$this->app['files']->makeDirectory($path);
			header('LOCATION: /');
			exit();
		}
		else
		{
			// Display view to create the components folder
			$foldername = $this->app['config']->get('components::name');
			$location = $this->cleanPath($this->app['config']);
			echo $this->app['view']->make('components::create_folder', compact('foldername', 'location'));
		}
	}

	/**
	 * Display all Components as Array
	 * @return array
	 */
	public function all()
	{
		return $this->components;
	}

	/**
	 * Setup clean Path
	 */
	private function cleanPath($config)
	{
		$location = $config->get('components::location');
		$name = $config->get('components::name');
		$type = $config->get('components::type');

		if($type == 'namespace')
		{
			if(starts_with($location, './') || starts_with($location, '/'))
			{
				throw new ComponentsException('PSR-4 Loader needs relative path from your root directory e.g app/folder');
			}
			else
			{
				$path = str_finish(base_path(), '/').str_finish($location, '/');
			}
		}
		else
		{
			$location = ($name == $location) ? './' : $location;
			if(starts_with($location, './') || starts_with($location, '/'))
			{
				$path = str_finish(base_path(), '/').$name;
			}
			else
			{
				$path = str_finish(base_path(), '/').str_finish($location, '/').$name;
			}
		}

		return $path;
	}

	/**
	 * Check if components Path exists
	 * @return boolean
	 */
	public function checkPath()
	{
		$config = $this->app['config'];

		// get config vars
		$location = $config->get('components::location');
		$type = $config->get('components::type');
		$name = $config->get('components::name');;

		// Setup Name
		$name = ucfirst($name);

		$path = $this->cleanPath($config);

		// Check Path is writeable
		$writeablePath = is_writable($path);		

		// Get Composer File
		$composer = json_decode($this->jsonFileWorker->getComposerFile(), true);

		// Write to composer.json if key and value not exists
		// dd(!in_array($location, array_get($composer, 'autoload.psr-0')));

		$psr = ($type == 'namespace') ? 'psr-4' : 'psr-0';

		if(!array_key_exists($name, array_get($composer, 'autoload.'.$psr, array())) || !in_array($location, array_get($composer, 'autoload.'.$psr)))
		{

			if($psr == 'psr-0')
			{
				$location = ($name == $location) ? './' : $location;
			}

			$newName = ($psr == 'psr-4') ? $name.'\\' : $name;

			if(!array_get($composer, 'autoload.'.$psr.'.'.$newName, false))
			{
				array_set($composer, 'autoload.'.$psr, array_merge(array_get($composer, 'autoload.'.$psr, array()), array($newName => $location)));
			}
			else
			{
				array_set($composer, 'autoload.'.$psr.'.'.$newName, $location);
			}

			// Forget PSR-4 Components entry
			$unsetPsr = ($psr == 'psr-4') ? 'psr-0' : 'psr-4';
			$nameForget = ($unsetPsr == 'psr-4') ? $name.'\\' : $name;

			array_forget($composer, 'autoload.'.$unsetPsr.'.'.$nameForget);

			if(array_key_exists($unsetPsr, array_get($composer, 'autoload', array())) && !array_get($composer, 'autoload.'.$unsetPsr, false))
			{
				array_forget($composer, 'autoload.'.$unsetPsr);
			}

			// Write new composer.json
			if($writeablePath)
			{
				$this->jsonFileWorker->setComposerFile($composer);
			}
		}
		
		// Setup Path
		$this->path = $path;

		// Check if components path is writable
		return $writeablePath;
	}

	/**
	 * Load all necessary Files before other Action
	 * @return void
	 */
	public function before()
	{

	}

	/**
	 * Get the Component Path
	 * @return string
	 */
	public function getPath($component = false)
	{
		if($component)
		{
			if(array_key_exists(ucfirst($component), $this->components))
			{
				return $this->components[$component]['path'];
			}
		}

		return $this->path;
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
		$config = $this->app['config']->get('components::file_autoload');
		$compAutoload = $this->jsonFileWorker->getSettingsFile($component['path'], 'file_autoload', array());

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

		if(array_key_exists('global_namespace', $json))
		{
			foreach($json['global_namespace'] as $glob)
			{
				$directories[] = str_finish($component['path'], '/').$glob;
			}
			ClassLoader::addDirectories($directories);
		}
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
		
		if(array_key_exists('register_folders', $json))
		{
			if(in_array('views', $json['register_folders']))
			{
				if($this->app['files']->isWritable($viewPath = $path.'views'))
				{
					$this->app['view']->addNamespace($component['slug'], $viewPath);
				}

			}

			if(in_array('lang', $json['register_folders']))
			{
				if($this->app['files']->isWritable($langPath = $path.'lang'))
				{
					$this->app['translator']->addNamespace($component['slug'], $langPath);
				}
			}

			if(in_array('config', $json['register_folders']))
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
	private function registerComponents($components = null, $mainCompName = null)
	{
		$components = (!is_null($components)) ? $components : $this->findAllComponents();

		foreach($components as $compPath)
		{
			$json = $this->jsonFileWorker->getSettingsFile($compPath);
			$compName = last(explode('/', str_replace('\\', '/', $compPath)));

			if(!is_null($mainCompName))
			{
				$compName = $mainCompName . '_' . $compName;
			}

			if(!is_null($json))
			{
				// Write component to array
				$this->components[$compName] = array(
					'path' => $compPath,
					'name' => $compName,
					'slug' => $json['slug']
				);

				$status = (array_key_exists('enabled', $json)) ? $json['enabled'] : false;

				// Set component status
				array_set($this->components[$compName], 'enabled', $status);

				// If component is active, then register namespaces
				if($this->isActive($compName))
				{
					$this->loadRequiredFiles($this->components[$compName]);
					$this->registerFolders($this->components[$compName]);
					$this->registerGlobalNamespace($this->components[$compName]);	
				}

				// Sub Components Register
				$subComponentsJson = (array_key_exists('sub_modules', $json)) ? $json['sub_modules'] : array();

				foreach($subComponentsJson as $sc)
				{
					$path = str_finish($compPath, '/').$sc;
					$this->registerSubComponents(array($path), $compName);
				}
			}
		}

	}

	/**
	 * Register subcomponents in component array
	 * @param  string $components
	 * @param  string $mainCompName
	 * @return void
	 */
	private function registerSubComponents($components, $mainCompName)
	{
		$this->registerComponents($components, $mainCompName);
	}

	/**
	 * Get component status
	 * @param  string  $component
	 * @return boolean
	 */
	public function isActive($component)
	{
		return (array_key_exists($component, $this->components)) ? $this->components[$component]['enabled'] : false;
	}

	/**
	 * [call description]
	 * @param  [type] $controller [description]
	 * @param  string $action     [description]
	 * @param  array  $attr       [description]
	 * @return [type]             [description]
	 */
	public function call($controller, $action = 'index', $attr = array())
	{

		if(str_contains($controller, '::'))
		{
			$widget = explode('::', $controller);
			$controllerCall = $this->jsonFileWorker->getSettingsFile($this->getPath(mb_convert_case($widget[0], MB_CASE_TITLE, "UTF-8")), 'widgets');
			$controller = array_get($controllerCall, $widget[1]);
		}

		$array = array(
			'controller' => $controller,
			'action' => $action,
			'attr' => $attr
		);

		return $this->app['components.hmvc']->get($array);
	}

	/**
	 * Check if is Hmvc Request
	 * @return boolean [description]
	 */
	public function isHmvc()
	{
		return $this->app['components.hmvc']->active;
	}
}