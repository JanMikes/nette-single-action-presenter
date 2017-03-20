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
		$builder = $this->getContainerBuilder();

		Compiler::loadDefinitions(
			$builder,
			$this->loadFromFile(__DIR__. '/../config/services.neon')
		);

		if ($builder->findByType(ITemplateFactory::class)) {
			$builder->addDefinition($this->prefix('templateRenderer'))
				->setClass(TemplateRenderer::class);
		}
	}

}
