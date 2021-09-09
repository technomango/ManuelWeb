<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Akeeba\Replace\Database\Driver;
use Akeeba\Replace\Engine\Core\Configuration;
use Akeeba\Replace\Engine\Core\Helper\MemoryInfo;
use Akeeba\Replace\Engine\Core\Part\Database;
use Akeeba\Replace\Engine\PartStatus;
use Akeeba\Replace\Logger\NullLogger;

defined('_AKEEBA') or die();

class AngieModelWordpressReplacedata extends AModel
{
	/** @var array The replacements to conduct */
	private $replacements = [];

	/** @var ADatabaseDriver Reference to the database driver object */
	private $db = null;

	public function __construct(array $config = [], AContainer $container = null)
	{
		parent::__construct($config, $container);

		/**
		 * Load the ANGIENullWriter class, used with the Akeeba Replace engine to suppress warnings about lack of
		 * backups.
		 */
		require_once __DIR__ . '/nullwriter.php';
	}

	/**
	 * Get a reference to the database driver object
	 *
	 * @return ADatabaseDriver
	 */
	public function &getDbo()
	{
		if (!is_object($this->db))
		{
			$options = $this->getDatabaseConnectionOptions();
			$name    = $options['driver'];

			unset($options['driver']);

			$this->db = ADatabaseFactory::getInstance()->getDriver($name, $options);
			$this->db->setUTF();
		}

		return $this->db;
	}

	/**
	 * Is this a multisite installation?
	 *
	 * @return  bool  True if this is a multisite installation
	 */
	public function isMultisite()
	{
		/** @var AngieModelWordpressConfiguration $config */
		$config = AModel::getAnInstance('Configuration', 'AngieModel', [], $this->container);

		return $config->get('multisite', false);
	}

	/**
	 * Returns all the database tables which are not part of the WordPress core
	 *
	 * @return array
	 */
	public function getNonCoreTables()
	{
		// Get a list of core tables
		$coreTables = $this->getCoreTables();

		// Now get a list of non-core tables
		$db        = $this->getDbo();
		$allTables = $db->getTableList();

		$result = [];

		foreach ($allTables as $table)
		{
			if (in_array($table, $coreTables))
			{
				continue;
			}

			$result[] = $table;
		}

		return $result;
	}

	/**
	 * Get the core WordPress tables. Content in these tables is always being replaced during restoration.
	 *
	 * @return  array
	 */
	public function getCoreTables()
	{
		// Core WordPress tables (single site)
		$coreTables = [
			'#__commentmeta', '#__comments', '#__links', '#__options', '#__postmeta', '#__posts',
			'#__term_relationships', '#__term_taxonomy', '#__wp_termmeta', '#__terms', '#__usermeta', '#__users',
		];

		$db = $this->getDbo();

		// If we have a multisite installation we need to add the per-blog tables as well
		if ($this->isMultisite())
		{
			$additionalTables = ['#__blogmeta', '#__blogs', '#__site', '#__sitemeta'];

			/** @var AngieModelWordpressConfiguration $config */
			$config     = AModel::getAnInstance('Configuration', 'AngieModel', [], $this->container);
			$mainBlogId = $config->get('blog_id_current_site', 1);

			$map     = $this->getMultisiteMap($db);
			$siteIds = array_keys($map);

			foreach ($siteIds as $id)
			{
				if ($id == $mainBlogId)
				{
					continue;
				}

				foreach ($coreTables as $table)
				{
					$additionalTables[] = '#__' . $id . '_' . substr($table, 3);
				}
			}

			$coreTables = array_merge($coreTables, $additionalTables);
		}

		// Replace the meta-prefix with the real prefix
		return array_map(function ($v) use ($db) {
			return $db->replacePrefix($v);
		}, $coreTables);
	}

	/**
	 * Data in these tables shouldn't be replaced by default, since they are known to create issues (very long fields)
	 *
	 * @return array
	 */
	public function getDeselectedTables()
	{
		$db = $this->getDbo();

		$blacklist = [
			'#__itsec_distributed_storage',     // iTheme Security Pro: site files fingerprints
			'#__itsec_logs',                    // iTheme Security Pro: security exceptions log
		];

		// Replace the meta-prefix with the real prefix
		return array_map(function ($v) use ($db) {
			return $db->replacePrefix($v);
		}, $blacklist);
	}

	/**
	 * Get the data replacement values
	 *
	 * @param   bool  $fromRequest  Should I override session data with those from the request?
	 * @param   bool  $force        True to forcibly load the default replacements.
	 *
	 * @return array
	 */
	public function getReplacements($fromRequest = false, $force = false)
	{
		$session      = $this->container->session;
		$replacements = $session->get('dataReplacements', []);

		if (empty($replacements))
		{
			$replacements = [];
		}

		if ($fromRequest)
		{
			$replacements = [];

			$keys   = trim($this->input->get('replaceFrom', '', 'string'));
			$values = trim($this->input->get('replaceTo', '', 'string'));

			if (!empty($keys))
			{
				$keys   = explode("\n", $keys);
				$values = explode("\n", $values);

				foreach ($keys as $k => $v)
				{
					if (!isset($values[$k]))
					{
						continue;
					}

					$replacements[$v] = $values[$k];
				}
			}
		}

		if (empty($replacements) || $force)
		{
			$replacements = $this->getDefaultReplacements();
		}

		/**
		 * I must not replace / with something else, e.g. /foobar. This would cause URLs such as
		 * http://www.example.com/something to be replaced with a monstrosity like
		 * http:/foobar/foobar/www.example.com/foobarsomething which breaks the site :s
		 *
		 * The same goes for the .htaccess file, where /foobar would be added in random places,
		 * breaking the site.
		 */
		if (isset($replacements['/']))
		{
			unset($replacements['/']);
		}

		$session->set('dataReplacements', $replacements);

		return $replacements;
	}

