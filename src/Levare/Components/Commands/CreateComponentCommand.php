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
	
	protected $name = 'component:create';
	protected $description = 'Create a new component';

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
		$this->line('Welcome to create a new component');
		$name = ucfirst($this->ask('Name of component:'));

		if(array_key_exists($name, $this->main->components))
		{
			$this->error('An component with this name already exists, please choose another one');
		}
		else
		{
			$this->worker->commandCreate($name);
			$this->info("Component $name successfully created");
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