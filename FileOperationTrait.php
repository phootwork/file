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

trait FileOperationTrait {
	protected string $pathname;

	abstract public function delete(): void;
	abstract public function __construct(string|Stringable $pathname);

	/**
	 * Static instantiator
	 *
	 * @param string|Stringable $pathname
	 *
	 * @return self
	 */
	public static function create(string|Stringable $pathname): self {
		return new self($pathname);
	}

	/**
	 * Returns the file extensions
	 *
	 * @return Text the file extension
	 */
	public function getExtension(): Text {
		return new Text(pathinfo($this->pathname, PATHINFO_EXTENSION));
	}

	/**
	 * Returns the filename
	 *
	 * @return Text the filename
	 */
	public function getFilename(): Text {
		return new Text(basename($this->pathname));
	}

	/**
	 * Gets the path without filename
	 *
	 * @return Text
	 */
	public function getDirname(): Text {
		return new Text(dirname($this->pathname));
	}

	/**
	 * Gets the path to the file
	 *
	 * @return Text
	 */
	public function getPathname(): Text {
		return new Text($this->pathname);
	}

	/**
	 * Converts the path into a path object
	 *
	 * @return Path
	 */
	public function toPath(): Path {
		return new Path($this->pathname);
	}

	/**
	 * Gets last access time.
	 *
	 * @throws FileException
	 *
	 * @return DateTime
	 */
	public function getLastAccessedAt(): DateTime {
		try {
			$timestamp = fileatime($this->pathname);
			$time = new DateTime();
			$time->setTimestamp($timestamp);

			return $time;
		} catch (\Exception $e) {
			throw new FileException($e->getMessage(), (int) $e->getCode(), $e);
		}
	}

	/**
	 * Gets the created time.
	 *
	 * @throws FileException
	 *
	 * @return DateTime
	 */
	public function getCreatedAt(): DateTime {
		try {
			$timestamp = filemtime($this->pathname);
			$time = new DateTime();
			$time->setTimestamp($timestamp);

			return $time;
		} catch (\Exception $e) {
			throw new FileException($e->getMessage(), (int) $e->getCode(), $e);
		}
	}

	/**
	 * Gets last modified time.
	 *
	 * @throws FileException
	 *
	 * @return DateTime
	 */
	public function getModifiedAt(): DateTime {
		try {
			$timestamp = filemtime($this->pathname);
			$time = new DateTime();
			$time->setTimestamp($timestamp);

			return $time;
		} catch (\Exception $e) {
			throw new FileException($e->getMessage(), (int) $e->getCode(), $e);
		}
	}

	/**
	 * Gets file inode
	 *
	 * @return int|null Returns the inode number of the file, or NULL on failure.
	 */
	public function getInode(): ?int {
		$inode = fileinode($this->pathname);

		return false === $inode ? null : $inode;
	}

	/**
	 * Gets file group
	 *
	 * @return int|null Returns the group ID, or NULL if an error occurs.
	 */
	public function getGroup(): ?int {
		$group = filegroup($this->pathname);

		return false === $group ? null : $group;
	}

	/**
	 * Gets file owner
	 *
	 * @return int|null Returns the user ID of the owner, or NULL on failure.
	 */
	public function getOwner(): ?int {
		$owner = fileowner($this->pathname);

		return false === $owner ? null : $owner;
	}

	/**
	 * Gets file permissions
	 *
	 * @return int Returns the file's permissions as a numeric mode. Lower bits of this
	 * 		mode are the same as the permissions expected by chmod(), however on most platforms
	 * 		the return value will also include information on the type of file given as filename.
	 */
	public function getPermissions(): int {
		return fileperms($this->pathname);
	}

	/**
	 * Checks its existance
	 *
	 * @return bool Returns TRUE if exists; FALSE otherwise. Will return FALSE for symlinks
	 * 		pointing to non-existing files.
	 */
	public function exists(): bool {
		return file_exists($this->pathname);
	}

	/**
	 * Tells whether is executable
	 *
	 * @return bool Returns TRUE if exists and is executable.
	 */
	public function isExecutable(): bool {
		return is_executable($this->pathname);
	}

	/**
	 * Tells whether is readable
	 *
	 * @return bool Returns TRUE if exists and is readable.
	 */
	public function isReadable(): bool {
		return is_readable($this->pathname);
	}

	/**
	 * Tells whether is writable
	 *
	 * @return bool Returns TRUE if exists and is writable.
	 */
	public function isWritable(): bool {
		return is_writable($this->pathname);
	}