	/**
	 * Post-processing for the #__blogs table of multisite installations
	 */
	public function updateMultisiteTables()
	{
		// Get the new base domain and base path

		/** @var AngieModelWordpressConfiguration $config */
		$config                     = AModel::getAnInstance('Configuration', 'AngieModel', [], $this->container);
		$new_url                    = $config->get('homeurl');
		$newUri                     = new AUri($new_url);
		$newDomain                  = $newUri->getHost();
		$newPath                    = $newUri->getPath();
		$old_url                    = $config->get('oldurl');
		$oldUri                     = new AUri($old_url);
		$oldDomain                  = $oldUri->getHost();
		$oldPath                    = $oldUri->getPath();
		$useSubdomains              = $config->get('subdomain_install', 0);
		$changedDomain              = $newUri->getHost() != $oldDomain;
		$changedPath                = $oldPath != $newPath;
		$convertSubdomainsToSubdirs = $this->mustConvertSudomainsToSubdirs($config, $changedPath, $newDomain);

		$db = $this->getDbo();

		/**
		 * Update #__blogs
		 *
		 * This contains a map of blog IDs to their domain and path (stored separately).
		 */
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__blogs'));

		try
		{
			$blogs = $db->setQuery($query)->loadObjectList();
		}
		catch (Exception $e)
		{
			$blogs = [];
		}

		$defaultBlogId = 1;

		foreach ($blogs as $blog)
		{
			if ($blog->blog_id == $defaultBlogId)
			{
				// Default site: path must match the site's installation path (e.g. /foobar/)
				$blog->path   = '/' . trim($newPath, '/') . '/';
				$blog->domain = $newUri->getHost();
			}
			/**
			 * Converting blog1.example.com to www.example.net/myfolder/blog1 (multisite subdomain installation in the
			 * site's root TO multisite subfolder installation in a subdirectory)
			 */
			elseif ($convertSubdomainsToSubdirs)
			{
				// Extract the subdomain WITHOUT the trailing dot
				$subdomain = substr($blog->domain, 0, -strlen($oldDomain) - 1);

				// Step 1. domain: Convert old subdomain (blog1.example.com) to new full domain (www.example.net)
				$blog->domain = $newUri->getHost();

				// Step 2. path: Replace old path (/) with new path + slug (/mysite/blog1).
				$blogPath   = trim($newPath, '/') . '/' . trim($subdomain, '/') . '/';
				$blog->path = '/' . ltrim($blogPath, '/') . '/';

				if ($blog->path == '//')
				{
					$blog->path = '/';
				}
			}
			/**
			 * Converting blog1.example.com to blog1.example.net (keep multisite subdomain installation, change the
			 * domain name)
			 */
			elseif ($useSubdomains && $changedDomain)
			{
				// Change domain (extract subdomain a.k.a. alias, append $newDomain to it)
				$subdomain    = substr($blog->domain, 0, -strlen($oldDomain));
				$blog->domain = $subdomain . $newDomain;
			}
			/**
			 * Convert subdomain installations when EITHER the domain OR the path have changed. E.g.:
			 *  www.example.com/blog1   to  www.example.net/blog1
			 * OR
			 *  www.example.com/foo/blog1   to  www.example.com/bar/blog1
			 * OR
			 *  www.example.com/foo/blog1   to  www.example.net/bar/blog1
			 */
			elseif ($changedDomain || $changedPath)
			{
				if ($changedDomain)
				{
					// Update the domain
					$blog->domain = $newUri->getHost();
				}

				if ($changedPath)
				{
					// Change $blog->path (remove old path, keep alias, prefix it with new path)
					$path       = (strpos($blog->path, $oldPath) === 0) ? substr($blog->path, strlen($oldPath)) : $blog->path;
					$blog->path = '/' . trim($newPath . '/' . ltrim($path, '/'), '/');
				}
			}

			// For every record, make sure the path column ends in forward slash (required by WP)
			$blog->path = rtrim($blog->path, '/') . '/';

			// Save the changed record
			try
			{
				$db->updateObject('#__blogs', $blog, ['blog_id', 'site_id']);
			}
			catch (Exception $e)
			{
				// If we failed to save the record just skip over to the next one.
			}
		}

		/**
		 * Update #__site
		 *
		 * This contains the main site address in its one and only record. The same address which is defined as a
		 * constant in wp-config.php and stored in the #__options and #__sitemeta table.
		 *
		 * I am not making up preposterous claims. This is what WordPress itself describes in its official Codex, see
		 * https://codex.wordpress.org/Database_Description#Multisite_Table_Overview Yeah, I know it's a pointless
		 * table with pointless data. Yet, if it's not replaced properly the whole thing goes bang like a firecracker!
		 */
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__site'))
			->where($db->qn('id') . ' = ' . $db->q('1'));

		try
		{
			$siteObject = $db->setQuery($query)->loadObject();
		}
		catch (Exception $e)
		{
			return;
		}

		// Default site: path must match the site's installation path (e.g. /foobar/)
		$siteObject->path   = '/' . trim($newPath, '/') . '/';
		$siteObject->domain = $newUri->getHost();

		// Save the changed record
		try
		{
			$db->updateObject('#__site', $siteObject, ['id']);
		}
		catch (Exception $e)
		{
			// If we failed to save the record no problem, everything will crash and burn.
		}
	}

