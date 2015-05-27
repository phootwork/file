<?php
namespace phootwork\file;

use \DirectoryIterator;
use phootwork\file\exception\FileException;

class Directory implements \Iterator {
	
	use FileOperationTrait;
	
	private $iterator;
	
	public function __construct($filename) {
		$this->init($filename);
	}
	
	/**
	 * Creates the directory
	 * 
	 * @throws FileException when something goes wrong
	 * @param number $mode
	 * @return boolean true on success; false if it fails
	 */
	public function make($mode = 0777) {
		if (!$this->exists() && !@mkdir($this->pathname, $mode, true)) {
			throw new FileException(sprintf('Failed to create directory "%s"', $this->pathname));
		}
	}
	
	/**
	 * Recursively deletes the directory
	 *
	 * @throws FileException when something goes wrong
	 * @return boolean true on success; false if it fails
	 */
	public function delete() {
		foreach ($this as $file) {
			if (!$file->isDot()) {
				$file->delete();
			}
		}

		if (!@rmdir($this->pathname)) {
			throw new FileException(sprintf('Failed to delete directory "%s"', $this->pathname));
		}
	}

	/**
	 * Returns a directory iterator
	 * 
	 * @return DirectoryIterator
	 */
	private function getIterator() {
		if ($this->iterator === null) {
			$this->iterator = new DirectoryIterator($this->pathname);
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

	/**
	 * String representation of this directory as pathname
	 */
	public function __toString() {
		return $this->pathname;
	}
}
