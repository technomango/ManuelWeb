<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_AKEEBA') or die();

class AngieControllerWordpressFinalise extends AngieControllerBaseFinalise
{
	public function ajax()
	{
		$method = $this->input->getCmd('method', '');
		$result = false;
		$model = $this->getThisModel();

		if (method_exists($model, $method))
		{
			try
			{
				$result = $model->$method();
			}
			catch(Exception $e)
			{
				$result = false;
			}
		}

		@ob_clean();
		echo json_encode($result);
	}
}