	/**
	 * Initialize the replacement engine, tick it for the first time and return the result
	 *
	 * @return  PartStatus
	 */
	public function init()
	{
		$replaceGUID = $this->input->get('replaceguid', 0);

		/**
		 * Get the excluded tables.
		 *
		 * All core WordPress tables are always included. We only let the user which of the non-core tables should also
		 * be included in replacements. Therefore any non-core table NOT explicitly included by the user has to be
		 * excluded from the replacement.
		 */
		$extraTables    = $this->input->get('extraTables', [], 'array');
		$nonCore        = $this->getNonCoreTables();
		$excludedTables = array_diff($nonCore, $extraTables);

		// If we are under CLI we need to replace everything, everywhere -- as long as we share the same prefix
		if (!array_key_exists('REQUEST_METHOD', $_SERVER))
		{
			$dbAngie        = $this->getDbo();
			$prefix         = $dbAngie->getPrefix();
			$excludedTables = array_filter($dbAngie->getTableList(), function ($tableName) use ($prefix) {
				return strpos($tableName, $prefix) !== 0;
			});
		}

		// I must always exclude the #__blogs table because this is handled in the updateMultisiteTables() method
		$excludedTables[] = '#__blogs';
		// I must always exclude the #__site table because this is handled in the updateMultisiteTables() method
		$excludedTables[] = '#__site';

		// Push some useful information into the session
		$session = $this->container->session;
		$min     = $this->input->getInt('min_exec', 0);
		$session->set('replacedata.min_exec', $min);

		/**
		 * Make a Database object and pass the existing connection.
		 *
		 * We set a blank username and password to prevent the Akeeba Replace DB driver from reconnecting without
		 * using our custom connection.
		 */
		$dbOptions               = $this->getDatabaseConnectionOptions();
		$dbOptions['connection'] = $this->getDbo()->getConnection();
		$dbOptions['user']       = '';
		$dbOptions['password']   = '';

		$db = Driver::getInstance($dbOptions);

		/**
		 * Make a Configuration object
		 *
		 * Output, backup and log file names are ignored since we use a null writer for them further down this method.
		 * However, by using non-empty filenames we suppress warnings about not using these features from being
		 * displayed to the users since *that* would confuse them.
		 */
		$isDebug = defined('AKEEBA_DEBUG') && AKEEBA_DEBUG;

		// Excluded fields
		$excludedFields = [
			// Exclude meta keys from replacements (these never contain any replaceable values)
			$this->getDbo()->getPrefix() . 'postmeta' => ['meta_key'],
		];

		// Unless we're specifically told otherwise, exclude all post GUIDs from replacements
		if (!$replaceGUID)
		{
			$excludedFields[$this->getDbo()->getPrefix() . 'posts'] = ['guid'];
		}

		if ($this->isMultisite())
		{
			$dbAngie = $this->getDbo();
			$map     = $this->getMultisiteMap($dbAngie);
			$siteIds = array_keys($map);

			foreach ($siteIds as $siteId)
			{
				if ($siteId == 1)
				{
					continue;
				}

				$excludedFields[$this->getDbo()->getPrefix() . $siteId . '_posts'] = ['guid'];
			}
		}

		// Set up the replacement engine
		$configParams = [
			'outputSQLFile'      => sprintf('replacements-%s.sql', $dbOptions['database']),
			'backupSQLFile'      => sprintf('backup-%s.sql', $dbOptions['database']),
			'logFile'            => sprintf('replacements-%s.log', $dbOptions['database']),
			'liveMode'           => true,
			'allTables'          => true,
			'maxBatchSize'       => $this->input->getInt('batchSize', 100),
			'excludeTables'      => $excludedTables,
			'excludeRows'        => $excludedFields,
			'regularExpressions' => false,
			'replacements'       => $this->getReplacements(true, false),
			'databaseCollation'  => '',
			'tableCollation'     => '',
			'description'        => 'ANGIE replacing data in your WordPress site',
		];
		$config       = new Configuration($configParams);

		if (!defined('AKEEBA_REPLACE_MAXIMUM_COLUMN_SIZE'))
		{
			define('AKEEBA_REPLACE_MAXIMUM_COLUMN_SIZE', $this->input->getInt('column_size', 1048576));
		}

		// Make a Timer object
		$max   = $this->input->getInt('max_exec', 3);
		$bias  = $this->input->getInt('runtime_bias', 75);
		$timer = new \Akeeba\Replace\Timer\Timer($max, $bias);

		// Create dummy writer objects
		$logger = new NullLogger();
		$output = new ANGIENullWriter('/tmp/fake_out.sql');
		$backup = new ANGIENullWriter('/tmp/fake_bak.sql');

		if ($isDebug)
		{
			$logger = Akeeba\Replace\Logger\FileLogger::fromFile(APATH_INSTALLATION . '/tmp/' . $configParams['logFile']);
			$output = new \Akeeba\Replace\Writer\FileWriter(APATH_INSTALLATION . '/tmp/' . $configParams['outputSQLFile'], true);
			$backup = new \Akeeba\Replace\Writer\FileWriter(APATH_INSTALLATION . '/tmp/' . $configParams['backupSQLFile'], true);
		}

		// Create a memory information object
		$memoryInfo = new MemoryInfo();

		// Create the new engine object and serialize it
		$engine = new Database($timer, $db, $logger, $output, $backup, $config, $memoryInfo);
		$session->set('replacedata.engine', serialize($engine));

		// Set up the logger's minimum severity
		$logger->setMinimumSeverity(\Akeeba\Replace\Logger\LoggerInterface::SEVERITY_DEBUG);
		$logger->debug('========== Starting first step ==========');

		// Now run the engine for the first time
		$ret = $this->step();
		$logger->debug('========== Breaking first step ==========');

		return $ret;

	}

