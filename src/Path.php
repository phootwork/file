<?php
namespace phootwork\file;

use phootwork\lang\String;
use phootwork\lang\ArrayObject;

class Path {
	
	private $extension;
	private $pathname;
	private $segments;
	
	public function __construct($pathname) {
		$this->init($pathname);
	}

	private function init($pathname) {
		$this->pathname = $pathname instanceof String ? $pathname : new String($pathname);
		$this->segments = $this->pathname->split('/');

		$pathInfo = pathinfo($this->pathname);
		$this->fileName = $pathInfo['filename'];

		if (isset($pathInfo['extension'])) {
			$this->extension = $pathInfo['extension'];
		}
	}

	public function getExtension() {
		return $this->extension;
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
	 * Gets the full pathname
	 *
	 * @return String
	 */
	public function getPathname() {
		return $this->pathname;
	}
	
	/**
	 * Changes the extension of this path
	 * 
	 * @param string $extension the new extension
	 * @return Path $this
	 */
	public function setExtension($extension) {
		$pathinfo = pathinfo($this->pathname);
		
		$pathname = new String($pathinfo['dirname']);
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
	 * @param String|Path $path
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
	 * @return String
	 */
	public function lastSegment() {
		return new String($this->segments[count($this->segments) - 1]);
	}

	/**
	 * 
	 * @param Path $base
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
	 * @return String
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
	 * @return String[]
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

		if ($thisUrl xor $anotherUrl) {
			return false;
		} else if ($thisUrl && $anotherUrl) {
			return $this->pathname->equals($anotherPath->getPathname());
		}

		return realpath($this->pathname->toString()) == realpath($anotherPath->toString());
	}

}
