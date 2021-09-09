<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/**
 * A WriterInterface implementation which does absolutely nothing
 *
 * @package Akeeba\Replace\Writer
 */
class ANGIENullWriter implements Akeeba\Replace\Writer\WriterInterface
{
	protected $fakeFile = '';

	public function __construct($filePath, $reset = true)
	{
		$this->fakeFile = $filePath;
	}

	public function getFilePath()
	{
		return $this->fakeFile;
	}

	public function setMaxFileSize($bytes)
	{
	}

	public function getMaxFileSize()
	{
		return 0;
	}

	public function writeLine($line, $eol = "\n")
	{
	}

	public function getNumberOfParts()
	{
		return 0;
	}

	public function getListOfParts()
	{
		return [];
	}

	public function reset()
	{
	}

}