	/**
	 * Step the Akeeba Replace engine for the allowed period of time (or until we're done) and return the result to the
	 * caller.
	 *
	 * @return  PartStatus
	 */
	public function step()
	{
		$session          = $this->container->session;
		$serializedEngine = $session->get('replacedata.engine', null);

		if (empty($serializedEngine))
		{
			throw new RuntimeException("Broken session: cannot unserialize the data replacement engine; the serialized data is missing.");
		}

		/** @var Database $engine */
		$engine = @unserialize($serializedEngine);

		if (!is_object($engine) || !($engine instanceof Database))
		{
			throw new RuntimeException("Broken session: cannot unserialize the data replacement engine; the serialized data is corrupt.");
		}

		// Upon unserialization the configured connection object is gone. So we need to reapply it here.
		$engine->getDbo()->setConnection($this->getDbo()->getConnection());

		// Set up the logger's minimum severity
		$logger = $engine->getLogger();
		$logger->setMinimumSeverity(\Akeeba\Replace\Logger\LoggerInterface::SEVERITY_DEBUG);
		$logger->debug('========== Starting new step ==========');

		// Prime the status with an error -- this is used if we cannot load a cached engine
		$status = new PartStatus([
			'Error' => 'Trying to step the replacement engine after it has finished processing replacements.',
		]);

		$timer    = $engine->getTimer();
		$warnings = [];
		$error    = null;

		$timer->resetTime();

		while ($timer->getTimeLeft() > 0)
		{
			// Run a single step
			$status = $engine->tick();

			// Merge any warnings
			$newWarnings = $status->getWarnings();
			$warnings    = array_merge($warnings, $newWarnings);

			// Are we done already?
			if ($status->isDone())
			{
				break;
			}

			// Check for an error
			$error = $status->getError();

			if (!is_object($error) || !($error instanceof ErrorException))
			{
				$error = null;

				continue;
			}

			// We hit an error
			break;
		}

		$logger->debug('========== Breaking step ==========');

		// Construct a new status array with the merged warnings and the carried over error (if any)
		$configArray             = $status->toArray();
		$configArray['Warnings'] = $warnings;
		$configArray['Error']    = $error;
		$status                  = new PartStatus($configArray);

		if ($status->isDone())
		{
			$logger->debug('All done.');
		}
		elseif (!is_null($error))
		{
			$logger->debug('Replacement engine died with an error.');
		}

		if ($status->isDone() || !is_null($error))
		{
			// If we are done (or died with an error) we remove the cached engine from the session (we do not need it)
			$session->remove('replacedata.engine');
		}
		else
		{
			// Cache the new engine status
			$session->set('replacedata.engine', serialize($engine));
		}

		// Enforce minimum execution time but only if we haven't finished already (done or error)
		if (!is_null($engine))
		{
			$minExec     = $session->get('replacedata.min_exec', 0);
			$runningTime = $timer->getRunningTime();

			if ($runningTime < $minExec)
			{
				$sleepForSeconds = $minExec - $runningTime;

				$logger->debug(sprintf('Enforcing minimum execution time of %0.2f seconds (sleeping for %0.2f seconds)', $minExec, $sleepForSeconds));

				usleep($sleepForSeconds * 1000000);
			}
		}

		return $status;
	}

