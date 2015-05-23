<?php
namespace phootwork\file;

use \DirectoryIterator;

class Directory implements \Iterator {
	
	use FileOperationTrait;
	
	private $iterator;
	
	public function __construct($fileName) {
		$this->pathName = $fileName;
	}

	/**
	 * Checks whether the directory exists
	 *
	 * @return boolean Returns TRUE if exists; FALSE otherwise. Will return FALSE for symlinks
	 * 		pointing to non-existing files.
	 */
	public function exists() {
		return file_exists($this->pathName);
	}
	
	/**
	 * Creates the directory
	 * 
	 * @param number $mode
	 * @return TRUE on success; FALSE if it fails
	 */
	public function create($mode = 0777) {
		if (!$this->exists()) {
			return mkdir($this->pathName, $mode, true);
		}
		return true;
	}

	/**
	 * Returns a directory iterator
	 * 
	 * @return DirectoryIterator
	 */
	private function getIterator() {
		if ($this->iterator === null) {
			$this->iterator = new DirectoryIterator($this->pathName);
		}
		return $this->iterator;
	}
	
	/**
	 * @return FileDescriptor
	 * @internal
	 */
	public function current () {
		return FileDescriptor::fromFileInfo($this->getIterator()->current());
	}

	/**
	 * @internal
	 */
	public function key () {
		return $this->getIterator()->key();
	}

	/**
	 * @internal
	 */
	public function next () {
		return $this->getIterator()->next();
	}

	/**
	 * @internal
	 */
	public function rewind () {
		return $this->getIterator()->rewind();
	}

	/**
	 * @internal
	 */
	public function valid () {
		return $this->getIterator()->valid();
	}

}
