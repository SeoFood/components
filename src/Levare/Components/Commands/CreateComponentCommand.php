<?php namespace Levare\Components\Commands;

/**
 * This Class creates an Artisan Command to create a Component
 *
 * @todo Write Documentation
 *
 * @package levare\components
 * @author Florian Uhlrich <f.uhlrich@levare-cms.de>
 * @copyright Copyright (c) 2013 by Levare Project Team
 * @version 1.0alpha
 * @access public
 */

use Illuminate\Foundation\Application;
use Illuminate\Console\Command;
use Levare\Components\Components as Comp;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CreateComponentCommand extends Command {
	
	/**
	 * Command Name
	 * @var string
	 */
	protected $name = 'component:create';
	
	/**
	 * Command Description
	 * @var string
	 */
	protected $description = 'Create a new component';

	/**
	 * Component Class
	 * @var Levare\Components\Components
	 */
	private $main;

	/**
	 * Worker Class
	 * @var Levare\Componets\Worker
	 */
	private $worker;

	public function __construct(Application $app)
	{
		parent::__construct();

		$this->main = $app['components'];
		$this->worker = $app['components.worker'];
	}

	/**
	 * Fire the Artisan Command
	 * @return void
	 */
	public function fire()
	{
		$this->line('Welcome to create a new component');
		$modules = $this->main->components;
		
		$name = ucfirst($this->ask('Name of component:'));
		$mainmodule = $this->confirm('Is Core Module? [default: yes]', true);

		if($mainmodule)
		{
			while(1) {
				if(array_key_exists($name, $modules))
				{
					$this->error('An component with this name already exists, please choose another one');
					$name = ucfirst($this->ask('Name of component:'));
					continue;
				}
				else
				{
					$this->info('Name of Component valid. Go to next Step.');
					$this->worker->beforeCreate($name);
					$this->createCommand();
					break 1;
				}
			}

		}
		else
		{
			while(1) {

				$i = 0;
				foreach($modules as $mod)
				{
					$i++;
					$this->comment('[' . $i . '] ' . $mod['name']);
					$componentPath[$i] = $mod['path'];
					$modName[$i] = $mod['name'];
				}

				$modNumber = $this->ask('Select Number of Core Module');

				if($modNumber <= 0 || $modNumber > $i)
				{
					$this->error('Please try a valid number');
					continue;
				}
				else
				{
					$this->info('Ok, i found the Component Path. Let`s go to next part');
					// $this->comment($componentPath[$modNumber]);
					$name = $modName[$modNumber] . '_' . $name;
					break 1;
				}
			}

			$this->worker->beforeCreate($name, $componentPath[$modNumber]);

			$this->createCommand();
		}

			
	}

	/**
	 * Create a new Component
	 * @return void
	 */
	private function createCommand()
	{
		$createDefaultFolder = $this->confirm('Would you create all default folders (e.g controllers, views, models,...) [default: yes]', true);

		if($createDefaultFolder)
		{
			$this->worker->commandCreate();
		}
		else
		{
			$this->worker->createFolder();
			$this->worker->createSettingsFile();

			$createRoutesFile = $this->confirm('Would you create a routes.php? [default: yes]', true);

			if($createRoutesFile)
			{
				$this->worker->createRoutesFile();
			}
		}

		$this->info('Your Component was successfully created. Thank you for using the Components Package.');
	}

	public function getArguments()
	{
		return array();
	}

	public function getOptions()
	{
		return array();
	}

}