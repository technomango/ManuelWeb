<?php

/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */
defined('_AKEEBA') or die();

class AngieModelWordpressSetup extends AngieModelBaseSetup
{
	/** @inheritDoc */
	protected function getSiteParamsVars()
	{
		$siteurl = str_replace('/installation/', '', AUri::root());
		$homeurl = str_replace('/installation/', '', AUri::root());

		$ret = [
			'blogname'        => $this->getState('blogname', $this->configModel->get('blogname', 'Restored website')),
			'blogdescription' => $this->getState('blogdescription', $this->configModel->get('blogdescription', 'Restored website')),
			'dbcharset'       => $this->getState('dbcharset', $this->configModel->get('dbcharset', 'utf_8')),
			'dbcollation'     => $this->getState('dbcollation', $this->configModel->get('dbcollation', '')),
			'homeurl'         => $this->getState('homeurl', $homeurl),
			'siteurl'         => $this->getState('siteurl', $siteurl),
		];

		require_once APATH_INSTALLATION . '/angie/helpers/setup.php';

		$ret['homeurl'] = AngieHelperSetup::cleanLiveSite($ret['homeurl']);
		$ret['siteurl'] = AngieHelperSetup::cleanLiveSite($ret['siteurl']);

		$this->configModel->set('siteurl', $ret['siteurl']);
		$this->configModel->set('homeurl', $ret['homeurl']);

		// Special handling: if we were told to downgrade data from utf8mb4 to utf8 and the dbcharset or dbcollation
		// contains utf8mb4 we have to downgrade that too to utf8.
		/** @var AngieModelDatabase $dbModel */
		$dbModel       = AModel::getTmpInstance('Database', 'AngieModel');
		$allDbIni      = $dbModel->getDatabasesJson();
		$dbNames       = $dbModel->getDatabaseNames();
		$firstDb       = array_shift($dbNames);
		$dbIni         = $allDbIni[$firstDb];
		$dbOptions     = [
			'driver'   => $dbIni['dbtype'],
			'database' => $dbIni['dbname'],
			'select'   => 0,
			'host'     => $dbIni['dbhost'],
			'user'     => $dbIni['dbuser'],
			'password' => $dbIni['dbpass'],
			'prefix'   => $dbIni['prefix'],
		];
		$db            = ADatabaseDriver::getInstance($dbOptions);
		$downgradeUtf8 = $dbIni['utf8tables'] && (
				!$dbIni['utf8mb4']
				|| ($dbIni['utf8mb4'] && !$db->supportsUtf8mb4())
			);

		if ($downgradeUtf8)
		{
			$ret['dbcharset']   = str_replace('utf8mb4', 'utf8', $ret['dbcharset']);
			$ret['dbcollation'] = str_replace('utf8mb4', 'utf8', $ret['dbcollation']);

			$this->configModel->set('dbcharset', $ret['dbcharset']);
			$this->configModel->set('dbcollation', $ret['dbcollation']);
		}

		return $ret;
	}

	/** @inheritDoc */
	protected function getSuperUsersVars()
	{
		$ret = [];

		try
		{
			// Connect to the database
			$db = $this->getDatabase();
		}
		catch (Exception $exc)
		{
			return $ret;
		}

		try
		{
			// Options are stored with the table prefix in front of it
			$table_prefix = $this->configModel->get('olddbprefix');

			// Deprecated value, but it's still used...
			$query      = $db->getQuery(true)
				->select($db->qn('user_id'))
				->from($db->qn('#__usermeta'))
				->where($db->qn('meta_key') . ' = ' . $db->q($table_prefix . 'user_level'))
				->where($db->qn('meta_value') . ' = ' . $db->q(10));
			$deprecated = $db->setQuery($query)->loadColumn();

			// Current usage. Roles are stored as serialized arrays, so I have to get them all and check one by one
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('#__usermeta'))
				->where($db->qn('meta_key') . ' = ' . $db->q($table_prefix . 'capabilities'));
			$users = $db->setQuery($query)->loadObjectList();

			$current = [];

			foreach ($users as $user)
			{
				$roles = unserialize($user->meta_value);

				if (isset($roles['administrator']) && $roles['administrator'])
				{
					$current[] = $user->user_id;
				}
			}

			$admins = array_intersect($current, $deprecated);
		}
		catch (Exception $exc)
		{
			return $ret;
		}

