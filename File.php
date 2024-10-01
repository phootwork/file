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

use DateTime;
use phootwork\file\exception\FileException;
use phootwork\lang\Text;
use Stringable;

/**
 * Class File
 */
class File implements Stringable {
	use FileOperationTrait;

	public function __construct(string|Stringable $filename) {
		$this->pathname = (string) $filename;
	}

	/**
	 * Reads contents from the file
	 * 
	 * @throws FileException
	 *
	 * @return Text contents
	 */
	public function read(): Text {
		if (!$this->exists()) {
			throw new FileException(sprintf('File does not exist: %s', $this->getFilename()->toString()));
		}

		if (!$this->isReadable()) {
			throw new FileException(sprintf('You don\'t have permissions to access %s file', $this->getFilename()->toString()));
		}

		return new Text(file_get_contents($this->pathname));
	}

	/**
	 * Writes contents to the file
	 *
	 * @param string|Stringable $contents
	 *
	 * @throws FileException
	 *
	 * @return $this
	 */
	public function write(Stringable|string $contents): self {
		$dir = new Directory($this->getDirname());
		$dir->make();

		if ($this->exists() && !$this->isWritable()) {
			throw new FileException(
				"Impossible to write the file `{$this->getPathname()}`: do you have enough permissions?"
			);
		}

		file_put_contents($this->pathname, (string) $contents);

		return $this;
	}

	/**
	 * Append the content at the end of the file.
	 *
	 * @param Stringable|string $contents The content to append
	 *
	 * @throws FileException if the file does not exist or is not writable
	 *
	 * @return $this
	 */
	public function append(Stringable|string $contents): self {
		if (!$this->exists() || !$this->isWritable()) {
			throw new FileException(
				"Impossible to write the file `{$this->getPathname()}`: do you have enough permissions?"
			);
		}

		file_put_contents($this->pathname, (string) $contents, FILE_APPEND);

		return $this;
	}

	/**
	 * Touches the file
	 *
	 * @param DateTime|int|null $created
	 * @param DateTime|int|null $lastAccessed
	 *
	 * @throws FileException when something goes wrong
	 */
	public function touch(DateTime|int|null $created = null, DateTime|int|null $lastAccessed = null): void {
		$created = $created instanceof DateTime
			? $created->getTimestamp()
			: ($created === null ? time() : $created);
		$lastAccessed = $lastAccessed instanceof DateTime
			? $lastAccessed->getTimestamp()
			: ($lastAccessed === null ? time() : $lastAccessed);

		if (!@touch($this->pathname, $created, $lastAccessed)) {
			throw new FileException(sprintf('Failed to touch file at %s', $this->pathname));
		}
	}

	/**
	 * Deletes the file
	 *
	 * @throws FileException when something goes wrong
	 */
	public function delete(): void {
		if (!@unlink($this->pathname)) {
			throw new FileException(sprintf('Failed to delete file at %s', $this->pathname));
		}
	}

	/**
	 * Get the size of the file in bytes.
	 *
	 * @throws FileException If the file does not exist or it's not accessible
	 */
	public function getSize(): int {
		$size = @filesize($this->getPathname()->toString());

		if ($size === false) {
			$message = Text::create(error_get_last()['message'] ?? '')
				->replace('filesize(): ', '')
				->prepend('Impossible to get the file size: ')
				->trim(' :')
				->ensureEnd('.')
			;

			throw new FileException((string) $message);
		}

		return $size;
	}

	/**
	 * String representation of this file as pathname
	 */
	public function __toString(): string {
		return $this->pathname;
	}
}
