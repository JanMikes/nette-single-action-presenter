<?php

namespace App;

use App\Presenters\HomepagePresenter;
use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;


class RouterFactory
{
	use Nette\StaticClass;

	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter()
	{
		$router = new RouteList;
		$router[] = new Route('<presenter>/<action>', 'Homepage:default');
		// @note: idea?
		// $router[] = new Route('<presenter>/<action>', HomepagePresenter::class);
		return $router;
	}

}
