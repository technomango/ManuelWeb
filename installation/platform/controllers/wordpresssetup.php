<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_AKEEBA') or die();

class AngieControllerWordpressSetup extends AngieControllerBaseSetup
{
    /**
     * I have to override parent method since I have the replacedata extra step
     *
     * @throws AExceptionApp
     */
	public function apply()
	{
		/** @var AngieModelWordpressSetup $model */
		$model = $this->getThisModel();

		try
		{
			$writtenConfiguration = $model->applySettings();
			$msg = null;
			$this->container->session->set('writtenConfiguration', $writtenConfiguration);

			$url = 'index.php?view=replacedata';
		}
		catch (Exception $exc)
		{
			$msg = $exc->getMessage();
			$url = 'index.php?view=setup';
		}

		$this->container->session->saveData();

		$this->setRedirect($url, $msg, 'error');

        // Encode the result if we're in JSON format
        if($this->input->getCmd('format', '') == 'json')
        {
            $result['error'] = $msg;

	        @ob_clean();
            echo json_encode($result);
        }
	}
}
