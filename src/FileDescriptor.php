<?php
namespace phootwork\file;

class FileDescriptor {

	use FileOperationTrait;

	public static function fromFileInfo(\SplFileInfo $fileInfo) {
		return new self($fileInfo->getPathname());
	}

	public function __construct($filename) {
		$this->init($filename);
	}
	
	/**
	 * Tells whether this is a regular file
	 *
	 * @return boolean Returns TRUE if the filename exists and is a regular file, FALSE otherwise.
	 */
	public function isFile() {
		return is_file($this->pathname);
	}
	
	/**
	 * Tells whether the filename is a '.' or '..'
	 *
	 * @return boolean
	 */
	public function isDot() {
		return $this->getFilename() == '.' || $this->getFilename() == '..';
	}
	
	/**
	 * Tells whether this is a directory
	 *
	 * @return boolean Returns TRUE if the filename exists and is a directory, FALSE otherwise.
	 */
	public function isDir() {
		return is_dir($this->pathname);
	}

	public function toFile() {
		return new File($this->pathname);
	}
	
	public function toDirectory() {
		return new Directory($this->pathname);
	}
	
	public function delete() {
		if ($this->isDir()) {
			$this->toDirectory()->delete();
		} else {
			$this->toFile()->delete();
		}
	}

	public function __toString() {
		return $this->pathname;
	}
}
