<?php
namespace phootwork\file;

use phootwork\file\exception\FileException;

class File {
 
	use FileOperationTrait;
	
	public function __construct($fileName) {
		$this->pathName = $fileName;
	}

	/**
	 * Checks whether the file exists
	 *
	 * @return boolean Returns TRUE if exists; FALSE otherwise. Will return FALSE for symlinks
	 * 		pointing to non-existing files.
	 */
	public function exists() {
		return file_exists($this->pathName);
	}
	
	public function getContents() {
		if (!$this->exists()) {
			throw new FileException(sprintf('File does not exist: %s', $this->getFilename()));
		}
	
		if ($this->isFile()) {
			return file_get_contents($this->pathName);
		}
	}
	
	public function setContents($contents) {
		file_put_contents($this->pathName, $contents);
	}
	
	public function append($contents) {
		$this->write($this->read() . $contents);
	}
	
	public function prepend($contents) {
		$this->write($contents . $this->read());
	}
	
}
