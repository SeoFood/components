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
		$this->app['components.jsonfileworker'] = $this->app->share(function($app)
		{
			return new JsonFileWorker($app);
		});

		$this->app['components'] = $this->app->share(function($app)
		{
			return new Components($app);
		});
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