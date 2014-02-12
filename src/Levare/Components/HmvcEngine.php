<?php namespace Levare\Components;

/**
 * This Class is inspired by papajoker <https://github.com/papajoker>
 *
 * Modified by Florian Uhlrich <f.uhlrich@levare-cms.de> to use it with
 * Compontents
 *
 * @package Levare\Components
 * @author Florian Uhlrich <f.uhlrich@levare-cms.de>
 * @copyright Copyright (c) 2013 by Levare Project Team
 * @version 1.0
 * @access public
 */
use Illuminate\View\Engines\EngineInterface;
use Illuminate\Foundation\Application;

class HmvcEngine implements EngineInterface {

	/**
	 * [$app description]
	 * @var [type]
	 */
	protected $app;

	/**
	 * [$controller description]
	 * @var boolean
	 */
	private $controller = false;

	/**
	 * [$attr description]
	 * @var boolean
	 */
	private $attr = false;

	/**
	 * [$action description]
	 * @var string
	 */
	private $action = 'index';

	/**
	 * [__construct description]
	 * @param Application $app [description]
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	/**
	 * [get description]
	 * @param  [type] $path [description]
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	public function get($path, array $data = array())
	{
		$this->extract($data);
		$this->setConfig($path, $data);

		return $this->run();
	}

	/**
	 * [setConfig description]
	 * @param [type] $path [description]
	 * @param array  $data [description]
	 */
	protected function setConfig($path, array $data)
	{
		$conf = (is_array($path)) ? $path : $this->app['files']->getRequire($path);
		$this->controller = $conf['controller'];

		$this->attr = (array_key_exists('attr',$conf)) ? $conf['attr'] : array();

		// overwrite attr in file hmvc 
		$this->attr = array_merge($this->attr, $data);
		$this->action = (array_key_exists('action',$conf)) ? $conf['action'] : 'index';

	}

	/**
	 * [run description]
	 * @return [type] [description]
	 */
	protected function run()
	{
		$controller = new $this->controller();

		return call_user_func_array(array($controller, $this->action), $this->attr);
		// return $controller->{$this->action}(
		// 	$this->attr
		// );
	}

	/**
	 * [extract description]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	private function extract(&$data)
	{
		unset($data['__env']);
		unset($data['app']);
		unset($data['errors']);
		unset($data['path']);
	}

}