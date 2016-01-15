<?php
namespace phootwork\file;

use \DateTime;
use phootwork\file\exception\FileException;

class File {
 
	use FileOperationTrait;
	
	public function __construct($filename) {
		$this->init($filename);
	}

	/**
	 * Reads contents from the file
	 * 
	 * @throws FileException
	 * @return string contents
	 */
	public function read() {
		if (!$this->exists()) {
			throw new FileException(sprintf('File does not exist: %s', $this->getFilename()));
		}

		return file_get_contents($this->pathname);
	}

	/**
	 * Writes contents to the file
	 *
	 * @param string $contents
	 * @return $this
	 */
	public function write($contents) {
		$dir = new Directory($this->getDirname());
		$dir->make();
	
		file_put_contents($this->pathname, $contents);
		return $this;
	}
	
	/**
	 * Touches the file
	 * 
	 * @param int|DateTime $created
	 * @param int|DateTime $lastAccessed
	 * @throws FileException when something goes wrong
	 */
	public function touch($created = null, $lastAccessed = null) {
		$created = $created instanceof DateTime 
			? $created->getTimestamp() 
			: $created === null ? time() : $created;
		$lastAccessed = $lastAccessed instanceof DateTime
			? $lastAccessed->getTimestamp()
			: $lastAccessed === null ? time() : $lastAccessed;
		
		if (!@touch($this->pathname, $created, $lastAccessed)) {
			throw new FileException(sprintf('Failed to touch file at %s', $this->pathname));
		}
	}
	
	/**
	 * Deletes the file
	 *
	 * @throws FileException when something goes wrong
	 */
	public function delete() {
		if (!@unlink($this->pathname)) {
			throw new FileException(sprintf('Failed to delete file at %s', $this->pathname));
		}
	}
	
	/**
	 * String representation of this file as pathname
	 */
	public function __toString() {
		return $this->pathname;
	}

}
