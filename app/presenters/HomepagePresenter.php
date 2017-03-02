<?php

namespace App\Presenters;

use Nette\Application\IResponse;
use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Presenter;
use SingleActionPresenter\Template\TemplateRenderer;


final class HomepagePresenter extends Presenter
{

	/**
	 * @var TemplateRenderer
	 */
	private $templateRenderer;


	public function __construct(TemplateRenderer $templateRenderer)
	{
		$this->templateRenderer = $templateRenderer;
	}


	public function run(Request $request): IResponse
	{
		return new TextResponse(
			$this->templateRenderer->renderFileWithParameters(
				__DIR__ . '/templates/Homepage.latte'
			)
		);
	}

}
