<?php declare(strict_types=1);
/**
 * This file is part of the Phootwork package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 * @copyright Thomas Gossmann
 */

namespace phootwork\file;

use \DirectoryIterator;
use \Iterator;
use phootwork\file\exception\FileException;
use phootwork\lang\Text;

class Directory implements Iterator {
	
	use FileOperationTrait;

	/** @var DirectoryIterator|null */
	private $iterator;

	/**
	 * Directory constructor.
	 *
	 * @param string|Text $filename
	 */
	public function __construct($filename) {
		$this->init($filename);
	}
	
	/**
	 * Creates the directory
	 * 
	 * @throws FileException when something goes wrong
	 * @param int $mode
	 */
	public function make(int $mode = 0777): void {
		if (!$this->exists() && !@mkdir($this->pathname, $mode, true)) {
			throw new FileException(sprintf('Failed to create directory "%s"', $this->pathname));
		}
	}
	
	/**
	 * Recursively deletes the directory
	 *
	 * @throws FileException when something goes wrong
	 */
	public function delete(): void {
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
	private function getIterator(): DirectoryIterator {
		if ($this->iterator === null) {
			$this->iterator = new DirectoryIterator($this->pathname);
		}

		return $this->iterator;
	}
	
	/**
	 * @return FileDescriptor
	 * @internal
	 */
	public function current (): FileDescriptor {
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
		$this->getIterator()->next();
	}

	/**
	 * @internal
	 */
	public function rewind () {
		$this->getIterator()->rewind();
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
	public function __toString(): string {
		return $this->pathname;
	}
}
