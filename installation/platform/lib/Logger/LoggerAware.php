<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Logger;

/**
 * A Trait to implement the LoggerAwareInterface
 *
 * @package Akeeba\Replace\Logger
 */
trait LoggerAware
{
	/**
	 * The logger object used to log things in this class
	 *
	 * @var  LoggerInterface
	 */
	private $logger = null;

	/**
	 * Assigns a Logger to the object.
	 *
	 * This should only be used internally by the constructor. The constructor itself should use explicit dependency
	 * injection.
	 *
	 * @param   LoggerInterface  $logger  The logger object to assign
	 *
	 * @return  void
	 */
	protected function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * Returns a reference to the logger object. This should only be used internally.
	 *
	 * @return  LoggerInterface
	 */
	public function getLogger()
	{
		return $this->logger;
	}
}