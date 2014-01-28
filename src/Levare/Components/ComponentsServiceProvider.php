<?php namespace Levare\Components;

use Illuminate\Support\ServiceProvider;

class ComponentsServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('levare/components');

		// Require helpers.php
		require_once 'helpers.php';

		// Before Method
		$this->app['components']->before();
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		
		$this->registerJsonFileWorker();
		$this->registerWorker();
		$this->registerComponent();

		$this->registerCommandCreate();
		$this->registerCommandDelete();
		// $this->registerCommandArchive();
		$this->registerCommandList();

	}

	/**
	 * Register the components.jsonfileworker class
	 * @return JsonFileWorker
	 */
	private function registerJsonFileWorker()
	{
		$this->app['components.jsonfileworker'] = $this->app->share(function($app)
		{
			return new JsonFileWorker($app);
		});
	}

	/**
	 * Register the component class
	 * @return Components
	 */
	private function registerComponent()
	{
		$this->app['components'] = $this->app->share(function($app)
		{
			return new Components($app);
		});
	}

	/**
	 * Register Worker Class
	 * @return Worker
	 */
	private function registerWorker()
	{
		$this->app['components.worker'] = $this->app->share(function($app)
		{
			return new Worker($app);
		});
	}

	/**
	 * Register Component Create Command
	 */
	private function registerCommandCreate()
	{
		$this->app['command.component.create'] = $this->app->share(function($app)
		{
			return new Commands\CreateComponentCommand($app);
		});
		$this->commands('command.component.create');
	}

	/**
	 * Register Component Delete Command
	 */
	private function registerCommandDelete()
	{
		$this->app['command.component.delete'] = $this->app->share(function($app)
		{
			return new Commands\DeleteComponentCommand($app);
		});
		$this->commands('command.component.delete');
	}

	/**
	 * Register Component Archive Command
	 */
	private function registerCommandArchive()
	{
		$this->app['command.component.archive'] = $this->app->share(function($app)
		{
			return new Commands\ArchiveComponentCommand($app);
		});
		$this->commands('command.component.archive');
	}

	/**
	 * Register Component List Command
	 */
	private function registerCommandList()
	{
		$this->app['command.component.list'] = $this->app->share(function($app)
		{
			return new Commands\ListComponentCommand($app);
		});
		$this->commands('command.component.list');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}