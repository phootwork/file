<?php
namespace phootwork\file;

use phootwork\lang\ArrayObject;
use phootwork\lang\Text;

class Path {
	
	/** @var ArrayObject */
	private $segments;
	
	/** @var Text */
	private $pathname;
	
	/** @var string */
	private $dirname;
	
	/** @var string */
	private $filename;
	
	/** @var string */
	private $extension;
	
	
	public function __construct($pathname) {
		$this->init($pathname);
	}

	private function init($pathname) {
		$this->pathname = $pathname instanceof Text ? $pathname : new Text($pathname);
		$this->segments = $this->pathname->split('/');
		$this->extension = pathinfo($this->pathname, PATHINFO_EXTENSION);
		$this->filename = basename($this->pathname);
		$this->dirname = dirname($this->pathname);
	}

	/**
	 * Returns the extension
	 * 
	 * @return string the extension
	 */
	public function getExtension() {
		return $this->extension;
	}
	
	/**
	 * Returns the filename
	 *
	 * @return string the filename
	 */
	public function getFilename() {
		return $this->filename;
	}
	
	/**
	 * Gets the path without filename
	 *
	 * @return string
	 */
	public function getDirname() {
		return $this->dirname;
	}
	
	/**
	 * Gets the full pathname
	 *
	 * @return Text
	 */
	public function getPathname() {
		return $this->pathname;
	}
	
	/**
	 * Changes the extension of this path
	 * 
	 * @param string $extension the new extension
	 * @return $this
	 */
	public function setExtension($extension) {
		$pathinfo = pathinfo($this->pathname);
		
		$pathname = new Text($pathinfo['dirname']);
		if (!empty($pathinfo['dirname'])) {
			$pathname = $pathname->append('/');
		}
		
		$this->init($pathname
			->append($pathinfo['filename'])
			->append('.')
			->append($extension))
		;

		return $this;
	}
	
	/**
	 * Returns a path with the same segments as this path but with a 
	 * trailing separator added (if not already existent).
	 * 
	 * @return $this
	 */
	public function addTrailingSeparator() {
		if (!$this->hasTrailingSeparator()) {
			$this->pathname = $this->pathname->append('/');
		}
		return $this;
	}

	/**
	 * Returns the path obtained from the concatenation of the given path's 
	 * segments/string to the end of this path.
	 * 
	 * @param string|Text|Path $path
	 * @return Path
	 */
	public function append($path) {
		if ($path instanceof Path) {
			$path = $path->getPathname();
		}
		
		if (!$this->hasTrailingSeparator()) {
			$this->addTrailingSeparator();
		}
		
		return new Path($this->pathname->append($path));
	}

	/**
	 * Returns whether this path has a trailing separator.
	 * 
	 * @return boolean
	 */
	public function hasTrailingSeparator() {
		return $this->pathname->endsWith('/');
	}
	
	/**
	 * Returns whether this path is empty
	 * 
	 * @return boolean
	 */
	public function isEmpty() {
		return $this->pathname->isEmpty();
	}
	
	/**
	 * Returns whether this path is an absolute path.
	 * 
	 * @return boolean
	 */
	public function isAbsolute() {
		if (realpath($this->pathname->toString()) == $this->pathname->toString()) {
			return true;
		}
		
		if ($this->pathname->length() == 0 || $this->pathname->charAt(0) == '.') {
			return false;
		}
		
		// Windows allows absolute paths like this.
		if ($this->pathname->match('#^[a-zA-Z]:\\\\#')) {
			return true;
		}

		// A path starting with / or \ is absolute; anything else is relative.
		return $this->pathname->charAt(0) == '/' || $this->pathname->charAt(0) == '\\';
	}
	
	/**
	 * Checks whether this path is the prefix of another path
	 * 
	 * @param Path $anotherPath
	 * @return boolean
	 */
	public function isPrefixOf(Path $anotherPath) {
		return $anotherPath->getPathname()->startsWith($this->pathname);
	}

	/**
	 * Returns the last segment of this path, or null if it does not have any segments.
	 * 
	 * @return Text
	 */
	public function lastSegment() {
		return new Text($this->segments[count($this->segments) - 1]);
	}

