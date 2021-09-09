<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Engine;

use InvalidArgumentException;

/**
 * A trait to implement the DomainAwareInterface
 *
 * @package Akeeba\Replace\Engine
 */
trait DomainAware
{
	/**
	 * The current engine domain
	 *
	 * @var string
	 */
	private $domain = '';

	/**
	 * Get the name of the engine domain this part is processing.
	 *
	 * @return  mixed
	 */
	public function getDomain()
	{
		return $this->domain;
	}

	/**
	 * Set the current engine domain
	 *
	 * @param   string  $domain
	 *
	 * @throws InvalidArgumentException
	 */
	protected function setDomain($domain)
	{
		if (!is_string($domain))
		{
			throw new InvalidArgumentException(sprintf("Parameter \$domain to %s::%s must be a string, %s given", __CLASS__, __METHOD__, gettype($domain)));
		}

		$this->domain = $domain;
	}
}