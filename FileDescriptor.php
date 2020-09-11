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

use phootwork\lang\Text;

/**
 * Class FileDescriptor
 *
 * @psalm-consistent-constructor
 */
class FileDescriptor {
	use FileOperationTrait;

	/**
	 * Creates a new FileDescriptor from SplFileInfo
	 * 
	 * @param \SplFileInfo $fileInfo
	 *
	 * @return FileDescriptor
	 */
	public static function fromFileInfo(\SplFileInfo $fileInfo): self {
		return new self($fileInfo->getPathname());
	}

	/**
	 * FileDescriptor constructor.
	 *
	 * @param string|Text $filename
	 */
	public function __construct($filename) {
		$this->init($filename);
	}

	/**
	 * Tells whether this is a regular file
	 *
	 * @return bool Returns TRUE if the filename exists and is a regular file, FALSE otherwise.
	 */
	public function isFile(): bool {
		return is_file($this->pathname);
	}

	/**
	 * Tells whether the filename is a '.' or '..'
	 *
	 * @return bool
	 */
	public function isDot(): bool {
		return $this->getFilename() == '.' || $this->getFilename() == '..';
	}

	/**
	 * Tells whether this is a directory
	 *
	 * @return bool Returns TRUE if the filename exists and is a directory, FALSE otherwise.
	 */
	public function isDir(): bool {
		return is_dir($this->pathname);
	}

	/**
	 * Converts this file descriptor into a file object
	 * 
	 * @return File
	 */
	public function toFile(): File {
		return new File($this->pathname);
	}

	/**
	 * Converts this file descriptor into a directory object
	 *
	 * @return Directory
	 */
	public function toDirectory(): Directory {
		return new Directory($this->pathname);
	}

	/**
	 * Deletes the file
	 *
	 * @throws exception\FileException
	 */
	public function delete(): void {
		if ($this->isDir()) {
			$this->toDirectory()->delete();
		} else {
			$this->toFile()->delete();
		}
	}

	/**
	 * String representation of this file as pathname
	 */
	public function __toString(): string {
		return $this->pathname;
	}
}
