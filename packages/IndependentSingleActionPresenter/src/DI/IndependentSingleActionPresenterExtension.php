<?php declare(strict_types=1);

namespace IndependentSingleActionPresenter\DI;

use IndependentSingleActionPresenter\Template\TemplateRenderer;
use Nette\Application\UI\ITemplateFactory;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;

final class IndependentSingleActionPresenterExtension extends CompilerExtension
{

	public function loadConfiguration()
	{
		$containerBuilder = $this->getContainerBuilder();

		Compiler::loadDefinitions(
			$containerBuilder,
			$this->loadFromFile(__DIR__. '/../config/services.neon')
		);

		if ($containerBuilder->findByType(ITemplateFactory::class)) {
			$containerBuilder->addDefinition($this->prefix('templateRenderer'))
				->setClass(TemplateRenderer::class);
		}
	}

}
