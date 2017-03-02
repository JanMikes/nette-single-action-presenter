<?php

namespace App\Presenters;

use Nette\Application\IResponse;
use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\ITemplateFactory;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\TemplateFactory;


final class HomepagePresenter extends Presenter
{

	/**
	 * @var TemplateFactory
	 */
	private $templateFactory;

	public function __construct(ITemplateFactory $templateFactory)
	{
		$this->templateFactory = $templateFactory;
	}

	public function run(Request $request): IResponse
	{
		$template = $this->templateFactory->createTemplate();
		$latte = $template->getLatte();
		$latte->addProvider('uiControl', new UiPresenter());

		return new TextResponse(
			$template->render(__DIR__ . '/templates/Homepage.latte')
		);
	}
}
