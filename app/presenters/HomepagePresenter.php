<?php

namespace App\Presenters;

use Nette\Application\IPresenter;
use Nette\Application\IResponse;
use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\ITemplateFactory;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\TemplateFactory;


final class HomepagePresenter extends Presenter implements IPresenter
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
		$template = $this->templateFactory->createTemplate($this);
		$template->setFile(__DIR__ . '/templates/Homepage.latte');

		return new TextResponse($template);
	}

}