		// Get the user information for the Super Administrator users
		try
		{
			$query             = $db->getQuery(true)
				->select([
					$db->qn('ID') . ' AS ' . $db->qn('id'),
					$db->qn('user_login') . ' AS ' . $db->qn('username'),
					$db->qn('user_email') . ' AS ' . $db->qn('email'),
				])
				->from($db->qn('#__users'))
				->where($db->qn('ID') . ' IN(' . implode(',', $admins) . ')');
			$ret['superusers'] = $db->setQuery($query)->loadObjectList(0);
		}
		catch (Exception $exc)
		{
			return $ret;
		}

		return $ret;
	}

	/** @inheritDoc */
	public function applySettings()
	{
		// Get the state variables and update the global configuration
		$stateVars = $this->getStateVariables();

		// Apply server config changes
		$this->applyServerconfigchanges();

		// -- General settings
		$this->configModel->set('blogname', $stateVars->blogname);
		$this->configModel->set('blogdescription', $stateVars->blogdescription);
		$this->configModel->set('siteurl', $stateVars->siteurl);
		$this->configModel->set('homeurl', $stateVars->homeurl);

		// -- Database settings
		$connectionVars = $this->getDbConnectionVars();
		$this->configModel->set('dbtype', $connectionVars->dbtype);
		$this->configModel->set('dbhost', $connectionVars->dbhost);
		$this->configModel->set('dbuser', $connectionVars->dbuser);
		$this->configModel->set('dbpass', $connectionVars->dbpass);
		$this->configModel->set('dbname', $connectionVars->dbname);
		$this->configModel->set('dbprefix', $connectionVars->prefix);
		$this->configModel->set('dbcharset', $stateVars->dbcharset);
		$this->configModel->set('dbcollation', $stateVars->dbcollation);

		// -- Override the secret key
		$this->configModel->set('auth_key', substr(base64_encode(random_bytes(64)), 0, 64));
		$this->configModel->set('secure_auth_key', substr(base64_encode(random_bytes(64)), 0, 64));
		$this->configModel->set('logged_in_key', substr(base64_encode(random_bytes(64)), 0, 64));
		$this->configModel->set('nonce_key', substr(base64_encode(random_bytes(64)), 0, 64));
		$this->configModel->set('auth_salt', substr(base64_encode(random_bytes(64)), 0, 64));
		$this->configModel->set('secure_auth_salt', substr(base64_encode(random_bytes(64)), 0, 64));
		$this->configModel->set('logged_in_salt', substr(base64_encode(random_bytes(64)), 0, 64));
		$this->configModel->set('nonce_salt', substr(base64_encode(random_bytes(64)), 0, 64));

		// Update the base directory, if present
		$base = $this->configModel->get('base', null);

		if (!is_null($base))
		{
			$this->configModel->set('base', $this->configModel->getNewBasePath());
		}

		// Save the configuration to the session
		$this->configModel->saveToSession();

		// Sanity check
		if (!$stateVars->homeurl)
		{
			throw new Exception(AText::_('SETUP_HOMEURL_REQUIRED'));
		}

		if (!$stateVars->siteurl)
		{
			$this->configModel->set('siteurl', $stateVars->homeurl);
		}

		// Apply the Super Administrator changes
		$this->applySuperAdminChanges();

		// Apply the site name (blogname) and tag line (blogdescription)
		$this->applySiteName();

		// Get the wp-config.php file and try to save it
		if (!$this->configModel->writeConfig(APATH_SITE . '/wp-config.php'))
		{
			return false;
		}

		return true;
	}

	/**
	 * Apply the site name (blogname) and tag line (blogdescription)
	 *
	 * @return  void
	 */
	private function applySiteName()
	{
		// Connect to the database
		$db = $this->getDatabase();

		foreach (['blogname', 'blogdescription'] as $optionName)
		{
			$optionValue = $this->configModel->get($optionName, null);

			if (empty($optionValue))
			{
				continue;
			}

			try
			{
				$db->setQuery($db->getQuery(true)
					->update($db->qn('#__options'))
					->set($db->qn('option_value') . ' = ' . $db->q($optionValue))
					->where($db->qn('option_name') . ' = ' . $db->q($optionName))
				)->execute();
			}
			catch (Exception $e)
			{
				continue;
			}
		}
	}

	private function applySuperAdminChanges()
	{
		// Get the Super User ID. If it's empty, skip.
		$id = $this->getState('superuserid', 0);
		if (!$id)
		{
			return false;
		}

		// Get the Super User email and password
		$email     = $this->getState('superuseremail', '');
		$password1 = $this->getState('superuserpassword', '');
		$password2 = $this->getState('superuserpasswordrepeat', '');

		// If the email is empty but the passwords are not, fail
		if (empty($email))
		{
			if (empty($password1) && empty($password2))
			{
				return false;
			}
			else
			{
				throw new Exception(AText::_('SETUP_ERR_EMAILEMPTY'));
			}
		}

		// If the passwords are empty, skip
		if (empty($password1) && empty($password2))
		{
			return false;
		}

		// Make sure the passwords match
		if ($password1 != $password2)
		{
			throw new Exception(AText::_('SETUP_ERR_PASSWORDSDONTMATCH'));
		}

		// Connect to the database
		$db = $this->getDatabase();

		/**
		 * Create a new encrypted password.
		 *
		 * This uses the Phpass library which has been present in WordPress 2.5 and later
		 */
		$hasher = new AUtilsPwhash(8, true);
		$crypt  = $hasher->HashPassword($password1);

		// Update the database record
		$query = $db->getQuery(true)
			->update($db->qn('#__users'))
			->set($db->qn('user_pass') . ' = ' . $db->q($crypt))
			->set($db->qn('user_email') . ' = ' . $db->q($email))
			->where($db->qn('ID') . ' = ' . $db->q($id));
		$db->setQuery($query)->execute();

		return true;
	}

	/**
	 * Detects if there is an auto-prepend file (for example WordFence firewall plugin)
	 *
	 * @return bool|string        False if auto-prepend is not enabled, otherwise the name of the config file that sets
	 *                            the directive
	 */
	public function hasAutoPrepend()
	{
		$config_files = [
			'.htaccess',
			'htaccess.bak',
			'.user.ini',
			'.user.ini.bak',
			'php.ini',
			'php.ini.bak',
		];

		// Auto-prepend value could be in different places. Search in all of them, if we get an hit, let's stop
		foreach ($config_files as $configFile)
		{
			$full_path = APATH_ROOT . '/' . $configFile;

			if (!file_exists($full_path))
			{
				continue;
			}

			$contents = file_get_contents($full_path);

			// The "auto_prepend_file" name is always the same in all those files, it only changes the way it's set
			if (strpos($contents, 'auto_prepend_file') !== false)
			{
				return $full_path;
			}
		}

		return false;
	}

	/**
	 * Applies server configuration changes (removing/renaming server configuration files)
	 */
	private function applyServerconfigchanges()
	{
		if ($this->input->get('disable_autoprepend'))
		{
			// If everything went fine, let's set a variable flag so we can remember the user to re-enable them
			if ($this->disable_autoprepend())
			{
				$this->container->session->set('autoprepend_disabled', true);
				$this->container->session->saveData();
			}
		}

		if ($this->input->get('removehtpasswd'))
		{
			$this->removeHtpasswd(APATH_ROOT . '/wp-admin/');
		}

		$htaccessHandling = $this->getState('htaccessHandling', 'none');
		$this->applyHtaccessHandling($htaccessHandling);
	}

	private function disable_autoprepend()
	{
		$configPath = $this->hasAutoPrepend();

		if (!$configPath)
		{
			return true;
		}

		$contents   = file_get_contents($configPath);
		$contents   = explode("\n", $contents);
		$configFile = basename($configPath);

		$new_config = '';

		foreach ($contents as $line)
		{
			if (strpos($line, 'auto_prepend_file') !== false)
			{
				// Apply the correct comment depending on the type of file
				if (strpos($configFile, '.ini') !== false)
				{
					$line = '; ' . $line;
				}
				elseif (strpos($configFile, 'htaccess') !== false)
				{
					$line = '# ' . $line;
				}
			}

			$new_config .= $line . "\n";
		}

		// Finally write it back
		file_put_contents($configPath, $new_config);

		return true;
	}


	/**
	 * Checks if the current site has htpasswd files
	 *
	 * @return bool
	 */
	public function hasHtpasswd()
	{
		$files = [
			'wp-admin/.htaccess',
			'wp-admin/.htpasswd',
		];

		foreach ($files as $file)
		{
			if (file_exists(APATH_ROOT . '/' . $file))
			{
				return true;
			}
		}

		return false;
	}
}
