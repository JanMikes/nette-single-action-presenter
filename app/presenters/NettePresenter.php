<?php declare (strict_types = 1);
/**
 * @author      Jan Mikes <mikes@v-i-c.eu>
 * @copyright   Velveth International Corporation <v-i-c.eu>
 */

namespace App\Presenters;


class NettePresenter extends BasePresenter
{
	public function actionDefault()
	{
		$this->forward('Homepage:');
	}
}