<?php namespace Levare\Components\Commands;

/**
 * This Class creates an Artisan Command to delete a Component
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

class DeleteComponentCommand extends Command {
	
	protected $name = 'component:delete';
	protected $description = 'Delete an existing component';

	private $main;
	private $worker;

	public function __construct(Application $app)
	{
		parent::__construct();

		$this->main = $app['components'];
		$this->worker = $app['components.worker'];
	}

	public function fire()
	{
		$this->info('Welcome to component delete');
		$modules = $this->main->components;
		while(1) {

			$i = 0;
			foreach($modules as $mod)
			{
				$i++;
				$this->comment('[' . $i . '] ' . $mod['name']);
				$componentPath[$i] = $mod['path'];
				$modName[$i] = $mod['name'];
			}

			$modNumber = $this->ask('Select Number of Module');

			if($modNumber <= 0 || $modNumber > $i)
			{
				$this->error('Please try a valid number');
				continue;
			}
			else
			{
				$this->info('Ok, i found the Component Path. Let`s go to next part');
				// $this->comment($componentPath[$modNumber]);
				$name = $modName[$modNumber];
				break 1;
			}
		}

		$confirm = $this->confirm('Would you like really delete this component and all existing folders and files? [default: no]', false);

		if($confirm)
		{
			$this->worker->beforeDelete($modName[$modNumber], $componentPath[$modNumber]);
			$this->worker->deleteModule();
		}
		else
		{
			$this->info('Thank you for using the Components Package.');
		}
			
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