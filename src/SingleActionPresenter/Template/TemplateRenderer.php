<?php declare(strict_types=1);

namespace SingleActionPresenter\Template;

use Nette\Application\UI\ITemplateFactory;
use Nette\Bridges\ApplicationLatte\TemplateFactory;
use SingleActionPresenter\Application\PresenterHelper;


final class TemplateRenderer // @todo: add interface
{

	/**
	 * @var TemplateFactory
	 */
	private $templateFactory;

	/**
	 * @var PresenterHelper
	 */
	private $presenterHelper;


	public function __construct(ITemplateFactory $templateFactory, PresenterHelper $presenterHelper)
	{
		$this->templateFactory = $templateFactory;
		$this->presenterHelper = $presenterHelper;
	}


	public function renderFileWithParameters(string $file, array $parameters = []): string
	{
		$template = $this->templateFactory->createTemplate();
		$latte = $template->getLatte();

		$layout = $this->guessLayoutFromFile($file);
		$this->presenterHelper->setLayout($layout);
		$latte->addProvider('uiControl', $this->presenterHelper);

		return $latte->renderToString($file, $parameters + $template->getParameters());
	}


	private function guessLayoutFromFile(string $file): string
	{
		// @todo: add test for {extends "..."} later
		$possibleLayoutLocations = [];
		$possibleLayoutLocations[] = dirname($file) . DIRECTORY_SEPARATOR . '@layout.latte';
		foreach ($possibleLayoutLocations as $possibleLayoutLocation) {
			if (file_exists($possibleLayoutLocation)) {
				return $possibleLayoutLocation;
			}
		}

		// @todo: fail here
		return '';
	}

}