	/**
	 * Makes the path relative to another given path
	 * 
	 * @param Path $base
	 * @return Path the new relative path
	 */
	public function makeRelativeTo(Path $base) {
		$pathname = clone $this->pathname;
		return new Path($pathname->replace($base->removeTrailingSeparator()->getPathname(), ''));
	}
	
	/**
	 * Returns a count of the number of segments which match in this 
	 * path and the given path, comparing in increasing segment number order.
	 * 
	 * @param Path $anotherPath
	 * @return int
	 */
	public function matchingFirstSegments(Path $anotherPath) {
		$segments = $anotherPath->segments();
		$count = 0;
		foreach ($this->segments as $i => $segment) {
			if ($segment != $segments[$i]) {
				break;
			}
			$count++;
		}
		
		return $count;
	}
	
	/**
	 * Returns a new path which is the same as this path but with the file extension removed.
	 * 
	 * @return Path
	 */
	public function removeExtension() {
		return new Path($this->pathname->replace('.' . $this->getExtension(), ''));
	}
	
	/**
	 * Returns a copy of this path with the given number of segments removed from the beginning.
	 * 
	 * @param int $count
	 * @return Path
	 */
	public function removeFirstSegments($count) {
		$segments = new ArrayObject();
		for ($i = $count; $i < $this->segmentCount(); $i++) {
			$segments->push($this->segments[$i]);
		}
		return new Path($segments->join('/'));
	}
	
	/**
	 * Returns a copy of this path with the given number of segments removed from the end.
	 * 
	 * @param int $count
	 * @return Path
	 */
	public function removeLastSegments($count) {
		$segments = new ArrayObject();
		for ($i = 0; $i < $this->segmentCount() - $count; $i++) {
			$segments->push($this->segments[$i]);
		}
		return new Path($segments->join('/'));
	}
	
	/**
	 * Returns a copy of this path with the same segments as this path but with a trailing separator removed.
	 * 
	 * @return $this
	 */
	public function removeTrailingSeparator() {
		if ($this->hasTrailingSeparator()) {
			$this->pathname = $this->pathname->substring(0, -1);
		}
		return $this;
	}
	
	/**
	 * Returns the specified segment of this path, or null if the path does not have such a segment.
	 * 
	 * @param int $index
	 * @return string
	 */
	public function segment($index) {
		if (isset($this->segments[$index])) {
			return $this->segments[$index];
		}

		return null;
	}
	
	/**
	 * Returns the number of segments in this path.
	 * 
	 * @return int
	 */
	public function segmentCount() {
		return $this->segments->count();
	}
	
	/**
	 * Returns the segments in this path in order.
	 * 
	 * @return ArrayObject<string>
	 */
	public function segments() {
		return $this->segments;
	}
	
	/**
	 * Returns a FileDescriptor corresponding to this path.
	 * 
	 * @return FileDescriptor
	 */
	public function toFileDescriptor() {
		return new FileDescriptor($this->pathname);
	}
	
	/**
	 * Returns a string representation of this path
	 * 
	 * @return string A string representation of this path
	 */
	public function toString() {
		return $this->pathname->toString();
	}
	
	/**
	 * String representation as pathname
	 */
	public function __toString() {
		return $this->toString();
	}
	
	/**
	 * Returns a copy of this path truncated after the given number of segments.
	 * 
	 * @param int $count
	 * @return Path
	 */
	public function upToSegment($count) {
		$segments = new ArrayObject();
		for ($i = 0; $i < $count; $i++) {
			$segments->push($this->segments[$i]);
		}

		return new Path($segments->join('/'));
	}
	
	/**
	 * Checks whether both paths point to the same location
	 * 
	 * @param Path|string $anotherPath
	 * @return boolean true if the do, false if they don't
	 */
	public function equals($anotherPath) {
		$anotherPath = $anotherPath instanceof Path ? $anotherPath : new Path($anotherPath);

		// do something else, when path's are urls
		$regexp = '/^[a-zA-Z]+:\/\//';
		$thisUrl = $this->pathname->match($regexp);
		$anotherUrl = $anotherPath->getPathname()->match($regexp);

		if ($thisUrl ^ $anotherUrl) {
			return false;
		} else if ($thisUrl && $anotherUrl) {
			return $this->pathname->equals($anotherPath->getPathname());
		}

		return realpath($this->pathname->toString()) == realpath($anotherPath->toString());
	}

}
