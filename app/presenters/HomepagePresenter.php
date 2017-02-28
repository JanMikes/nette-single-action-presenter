<?php

namespace App\Presenters;

use Nette\Application\IResponse;
use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Presenter;


final class HomepagePresenter extends Presenter
{

	public function run(Request $request): IResponse
	{
		$template = $this->getTemplateFactory()
			->createTemplate($this);
		$template->setFile(__DIR__ . '/templates/Homepage/default.latte');

		return new TextResponse($template);
	}

}
