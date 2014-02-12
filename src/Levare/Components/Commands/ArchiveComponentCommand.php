<?php namespace Levare\Components\Commands;

/**
 * This Class creates an Artisan Command to create an Archiv from an component
 *
 * @todo Write Documentation, write better method comments
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

class ArchiveComponentCommand extends Command {
	
	protected $name = 'component:archive';
	protected $description = 'Create a Archive from Component';

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