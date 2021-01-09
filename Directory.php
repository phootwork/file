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

use DirectoryIterator;
use Iterator;
use phootwork\file\exception\FileException;
use Stringable;

/**
 * Class Directory
 */
class Directory implements Iterator, Stringable {
	use FileOperationTrait;

	/** @var ?DirectoryIterator */
	private ?DirectoryIterator $iterator = null;

	/**
	 * Directory constructor.
	 *
	 * @param string|Stringable $filename
	 */
	public function __construct(Stringable | string $filename) {
		$this->pathname = (string) $filename;
	}

	/**
	 * Creates the directory
	 * 
	 * @param int $mode
	 *
	 * @throws FileException when something goes wrong
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
	 *
	 * @internal
	 */
	public function current(): FileDescriptor {
		return FileDescriptor::fromFileInfo($this->getIterator()->current());
	}

	/**
	 * @internal
	 */
	public function key(): float | bool | int | string | null {
		return $this->getIterator()->key();
	}

	/**
	 * @internal
	 */
	public function next(): void {
		$this->getIterator()->next();
	}

	/**
	 * @internal
	 */
	public function rewind(): void {
		$this->getIterator()->rewind();
	}

	/**
	 * @internal
	 */
	public function valid(): bool {
		return $this->getIterator()->valid();
	}

	/**
	 * String representation of this directory as pathname
	 */
	public function __toString(): string {
		return $this->pathname;
	}
}
