<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_AKEEBA') or die();

class AngieViewSetup extends AView
{
	/** @var stdClass */
	public $stateVars;

	public $auto_prepend = [];

	public $hasAutoPrepend = false;

	public $removeHtpasswdOptions = [];

	public $hasHtaccess = false;

	public $htaccessOptionSelected = 'none';

	public $htaccessOptions = [];

	public function onBeforeMain()
	{
		/** @var AngieModelWordpressSetup $model */
		$model = $this->getModel();

		$this->stateVars      = $model->getStateVariables();
		$this->hasAutoPrepend = $model->hasAutoPrepend();

		// Prime the options array with some default info
		$this->auto_prepend = [
			'checked'  => '',
			'disabled' => '',
		];

		$this->removeHtpasswdOptions = [
			'checked'  => '',
			'disabled' => '',
			'help'     => 'SETUP_LBL_SERVERCONFIG_REMOVEHTPASSWD_HELP',
		];

		// If we are restoring to a new server everything is checked by default
		if ($model->isNewhost())
		{
			$this->auto_prepend['checked']          = 'checked="checked"';
			$this->removeHtpasswdOptions['checked'] = 'checked="checked"';
		}

		// If any option is not valid (ie missing files) we gray out the option AND remove the check
		// to avoid user confusion
		if (!$this->hasAutoPrepend)
		{
			$this->auto_prepend['checked']  = '';
			$this->auto_prepend['disabled'] = 'disabled="disabled"';
		}

		if (!$model->hasHtpasswd())
		{
			$this->removeHtpasswdOptions['disabled'] = 'disabled="disabled"';
			$this->removeHtpasswdOptions['checked']  = '';
			$this->removeHtpasswdOptions['help']     = 'SETUP_LBL_SERVERCONFIG_NONEED_HELP';
		}


		$this->loadHelper('select');

		$this->hasHtaccess            = $model->hasHtaccess();
		$this->htaccessOptionSelected = 'none';

		$options = ['none'];

		if ($model->hasAddHandler())
		{
			$options[] = 'removehandler';

			$this->htaccessOptionSelected = $model->isNewhost() ? 'removehandler' : 'none';
		}

		if ($model->hasAddHandler())
		{
			$options[] = 'replacehandler';

			$this->htaccessOptionSelected = 'replacehandler';
		}

		$this->htaccessOptionSelected = $model->getState('htaccessHandling', $this->htaccessOptionSelected);

		foreach ($options as $opt)
		{
			$this->htaccessOptions[] = AngieHelperSelect::option($opt, AText::_('SETUP_LBL_HTACCESSCHANGE_' . $opt));
		}

		return true;
	}
}
