<?php declare (strict_types = 1);

namespace App\Presenters;


class NettePresenter extends BasePresenter
{
	public function actionDefault()
	{
		$this->forward('Homepage:');
	}
}