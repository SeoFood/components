<?php namespace Levare\Components\Facades;

use Illuminate\Support\Facades\Facade;

class Components extends Facade {

	protected static function getFacadeAccessor()
	{
		return "components";
	}

}