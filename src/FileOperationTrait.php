<?php
namespace phootwork\file;

use \DateTime;
use phootwork\file\exception\FileException;
use phootwork\lang\Text;

trait FileOperationTrait {
	
	protected $pathname;
	
	/**
	 * Static instantiator
	 * 
	 * @param string $pathname
	 * @return static
	 */
	public static function create($pathname) {
		return new static($pathname);
	}

	protected function init($pathname) {
		$this->pathname = ''.$pathname; // "cast" to string
	}

	/**
	 * Returns the file extensions
	 * 
	 * @return string the file extension
	 */
	public function getExtension() {
		return pathinfo($this->pathname, PATHINFO_EXTENSION);
	}

	/**
	 * Returns the filename
	 * 
	 * @return string the filename
	 */
	public function getFilename() {
		return basename($this->pathname);
	}
	
	/**
	 * Gets the path without filename
	 * 
	 * @return string
	 */
	public function getDirname() {
		return dirname($this->pathname);
	}
	
	/**
	 * Gets the path to the file
	 * 
	 * @return Text
	 */
	public function getPathname() {
		return $this->pathname;
	}
	
	/**
	 * Converts the path into a path object
	 *
	 * @return Path
	 */
	public function toPath() {
		return new Path($this->pathname);
	}
	
	/**
	 * Gets last access time.
	 * 
	 * @return DateTime
	 */
	public function getLastAccessedAt() {
		$timestamp = fileatime($this->pathname);
		$time = new DateTime();
		$time->setTimestamp($timestamp);
		return $time;
	}
	
	/**
	 * Gets the created time.
	 *
	 * @return DateTime
	 */
	public function getCreatedAt() {
		$timestamp = filectime($this->pathname);
		$time = new DateTime();
		$time->setTimestamp($timestamp);
		return $time;
	}
	
	/**
	 * Gets last modified time.
	 *
	 * @return DateTime
	 */
	public function getModifiedAt() {
		$timestamp = filemtime($this->pathname);
		$time = new DateTime();
		$time->setTimestamp($timestamp);
		return $time;
	}
	
	/**
	 * Gets file inode
	 * 
	 * @return int Returns the inode number of the file, or FALSE on failure. 
	 */
	public function getInode() {
		return fileinode($this->pathname);
	}
	
	/**
	 * Gets file group
	 * 
	 * @return int Returns the group ID, or FALSE if an error occurs.
	 */
	public function getGroup() {
		return filegroup($this->pathname);
	}
	
	/**
	 * Gets file owner
	 *
	 * @return int Returns the user ID of the owner, or FALSE on failure.
	 */
	public function getOwner() {
		return fileowner($this->pathname);
	}
	
	/**
	 * Gets file permissions
	 * 
	 * @return int Returns the file's permissions as a numeric mode. Lower bits of this 
	 * 		mode are the same as the permissions expected by chmod(), however on most platforms 
	 * 		the return value will also include information on the type of file given as filename. 
	 * 		The examples below demonstrate how to test the return value for specific permissions 
	 * 		and file types on POSIX systems, including Linux and Mac OS X. 
	 */
	public function getPermissions() {
		return fileperms($this->pathname);
	}
	
	/**
	 * Checks its existance
	 *
	 * @return boolean Returns TRUE if exists; FALSE otherwise. Will return FALSE for symlinks
	 * 		pointing to non-existing files.
	 */
	public function exists() {
		return file_exists($this->pathname);
	}
	
	/**
	 * Tells whether is executable
	 *
	 * @return boolean Returns TRUE if exists and is executable.
	 */
	public function isExecutable() {
		return is_executable($this->pathname);
	}
	
	/**
	 * Tells whether is readable
	 *
	 * @return boolean Returns TRUE if exists and is readable.
	 */
	public function isReadable() {
		return is_readable($this->pathname);
	}
	
	/**
	 * Tells whether is writable
	 * 
	 * @return boolean Returns TRUE if exists and is writable. 
	 */
	public function isWritable() {
		return is_writable($this->pathname);
	}

	/**
	 * Tells whether the filename is a symbolic link
	 *
	 * @return boolean Returns TRUE if the filename exists and is a symbolic link, FALSE otherwise.
	 */
	public function isLink() {
		return is_link($this->pathname);
	}
	
	/**
	 * Returns the target if this is a symbolic link
	 * 
	 * @see #isLink
	 * @return Path|null The target path or null if this isn't a link
	 */
	public function getLinkTarget() {
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
	 * @param mixed $group A group name or number.
	 * @return boolean Returns TRUE on success or FALSE on failure.
	 */
	public function setGroup($group) {
		if ($this->isLink()) {
			return lchgrp($this->pathname, $group);
		} else {
			return chgrp($this->pathname, $group);
		}
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
	 * @return boolean Returns TRUE on success or FALSE on failure.
	 */
	public function setMode($mode) {
		if ($this->isLink()) {
			return lchmod($this->pathname, $mode);
		} else {
			return chmod($this->pathname, $mode);
		}
	}
	
	/**
	 * Changes file owner
	 *
	 * Attempts to change the owner. Only the superuser may change the owner of a file.
	 *
	 * @param mixed $user A user name or number.
	 * @return boolean Returns TRUE on success or FALSE on failure.
	 */
	public function setOwner($user) {
		if ($this->isLink()) {
			return lchown($this->pathname, $user);
		} else {
			return chown($this->pathname, $user);
		}
	}
	
	/**
	 * Copies the file
	 *
	 * If the destination file already exists, it will be overwritten.
	 *
	 * @throws FileException When an error appeared.
	 * @param string|Path $destination The destination path.
	 */
	public function copy($destination) {
		$destination = $this->getDestination($destination);
	
		if (!@copy($this->getPathname(), $destination)) {
			throw new FileException(sprintf('Failed to copy %s to %s', $this->pathname, $destination));
		}
	}
	
	/**
	 * Moves the file
	 *
	 * @throws FileException When an error appeared.
	 * @param string|Path $destination
	 */
	public function move($destination) {
		$destination = $this->getDestination($destination);

		if (@rename($this->getPathname(), $destination)) {
			$this->pathname = $destination;
		} else {
			throw new FileException(sprintf('Failed to move %s to %s', $this->pathname, $destination));
		}
	}
	
	/**
	 * Transforms destination into path and ensures, parent directory exists
	 * 
	 * @param string $destination
	 * @return Path
	 */
	private function getDestination($destination) {
		$destination = $destination instanceof Path ? $destination : new Path($destination);
		$targetDir = new Directory($destination->getDirname());
		$targetDir->make();
		return $destination;
	}
	
	/**
	 * Creates a symlink to the given destination
	 * 
	 * @param string|Path $destination
	 */
	public function linkTo($destination) {
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

		if (!$ok && @symlink($this->pathname, $target->getPathname()) !== true) {
			$report = error_get_last();
			if (is_array($report) && DIRECTORY_SEPARATOR === '\\' && strpos($report['message'], 'error code(1314)') !== false) {
				throw new FileException('Unable to create symlink due to error code 1314: \'A required privilege is not held by the client\'. Do you have the required Administrator-rights?');
			}
			throw new FileException(sprintf('Failed to create symbolic link from %s to %s', $this->pathname, $targetDir));
		}
	}

	public abstract function delete();

}