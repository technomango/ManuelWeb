<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Akeeba\Replace\Engine\ErrorHandling\WarningException;

defined('_AKEEBA') or die();

class AngieControllerWordpressReplacedata extends AController
{
	public function main()
	{
		/** @var AngieModelWordpressReplacedata $model */
		$model        = $this->getThisModel();
		$sameSiteURL  = $model->isSameSiteURL();
		$sameFileRoot = $model->isSameFilesystemRoot();

		// If we are restoring to the same URL and filesystem root (identical site) we don't need to replace any data
		if ($sameSiteURL && $sameFileRoot)
		{
			$this->setRedirect('index.php?view=finalise');

			return;
		}

		// Am I force reloading the data replacements?
		if ($this->input->getBool('force', false))
		{
			$session = $this->container->session;
			$session->set('dataReplacements', null);
		}

		parent::main();
	}

	public function ajax()
	{
		/** @var AngieModelWordpressReplacedata $model */
		$model  = $this->getThisModel();
		$method = $this->input->getCmd('method', '');

		try
		{
			switch ($method)
			{
				case 'init':
					$status = $model->init();
					break;

				case 'step':
					$status = $model->step();
					break;
			}

			$this->container->session->saveData();

			$error    = $status->getError();
			$warnings = $status->getWarnings();
			$hasError = is_object($error) && ($error instanceof Exception);

			$result = [
				'error'    => $hasError ? $error->getMessage() : '',
				'msg'      => $status->getDomain() . ' ' . $status->getStep() . ' ' . $status->getSubstep(),
				'more'     => !$status->isDone() && !$hasError,
				'warnings' => array_map(function (WarningException $w) {
					return $w->getMessage();
				}, $warnings),
			];

			if ($hasError)
			{
				$result['msg'] = $error->getCode() . ': ' . $error->getMessage();
			}

			// Perform finalization steps (file data replacement when we're done)
			if ($status->isDone())
			{
				// First we need to update the multisite tables, if necessary.
				if ($model->isMultisite())
				{
					$model->updateMultisiteTables();
				}
				$model->updateSiteOptions();
				$model->updateAttachmentGUIDs();
				$model->updateFiles();
				$model->updateWPConfigFile();
			}
		}
		catch (Exception $e)
		{
			$result = [
				'error'    => $e->getMessage(),
				'msg'      => $e->getCode() . ': ' . $e->getMessage(),
				'more'     => false,
				'warnings' => [],
			];
		}

		@ob_clean();
		echo json_encode($result);
	}

	public function replaceneeded()
	{
		/** @var AngieModelWordpressConfiguration $config */
		$config = AModel::getAnInstance('Configuration', 'AngieModel', [], $this->container);
		$result = true;

		/**
		 * When we initialised AngieModelWordpressConfiguration it read the oldurl from the database. However, at that
		 * point (in the "main" view) we had not already restored the database. Therefore it was either unable to read
		 * anything or it was reading false data from an existing database. Therefore I need to reload that information
		 * and set it to the configuration object.
		 */
		$array = [];
		$config->getOptionsFromDatabase($array);

		if (isset($array['oldurl']))
		{
			$config->set('oldurl', $array['oldurl']);
			$config->saveToSession();
		}

		// These values are stored inside the session, after the setup step
		$old_url = $config->get('oldurl');
		$new_url = $config->get('siteurl');

		// If we are restoring to the same URL we don't need to replace any data
		if ($old_url == $new_url)
		{
			$result = false;
		}

		@ob_clean();
		echo json_encode($result);
	}
}
