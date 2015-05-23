<?php
namespace phootwork\file;

use phootwork\lang\String;

class Path {
	
	private $extension;
	private $pathName;
	private $segments;
	
	public function __construct($pathName) {
		$this->init($pathName);
	}
	
	private function init($pathName) {
		$this->pathName = new String($pathName);
		$this->segments = $this->pathName->split('/');

		$pathInfo = pathinfo($this->pathName);
		$this->extension = $pathInfo['extension'];
		$this->fileName = $pathInfo['filename'];
	}
	
	public function getExtension() {
		return pathinfo($this->pathName, PATHINFO_EXTENSION);
	}
	
	/**
	 * Returns the filename
	 *
	 * @return string the filename
	 */
	public function getFilename() {
		return basename($this->pathName);
	}
	
	/**
	 * Gets the path without filename
	 *
	 * @return string
	 */
	public function getDirectory() {
		return dirname($this->pathName);
	}
	
	/**
	 * Gets the full pathname
	 *
	 * @return String
	 */
	public function getPathname() {
		return $this->pathName;
	}
	
	/**
	 * Changes the extension of this path
	 * 
	 * @param string $extension the new extension
	 * @return Path $this
	 */
	public function setExtension($extension) {
		$pathinfo = pathinfo($this->pathName);
		
		$pathName = new String($pathinfo['dirname']);
		if (!empty($pathinfo['dirname'])) {
			$pathName = $pathName->append('/');
		}
		
		$this->init($pathName
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
	 * @return self
	 */
	public function addTrailingSeparator() {
		if (!$this->hasTrailingSeparator()) {
			$this->pathName = $this->pathName->append('/');
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
		
		$this->pathName = $this->pathName->append('/' . $path)->replace('//', '/');
		return $this;
	}

	/**
	 * Returns whether this path has a trailing separator.
	 * 
	 * @return boolean
	 */
	public function hasTrailingSeparator() {
		return $this->pathName->endsWith('/');
	}
	
	/**
	 * Returns whether this path is an absolute path.
	 * 
	 * @return boolean
	 */
	public function isAbsolute() {
		
	}
	
	/**
	 * 
	 * @param Path $anotherPath
	 * @return boolean
	 */
	public function isPrefixOf(Path $anotherPath) {
		return $this->pathName->startsWith($anotherPath->getPathname());
	}

	/**
	 * Returns the last segment of this path, or null if it does not have any segments.
	 * 
	 * @return String
	 */
	public function lastSegment() {
		if (count($this->segments)) {
			return new String($this->segments[count($this->segments) - 1]);
		}
		
		return null;
	}

	/**
	 * 
	 * @param Path $base
	 */
	public function makeRelativeTo(Path $base) {
		return new Path($this->pathName->replace($base->removeTrailingSeparator()->getPathname(), ''));
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
		return new Path($this->pathName->replace('.' . $this->getExtension(), ''));
	}
	
	/**
	 * Returns a copy of this path with the given number of segments removed from the beginning.
	 * 
	 * @param int $count
	 * @return Path
	 */
	public function removeFirstSegments($count) {
		$pathName = '';
		for ($i = $count; $i < $this->segmentCount(); $i++) {
			$pathName .= '/' .$this->segments[$i];
		}
		return new Path($pathName);
	}
	
	/**
	 * Returns a copy of this path with the given number of segments removed from the end.
	 * 
	 * @param int $count
	 * @return Path
	 */
	public function removeLastSegments($count) {
		$pathName = '';
		for ($i = 0; $i < $this->segmentCount() - $count; $i++) {
			$pathName .= '/' .$this->segments[$i];
		}
		return new Path($pathName);
	}
	
	/**
	 * Returns a copy of this path with the same segments as this path but with a trailing separator removed.
	 * 
	 * @return Path
	 */
	public function removeTrailingSeparator() {
		if ($this->hasTrailingSeparator()) {
			return new Path($this->pathName->substring(0, -1));
		}
		return new Path($this->pathName);
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
		return count($this->segments);
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
		return new FileDescriptor($this->pathName);
	}
	
	/**
	 * Returns a string representation of this path
	 * 
	 * @return String A string representation of this path
	 */
	public function toString() {
		return $this->pathName;
	}
	
	/**
	 * Returns a copy of this path truncated after the given number of segments.
	 * 
	 * @param int $count
	 * @return Path	
	 */
	public function upToSegment($count) {
		$pathName = '';
		for ($i = 0; $i < $count - 1; $i++) {
			$pathName .= '/' .$this->segments[$i];
		}
		return new Path($pathName);
	}
	
}
