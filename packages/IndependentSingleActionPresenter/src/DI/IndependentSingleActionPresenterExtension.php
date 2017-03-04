<?php declare(strict_types=1);

namespace IndependentSingleActionPresenter\DI;

use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;

final class IndependentSingleActionPresenterExtension extends CompilerExtension
{

	public function loadConfiguration()
	{
		Compiler::loadDefinitions(
			$this->getContainerBuilder(),
			$this->loadFromFile(__DIR__. '/../config/services.neon')
		);
	}

}