	/**
	 * Update the GUIDs of the uploads
	 *
	 * This is necessary because WordPress is using these GUIDs to insert the uploads into posts... even though the
	 * GUIDs are meant to be used as unique identifiers, not actual URLs. I guess it's too much asking them to make up
	 * their minds.
	 *
	 * @return  void
	 */
	public function updateAttachmentGUIDs()
	{
		/** @var AngieModelWordpressConfiguration $config */
		$config = AModel::getAnInstance('Configuration', 'AngieModel', [], $this->container);

		$old_url = $config->get('oldurl');
		$oldUri  = new AUri($old_url);
		$old_url = rtrim($oldUri->toString(), '/') . '/';

		$new_url = $config->get('homeurl');
		$newUri  = new AUri($new_url);
		$new_url = rtrim($newUri->toString(), '/') . '/';

		try
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true)
				->update($db->qn('#__posts'))
				->set(
					$db->qn('guid') . ' = REPLACE(' .
					$db->qn('guid') . ',' .
					$db->qn($old_url) . ',' .
					$db->qn($new_url) .
					')'
				)->where($db->qn('post_type') . ' = ' . $db->q('attachment'));
			$db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			// No problem if this fails.
		}
	}

	/**
	 * Updates known files that are storing absolute paths inside them
	 */
	public function updateFiles()
	{
		$files = [
			// Do not replace anything in .htaccess; we'll do that in the finalization (next step of the installer)
			/**
			 * APATH_SITE.'/.htaccess',
			 * APATH_SITE.'/htaccess.bak',
			 * /**/
			// I'll try to apply the changes to those files and their "backup" counterpart
			APATH_SITE . '/.user.ini.bak',
			APATH_SITE . '/.user.ini',
			APATH_SITE . '/php.ini',
			APATH_SITE . '/php.ini.bak',
			// Wordfence is storing the absolute path inside their file. We need to replace this or the site will crash.
			APATH_SITE . '/wordfence-waf.php',
		];

		foreach ($files as $file)
		{
			if (!file_exists($file))
			{
				continue;
			}

			$contents = file_get_contents($file);

			foreach ($this->replacements as $from => $to)
			{
				$contents = str_replace($from, $to, $contents);
			}

			file_put_contents($file, $contents);
		}
	}

	/**
	 * Update the wp-config.php file. Required for multisite installations.
	 *
	 * @return  bool
	 */
	public function updateWPConfigFile()
	{
		/** @var AngieModelWordpressConfiguration $config */
		$config = AModel::getAnInstance('Configuration', 'AngieModel', [], $this->container);

		// Update the base directory, if present
		$base = $config->get('base', null);

		if (!is_null($base))
		{
			$base = '/' . trim($config->getNewBasePath(), '/');
			$config->set('base', $base);
		}

		// If I have to convert subdomains to subdirs then I need to update SUBDOMAIN_INSTALL as well
		$old_url = $config->get('oldurl');
		$new_url = $config->get('homeurl');

		$oldUri = new AUri($old_url);
		$newUri = new AUri($new_url);

		$newDomain = $newUri->getHost();

		$newPath = $newUri->getPath();
		$newPath = empty($newPath) ? '/' : $newPath;
		$oldPath = $config->get('path_current_site', $oldUri->getPath());

		$replacePaths = $oldPath != $newPath;

		$mustConvertSubdomains = $this->mustConvertSudomainsToSubdirs($config, $replacePaths, $newDomain);

		if ($mustConvertSubdomains)
		{
			$config->set('subdomain_install', 0);
		}

		// Get the wp-config.php file and try to save it
		if (!$config->writeConfig(APATH_SITE . '/wp-config.php'))
		{
			return false;
		}

		return true;
	}

	public function getDefaultURLReplacements()
	{
		$replacements = [];

		/** @var AngieModelWordpressConfiguration $config */
		$config = AModel::getAnInstance('Configuration', 'AngieModel', [], $this->container);

		// Main site's URL
		$newReplacements = $this->getDefaultReplacementsForMainSite($config, false);
		$replacements    = array_merge($replacements, $newReplacements);

		// Multisite's URLs
		$newReplacements = $this->getDefaultReplacementsForMultisite($config);
		$replacements    = array_merge($replacements, $newReplacements);

		if (empty($replacements))
		{
			return [];
		}

		// Remove replacements where from is just a slash or empty
		$temp = [];

		foreach ($replacements as $from => $to)
		{
			$trimFrom = trim($from, '/\\');

			if (empty($trimFrom))
			{
				continue;
			}

			$temp[$from] = $to;
		}

		$replacements = $temp;

		if (empty($replacements))
		{
			return [];
		}

		// Find http[s]:// from/to and create replacements with just :// as the protocol
		$temp = [];

		foreach ($replacements as $from => $to)
		{
			$replaceFrom = ['http://', 'https://'];
			$replaceTo   = ['://', '://'];
			$from        = str_replace($replaceFrom, $replaceTo, $from);
			$to          = str_replace($replaceFrom, $replaceTo, $to);
			$temp[$from] = $to;
		}

		$replacements = $temp;

		if (empty($replacements))
		{
			return [];
		}

		// Go through all replacements and create a RegEx variation
		$temp = [];

		foreach ($replacements as $from => $to)
		{
			$from = $this->escape_string_for_regex($from);
			$to   = $this->escape_string_for_regex($to);

			if (array_key_exists($from, $replacements))
			{
				continue;
			}

			$temp[$from] = $to;
		}

		$replacements = array_merge_recursive($replacements, $temp);

		// Return the resulting replacements table
		return $replacements;
	}

	/**
	 * Updates entries in the #__options table
	 */
	public function updateSiteOptions()
	{
		// ========== Get the WordPress options to update ==========
		/** @var AngieModelWordpressConfiguration $configModel */
		$configModel = AModel::getAnInstance('Configuration', 'AngieModel', [], $this->container);

		$siteOptions = [
			'siteurl' => $configModel->get('siteurl', ''),
			'home'    => $configModel->get('homeurl', ''),
		];

		$siteOptions['home'] = empty($siteOptions['home']) ? $siteOptions['siteurl'] : $siteOptions['home'];

		// ========== Connect to the main site's database and update entries ==========
		/** @var AngieModelDatabase $dbModel */
		$dbModel        = AModel::getAnInstance('Database', 'AngieModel', [], $this->container);
		$dbKeys         = $dbModel->getDatabaseNames();
		$firstDbKey     = array_shift($dbKeys);
		$connectionVars = $dbModel->getDatabaseInfo($firstDbKey);

		try
		{
			$name    = $connectionVars->dbtype;
			$options = [
				'database' => $connectionVars->dbname,
				'select'   => 1,
				'host'     => $connectionVars->dbhost,
				'user'     => $connectionVars->dbuser,
				'password' => $connectionVars->dbpass,
				'prefix'   => $connectionVars->prefix,
			];

			$db = ADatabaseFactory::getInstance()->getDriver($name, $options);
		}
		catch (Exception $exc)
		{
			// Can't connect to the DB. Your site will be borked but at least I tried :(
			return;
		}

		foreach ($siteOptions as $key => $value)
		{
			try
			{
				$query = $db->getQuery(true)
					->update('#__options')
					->set($db->qn('option_value') . ' = ' . $db->q($value))
					->where($db->qn('option_name') . ' = ' . $db->q($key));
				$db->setQuery($query)->execute();
			}
			catch (Exception $e)
			{
				// Swallow it
			}
		}

		try
		{
			$db->disconnect();
		}
		catch (Exception $exc)
		{
			// No problem, we are done anyway
		}
	}

	/**
	 * Am I restoring to the same URL I backed up from?
	 *
	 * @return  bool
	 */
	public function isSameSiteURL()
	{
		/** @var AngieModelWordpressConfiguration $config */
		$config  = AModel::getAnInstance('Configuration', 'AngieModel', array(), $this->container);

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

		/**
		 * The oldurl (URL I backed up from) and homeurl (URL I am restoring to, as possibly modified by the user),
		 * are both stored in the session.
		 */
		return $config->get('oldurl') == $config->get('homeurl');
	}

	/**
	 * Am I restoring to the same filesystem root I backed up from?
	 *
	 * @return  bool
	 */
	public function isSameFilesystemRoot()
	{
		// Let's get the reference of the previous absolute path
		/** @var AngieModelBaseMain $mainModel */
		$mainModel  = AModel::getAnInstance('Main', 'AngieModel', [], $this->container);
		$extra_info = $mainModel->getExtraInfo();

		if (!isset($extra_info['root']) || empty($extra_info['root']))
		{
			return false;
		}

		$old_path = rtrim($extra_info['root']['current'], '/');
		$new_path = rtrim(APATH_SITE, '/');

		return $old_path == $new_path;
	}

	/**
	 * Escapes a string so that it's a neutral string inside a regular expression.
	 *
	 * @param   string  $str  The string to escape
	 *
	 * @return  string  The escaped string
	 */
	protected function escape_string_for_regex($str)
	{
		//All regex special chars (according to arkani at iol dot pt below):
		// \ ^ . $ | ( ) [ ]
		// * + ? { } , -

		$patterns = [
			'/\//', '/\^/', '/\./', '/\$/', '/\|/',
			'/\(/', '/\)/', '/\[/', '/\]/', '/\*/', '/\+/',
			'/\?/', '/\{/', '/\}/', '/\,/', '/\-/',
		];

		$replace = [
			'\/', '\^', '\.', '\$', '\|', '\(', '\)',
			'\[', '\]', '\*', '\+', '\?', '\{', '\}', '\,', '\-',
		];

		return preg_replace($patterns, $replace, $str);
	}

	/**
	 * Get the database driver connection options
	 *
	 * @return  array
	 */
	private function getDatabaseConnectionOptions()
	{
		/** @var AngieModelDatabase $model */
		$model      = AModel::getAnInstance('Database', 'AngieModel', [], $this->container);
		$keys       = $model->getDatabaseNames();
		$firstDbKey = array_shift($keys);

		$connectionVars = $model->getDatabaseInfo($firstDbKey);

		$options = [
			'driver'   => $connectionVars->dbtype,
			'database' => $connectionVars->dbname,
			'select'   => 1,
			'host'     => $connectionVars->dbhost,
			'user'     => $connectionVars->dbuser,
			'password' => $connectionVars->dbpass,
			'prefix'   => $connectionVars->prefix,
		];

		return $options;
	}

	/**
	 * Get the map of IDs to blog URLs
	 *
	 * @param   ADatabaseDriver  $db  The database connection
	 *
	 * @return  array  The map, or an empty array if this is not a multisite installation
	 */
	private function getMultisiteMap($db)
	{
		static $map = null;

		if (is_null($map))
		{
			/** @var AngieModelWordpressConfiguration $config */
			$config = AModel::getAnInstance('Configuration', 'AngieModel', [], $this->container);

			// Which site ID should I use?
			$site_id = $config->get('site_id_current_site', 1);

			// Get all of the blogs of this site
			$query = $db->getQuery(true)
				->select([
					$db->qn('blog_id'),
					$db->qn('domain'),
					$db->qn('path'),
				])
				->from($db->qn('#__blogs'))
				->where($db->qn('site_id') . ' = ' . $db->q($site_id));

			try
			{
				$map = $db->setQuery($query)->loadAssocList('blog_id');
			}
			catch (Exception $e)
			{
				$map = [];
			}
		}

		return $map;
	}

	/**
	 * Returns the default replacement values
	 *
	 * @return array
	 */
	private function getDefaultReplacements()
	{
		$replacements = [];

		/** @var AngieModelWordpressConfiguration $config */
		$config = AModel::getAnInstance('Configuration', 'AngieModel', [], $this->container);

		// Main site's URL
		$newReplacements = $this->getDefaultReplacementsForMainSite($config);
		$replacements    = array_merge($replacements, $newReplacements);

		// Multisite's URLs
		$newReplacements = $this->getDefaultReplacementsForMultisite($config);
		$replacements    = array_merge($replacements, $newReplacements);

		// Database prefix
		$newReplacements = $this->getDefaultReplacementsForDbPrefix($config);
		$replacements    = array_merge($replacements, $newReplacements);

		// Take into account JSON-encoded data
		foreach ($replacements as $from => $to)
		{
			// If we don't do that we end with the string literal "null" which is incorrect
			if (is_null($to))
			{
				$to = '';
			}

			$jsonFrom = json_encode($from);
			$jsonTo   = json_encode($to);
			$jsonFrom = trim($jsonFrom, '"');
			$jsonTo   = trim($jsonTo, '"');

			if ($jsonFrom != $from)
			{
				$replacements[$jsonFrom] = $jsonTo;
			}
		}

		// All done
		return $replacements;
	}

	/**
	 * Internal method to get the default replacements for the main site URL
	 *
	 * @param   AngieModelWordpressConfiguration  $config         The configuration model
	 * @param   bool                              $absolutePaths  Include absolute filesystem paths
	 *
	 * @return  array  Any replacements to add
	 */
	private function getDefaultReplacementsForMainSite($config, $absolutePaths = true)
	{
		$replacements = [];

		// Let's get the reference of the previous absolute path
		/** @var AngieModelBaseMain $mainModel */
		$mainModel  = AModel::getAnInstance('Main', 'AngieModel', [], $this->container);
		$extra_info = $mainModel->getExtraInfo();

		if (isset($extra_info['root']) && $extra_info['root'] && $absolutePaths)
		{
			$old_path = rtrim($extra_info['root']['current'], '/');
			$new_path = rtrim(APATH_SITE, '/');

			// Replace only if they are different
			if ($old_path != $new_path)
			{
				$replacements[$old_path] = $new_path;
			}
		}

		// These values are stored inside the session, after the setup step
		$old_url = $config->get('oldurl');
		$new_url = $config->get('homeurl');

		if ($old_url == $new_url)
		{
			return $replacements;
		}

		$oldUri       = new AUri($old_url);
		$newUri       = new AUri($new_url);
		$oldDirectory = $oldUri->getPath();
		$newDirectory = $newUri->getPath();

		// Replace domain site only if the protocol, the port or the domain are different
		if (
			($oldUri->getHost() != $newUri->getHost()) ||
			($oldUri->getPort() != $newUri->getPort()) ||
			($oldUri->getScheme() != $newUri->getScheme())
		)
		{
			// Normally we need to replace both the domain and path, e.g. https://www.example.com => http://localhost/wp

			$old = $oldUri->toString(['scheme', 'host', 'port', 'path']);
			$new = $newUri->toString(['scheme', 'host', 'port', 'path']);

			// However, if the path is the same then we must only replace the domain.
			if ($oldDirectory == $newDirectory)
			{
				$old = $oldUri->toString(['scheme', 'host', 'port']);
				$new = $newUri->toString(['scheme', 'host', 'port']);
			}

			$replacements[$old] = $new;

		}

		// If the relative path to the site is different, replace it too, but ONLY if the old directory isn't empty.
		if (!empty($oldDirectory) && ($oldDirectory != $newDirectory))
		{
			$replacements[rtrim($oldDirectory, '/') . '/'] = rtrim($newDirectory, '/') . '/';
		}

		/**
		 * Special case: The Inception Restoration
		 *
		 * When you are restoring the site into a subdirectory of itself and two (old and new) subdirectories begin with
		 * the same substring.
		 *
		 * This causes duplication of the new path, after the common prefix.
		 *
		 * Here are some examples.
		 *
		 * Take a backup from http://www.example.com/foobar and restore it to http://www.example.com/foobar/foobar
		 * This causes two replacements to be made
		 * 1. http://www.example.com/foobar => http://www.example.com/foobar/foobar
		 * 2. /foobar => /foobar/foobar
		 * Since they run in sequence, the URL http://www.example.com/foobar becomes after both replacement are run:
		 * http://www.example.com/foobar/foobar/foobar
		 * Solution: replace /foobar/foobar/foobar with /foobar/foobar
		 *
		 * 1. http://www.example.com/foo/bar ==> http://www.example.com/foo/bar/foo
		 * 2. foo/bar => foo/bar/foo
		 * http://www.example.com/foo/bar becomes http://www.example.com/foo/bar/foo/foo
		 * Solution: replace /foo/bar/foo/foo with /foo/bar/foo
		 *
		 * 1. http://xxx/foo => http://xxx/foo/bar
		 * 2. foo => foo/bar
		 * http://www.example.com/foo becomes http://www.example.com/foo/bar/bar
		 * Solution: replace foo/bar/bar with foo/bar
		 */
		$differentDirectory = trim($oldDirectory, '/') != trim($newDirectory, '/');
		$trimmedOldDir      = trim($oldDirectory, '/');
		$trimmedNewDir      = trim($newDirectory, '/');
		$samePrefix         = !empty($trimmedOldDir) && !empty($trimmedNewDir) && strpos($trimmedNewDir, $trimmedOldDir) === 0;

		if ($differentDirectory && $samePrefix)
		{
			$suffix                          = substr($trimmedNewDir, strlen($trimmedOldDir));
			$wrongReplacement                = '/' . $trimmedNewDir . '/' . trim($suffix, '/') . '/';
			$correctReplacement              = '/' . $trimmedNewDir . '/';
			$replacements[$wrongReplacement] = $correctReplacement;
		}

		return $replacements;
	}

	/**
	 * Internal method to get the default replacements for multisite's URLs
	 *
	 * @param   AngieModelWordpressConfiguration  $config  The configuration model
	 *
	 * @return  array  Any replacements to add
	 */
	private function getDefaultReplacementsForMultisite($config)
	{
		$replacements = [];
		$db           = $this->getDbo();

		if (!$this->isMultisite())
		{
			return $replacements;
		}

		// These values are stored inside the session, after the setup step
		$old_url = $config->get('oldurl');
		$new_url = $config->get('homeurl');

		// If the URL didn't change do nothing
		if ($old_url == $new_url)
		{
			return $replacements;
		}

		// Get the old and new base domain and base path
		$oldUri = new AUri($old_url);
		$newUri = new AUri($new_url);

		$newDomain = $newUri->getHost();
		$oldDomain = $oldUri->getHost();

		$newPath = $newUri->getPath();
		$newPath = empty($newPath) ? '/' : $newPath;
		$oldPath = $config->get('path_current_site', $oldUri->getPath());

		$replaceDomains = $newDomain != $oldDomain;
		$replacePaths   = $oldPath != $newPath;

		// Get the multisites information
		$multiSites = $this->getMultisiteMap($db);

		// Get other information
		$mainBlogId    = $config->get('blog_id_current_site', 1);
		$useSubdomains = $config->get('subdomain_install', 0);

		/**
		 * If we use subdomains and we are restoring to a different path OR we are restoring to localhost THEN
		 * we must convert subdomains to subdirectories.
		 */
		$convertSubdomainsToSubdirs = $this->mustConvertSudomainsToSubdirs($config, $replacePaths, $newDomain);

		// Do I have to replace the domain?
		/** @noinspection PhpStatementHasEmptyBodyInspection */
		if ($oldDomain != $newDomain)
		{
			/**
			 * No, we do not have to do that.
			 *
			 * EXAMPLE: From http://test.web to http://mytest.web
			 *
			 * The main site replacements has already mapped http://test.web to http://mytest.web. If we add another map
			 * test.web to mytest.web consider the following link:
			 * http://test.web/foo/bar
			 * After first replacement (from main site): http://mytest.web/foo/bar (CORRECT)
			 * After second replacement (below): http://mymytest.web/foo/bar (INVALID!)
			 *
			 * This was originally added as a way to convert the entries of the #__blogs tables. However, since we now
			 * handle this special table in the separate method self::updateMultisiteTables() we don't need this
			 * replacement and the problems it entails. Hence, it's commented out.
			 *
			 * Please leave it commented out with this explanatory comment above it to prevent any future "clever" ideas
			 * which could possibly reintroduce it and break things again.
			 */
			// $replacements[$oldDomain] = $newUri->getHost();
		}

		// Maybe I have to do... nothing?
		if ($useSubdomains && !$replaceDomains && !$replacePaths)
		{
			return $replacements;
		}

		// Subdirectories installation and the path hasn't changed
		if (!$useSubdomains && !$replacePaths)
		{
			return $replacements;
		}

		// Loop for each multisite
		foreach ($multiSites as $blogId => $info)
		{
			// Skip the first site, it is the same as the main site
			if ($blogId == $mainBlogId)
			{
				continue;
			}

			// Multisites using subdomains?
			if ($useSubdomains && !$convertSubdomainsToSubdirs)
			{
				$blogDomain = $info['domain'];

				// Extract the subdomain
				$subdomain = substr($blogDomain, 0, -strlen($oldDomain));

				// Add a replacement for this domain
				$replacements[$blogDomain] = $subdomain . $newDomain;

				continue;
			}

			// Convert subdomain install to subdirectory install
			if ($convertSubdomainsToSubdirs)
			{
				$blogDomain = $info['domain'];

				/**
				 * No, you don't need this. You need to convert the old subdomain to the new domain PLUS path **AND**
				 * different RewriteRules in .htaccess to magically transform invalid paths to valid paths. Bleh.
				 */
				// Convert old subdomain (blog1.example.com) to new full domain (example.net)
				// $replacements[$blogDomain] = $newUri->getHost();

				// Convert links in post GUID, e.g. //blog1.example.com/ TO //example.net/mydir/blog1/
				$subdomain           = substr($blogDomain, 0, -strlen($oldDomain) - 1);
				$from                = '//' . $blogDomain;
				$to                  = '//' . $newUri->getHost() . $newUri->getPath() . '/' . $subdomain;
				$to                  = rtrim($to, '/');
				$replacements[$from] = $to;

				continue;
			}

			// Multisites using subdirectories. Let's check if I have to extract the old path.
			$path = (strpos($info['path'], $oldPath) === 0) ? substr($info['path'], strlen($oldPath)) : $info['path'];

			// Construct the new path and add it to the list of replacements
			$path      = trim($path, '/');
			$newMSPath = $newPath . '/' . $path;
			$newMSPath = trim($newMSPath, '/');

			/**
			 * Moving from www.example.com to localhost/foobar
			 *
			 * This would cause two replacements:
			 * http://www.example.com   to http://localhost/foobar
			 * /blog1/                  to /foobar/blog1/
			 *
			 * This means that http://www.example.com/blog1/baz.html becomes http://localhost/foobar/foobar/blog1/baz.html
			 * which is wrong. The only solution is to add another replacement:
			 * /foobar/foobar/blog1/ to /foobar/blog1/
			 */
			$wrongPath                = '/' . trim($newPath . '/' . $newMSPath, '/') . '/';
			$correctPath              = '/' . $newMSPath . '/';
			$replacements[$wrongPath] = $correctPath;

			/**
			 * Now add the replacement http://www.example.com to http://localhost/foobar (BECAUSE THE ORDER WILL REVERSE
			 * BELOW!). However, only do that when the domain changes. If it's the same domain then the rules above and
			 * below this chunk will take care of it. HOWEVER! If the domain is the same BUT the old directory is the
			 * root and the new one is not I still have to run this replacement.
			 */
			$trimmedOldPath             = trim($oldPath, '/');
			$trimmedNewPath             = trim($newPath, '/');
			$migrateFromRootToSubfolder = empty($trimmedOldPath) && !empty($trimmedNewPath);

			if ($replaceDomains || $migrateFromRootToSubfolder)
			{
				$oldFullMultisiteURL                = rtrim($old_url, '/') . '/' . trim($info['path'], '/');
				$newFullMultisiteURL                = rtrim($new_url, '/') . '/' . trim($info['path'], '/');
				$replacements[$oldFullMultisiteURL] = $newFullMultisiteURL;
			}

			// Now add the replacement /blog1/ to /foobar/blog1/ (BECAUSE THE ORDER WILL REVERSE BELOW!)
			$replacements[rtrim($info['path'], '/') . '/'] = '/' . $newMSPath . '/';

		}

		// Important! We have to change subdomains BEFORE the main domain. And for this, we need to reverse the
		// replacements table. If you're wondering why: old domain example.com, new domain www.example.net. This
		// makes blog1.example.com => blog1.www.example.net instead of blog1.example.net (note the extra www). Oops!
		$replacements = array_reverse($replacements);

		return $replacements;
	}

	/**
	 * Internal method to get the default replacements for the database prefix
	 *
	 * @param   AngieModelWordpressConfiguration  $config  The configuration model
	 *
	 * @return  array  Any replacements to add
	 */
	private function getDefaultReplacementsForDbPrefix($config)
	{
		$replacements = [];

		// Replace the table prefix if it's different
		$db        = $this->getDbo();
		$oldPrefix = $config->get('olddbprefix');
		$newPrefix = $db->getPrefix();

		if ($oldPrefix != $newPrefix)
		{
			$replacements[$oldPrefix] = $newPrefix;

			return $replacements;
		}

		return $replacements;
	}

	/**
	 * Do I have to convert the subdomain installation to a subdirectory installation?
	 *
	 * @param   AngieModelWordpressConfiguration  $config
	 * @param                                     $replacePaths
	 * @param                                     $newDomain
	 *
	 * @return  bool
	 */
	private function mustConvertSudomainsToSubdirs(AngieModelWordpressConfiguration $config, $replacePaths, $newDomain)
	{
		$useSubdomains = $config->get('subdomain_install', 0);

		// If we use subdomains and we are restoring to a different path we MUST convert subdomains to subdirectories
		$convertSubdomainsToSubdirs = $replacePaths && $useSubdomains;

		if (!$convertSubdomainsToSubdirs && $useSubdomains && ($newDomain == 'localhost'))
		{
			/**
			 * Special case: localhost
			 *
			 * Localhost DOES NOT support subdomains. Therefore the subdomain multisite installation MUST be converted
			 * to a subdirectory installation.
			 *
			 * Why is this special case needed? The previous line will only be triggered if we are restoring to a
			 * different path. However, when you are restoring to localhost you ARE restoring to the root of the site,
			 * i.e. the same path as a live multisite subfolder installation of WordPress. This would mean that ANGIE
			 * would try to restore as a subdomain installation which would fail on localhost.
			 */
			$convertSubdomainsToSubdirs = true;
		}

		return $convertSubdomainsToSubdirs;
	}
}