<?php namespace Levare\Components\Commands;

/**
 * This Class creates an Artisan Command to show all components
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

class ListComponentCommand extends Command {
	
	protected $name = 'component:list';
	protected $description = 'Show all registered components';

	private $main;
	private $worker;
	private $app;

	private $headers = array('Name', 'Slug', 'Path', 'Namespace', 'Status');

	public function __construct(Application $app)
	{
		parent::__construct();

		$this->app = $app;
		$this->main = $app['components'];
		$this->worker = $app['components.worker'];
	}

	public function fire()
	{
		$this->table = $this->getHelperSet()->get('table');

		if (count($this->main->components) == 0)
		{
			return $this->error("Your application doesn't have any components.");
		}

		$this->displayComponents($this->getComponents());
	}

	protected function displayComponents(array $components)
	{
		$this->table->setHeaders($this->headers)->setRows($components);
		$this->table->render($this->getOutput());
	}

	protected function getComponents()
	{
		foreach($this->main->components as $key => $comp)
		{
			$out[] = array(
				'name' => $comp['name'],
				'slug' => $comp['slug'],
				'path' => $comp['path'],
				'namespace' => $this->app['config']->get('components::name').'\\'.str_replace('_', '\\', $comp['name']),
				'status' => ($this->main->isActive($comp['name'])) ? 'enabled' : 'disabled'
			);
		}

		return $out;
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