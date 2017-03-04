<?php

namespace App\Presenters;

use Nette\Application\IPresenter;
use Nette\Application\IResponse;
use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use SingleActionPresenter\Template\TemplateRenderer;


final class HomepagePresenter
{

	/**
	 * @var TemplateRenderer
	 */
	private $templateRenderer;


	public function __construct(TemplateRenderer $templateRenderer)
	{
		$this->templateRenderer = $templateRenderer;
	}


	public function __invoke(Request $request): IResponse
	{
		return new TextResponse(
			$this->templateRenderer->renderFileWithParameters(
				__DIR__ . '/templates/Homepage.latte'
			)
		);
	}

}