	/**
	 * Tells whether the filename is a symbolic link
	 *
	 * @return bool Returns TRUE if the filename exists and is a symbolic link, FALSE otherwise.
	 */
	public function isLink(): bool {
		return is_link($this->pathname);
	}

	/**
	 * Returns the target if this is a symbolic link
	 *
	 * @see #isLink
	 *
	 * @return Path|null The target path or null if this isn't a link
	 */
	public function getLinkTarget(): ?Path {
		if ($this->isLink()) {
			return new Path(readlink($this->pathname));
		}

		return null;
	}

	/**
	 * Attempts to change the group.
	 *
	 * Only the superuser may change the group arbitrarily; other users may
	 * change the group of a file to any group of which that user is a member.
	 *
	 * @param string|int $group A group name or number.
	 *
	 * @return bool Returns TRUE on success or FALSE on failure.
	 */
	public function setGroup(string|int $group): bool {
		return $this->isLink() ? lchgrp($this->pathname, $group) : chgrp($this->pathname, $group);
	}

	/**
	 * Attempts to change the mode.
	 *
	 * @see #setGroup
	 * @see #setOwner
	 *
	 * @param int $mode
	 * 		Note that mode is not automatically assumed to be an octal value, so strings
	 * 		(such as "g+w") will not work properly. To ensure the expected operation, you
	 * 		need to prefix mode with a zero (0).
	 *
	 * @return bool Returns TRUE on success or FALSE on failure.
	 */
	public function setMode(int $mode): bool {
		return chmod($this->pathname, $mode);
	}

	/**
	 * Changes file owner
	 *
	 * Attempts to change the owner. Only the superuser may change the owner of a file.
	 *
	 * @param string|int $user A user name or number.
	 *
	 * @return bool Returns TRUE on success or FALSE on failure.
	 */
	public function setOwner(string|int $user): bool {
		return $this->isLink() ? lchown($this->pathname, $user) : chown($this->pathname, $user);
	}

	/**
	 * Copies the file
	 *
	 * If the destination file already exists, it will be overwritten.
	 *
	 *
	 * @param string|Text|Path $destination The destination path.
	 *
	 * @throws FileException When an error appeared.
	 */
	public function copy(Path|Text|string $destination): void {
		$destination = $this->getDestination($destination);

		if (!@copy($this->getPathname()->toString(), $destination->toString())) {
			throw new FileException(sprintf('Failed to copy %s to %s', $this->pathname, (string) $destination));
		}
	}

	/**
	 * Moves the file
	 *
	 * @param string|Text|Path $destination
	 *
	 * @throws FileException When an error appeared.
	 */
	public function move(Path|Text|string $destination): void {
		$destination = $this->getDestination($destination);

		if (@rename($this->getPathname()->toString(), $destination->toString())) {
			$this->pathname = (string) $destination;
		} else {
			throw new FileException(sprintf('Failed to move %s to %s', $this->pathname, (string) $destination));
		}
	}

	/**
	 * Transforms destination into path and ensures, parent directory exists
	 *
	 * @param string|Text|Path $destination
	 *
	 * @throws FileException
	 *
	 * @return Path
	 */
	private function getDestination(Path|Text|string $destination): Path {
		$destination = $destination instanceof Path ? $destination : new Path($destination);
		$targetDir = new Directory($destination->getDirname());
		$targetDir->make();

		return $destination;
	}

	/**
	 * Creates a symlink to the given destination
	 *
	 * @param string|Path|Text $destination
	 *
	 * @throws FileException
	 * @psalm-suppress PossiblyNullReference If $target->isLink() is true then $target->getLinkTarget() is never null
	 */
	public function linkTo(Path|Text|string $destination): void {
		$target = new FileDescriptor($destination);
		$targetDir = new Directory($target->getDirname());
		$targetDir->make();

		$ok = false;
		if ($target->isLink()) {
			if (!$target->getLinkTarget()->equals($this->pathname)) {
				$target->delete();
			} else {
				$ok = true;
			}
		}

		if (!$ok && @symlink($this->pathname, $target->getPathname()->toString()) !== true) {
			$report = error_get_last();
			if (is_array($report) && DIRECTORY_SEPARATOR === '\\' && str_contains($report['message'], 'error code(1314)')) {
				throw new FileException('Unable to create symlink due to error code 1314: \'A required privilege is not held by the client\'. Do you have the required Administrator-rights?');
			}

			throw new FileException(sprintf('Failed to create symbolic link from %s to %s', $this->pathname, (string) $targetDir));
		}
	}
}
