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

use phootwork\lang\ArrayObject;
use phootwork\lang\Text;

class Path {
	
	/** @var ArrayObject */
	private $segments;

	/** @var string */
	private $stream = '';
	
	/** @var Text */
	private $pathname;
	
	/** @var string */
	private $dirname;
	
	/** @var string */
	private $filename;
	
	/** @var string */
	private $extension;

	/**
	 * Path constructor.
	 *
	 * @param string|Text $pathname
	 * @todo does it make sense to accept empty strings as $pathname?
	 *       if yes, maybe we should add a Path::setPathname method
	 */
	public function __construct($pathname) {
		$this->init($pathname);
	}

	/**
	 * @param string|Text $pathname
	 */
	private function init($pathname): void {
		$this->pathname = $pathname instanceof Text ? $pathname : new Text($pathname);

		if ($this->pathname->match('/^[a-zA-Z]+:\/\//')) {
			$this->stream = $this->pathname->slice(0, (int) $this->pathname->indexOf('://') + 3)->toString();
			$this->pathname = $this->pathname->substring((int) $this->pathname->indexOf('://') + 3);
		}

		$this->segments = $this->pathname->split('/');
		$this->extension = pathinfo($this->pathname->toString(), PATHINFO_EXTENSION);
		$this->filename = basename($this->pathname->toString());
		$this->dirname = dirname($this->pathname->toString());
	}

	/**
	 * Returns the extension
	 * 
	 * @return string the extension
	 */
	public function getExtension(): string {
		return $this->extension;
	}
	
	/**
	 * Returns the filename
	 *
	 * @return string the filename
	 */
	public function getFilename(): string {
		return $this->filename;
	}
	
	/**
	 * Gets the path without filename
	 *
	 * @return string
	 */
	public function getDirname(): string {
		return $this->stream . $this->dirname;
	}

	//@todo Why this function returns Text and the other return string?
	/**
	 * Gets the full pathname
	 *
	 * @return Text
	 */
	public function getPathname(): Text {
		return new Text ($this->stream . $this->pathname);
	}

	/**
	 * @return bool
	 */
	public function isStream(): bool {
		return ('' !== $this->stream);
	}
	
	/**
	 * Changes the extension of this path
	 * 
	 * @param string $extension the new extension
	 * @return $this
	 */
	public function setExtension(string $extension): self {
		$pathinfo = pathinfo($this->pathname->toString());
		
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
	public function addTrailingSeparator(): self {
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
	public function append($path): Path {
		if ($path instanceof Path) {
			$path = $path->getPathname();
		}
		
		if (!$this->hasTrailingSeparator()) {
			$this->addTrailingSeparator();
		}
		
		return new Path($this->getPathname()->append($path));
	}

	/**
	 * Returns whether this path has a trailing separator.
	 * 
	 * @return boolean
	 */
	public function hasTrailingSeparator(): bool {
		return $this->pathname->endsWith('/');
	}
	
	/**
	 * Returns whether this path is empty
	 * 
	 * @return boolean
	 */
	public function isEmpty(): bool {
		return $this->pathname->isEmpty();
	}
	
	/**
	 * Returns whether this path is an absolute path.
	 * 
	 * @return boolean
	 */
	public function isAbsolute(): bool {
		//Stream urls are always absolute
		if ($this->isStream()) {
			return true;
		}

		if (realpath($this->pathname->toString()) == $this->pathname->toString()) {
			return true;
		}
		
		if ($this->pathname->length() == 0 || $this->pathname->startsWith('.')) {
			return false;
		}
		
		// Windows allows absolute paths like this.
		if ($this->pathname->match('#^[a-zA-Z]:\\\\#')) {
			return true;
		}

		// A path starting with / or \ is absolute; anything else is relative.
		return $this->pathname->startsWith('/') || $this->pathname->startsWith('\\');
	}
	
	/**
	 * Checks whether this path is the prefix of another path
	 * 
	 * @param Path $anotherPath
	 * @return boolean
	 */
	public function isPrefixOf(Path $anotherPath): bool {
		return $anotherPath->getPathname()->startsWith($this->pathname);
	}

	/**
	 * Returns the last segment of this path, or null if it does not have any segments.
	 * 
	 * @return Text
	 */
	public function lastSegment(): Text {
		return new Text($this->segments[count($this->segments) - 1]);
	}

	/**
	 * Makes the path relative to another given path
	 * 
	 * @param Path $base
	 * @return Path the new relative path
	 */
	public function makeRelativeTo(Path $base): Path {
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
	public function matchingFirstSegments(Path $anotherPath): int {
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
	public function removeExtension(): Path {
		return new Path($this->pathname->replace('.' . $this->getExtension(), ''));
	}
	
	/**
	 * Returns a copy of this path with the given number of segments removed from the beginning.
	 * 
	 * @param int $count
	 * @return Path
	 */
	public function removeFirstSegments(int $count): Path {
		$segments = new ArrayObject();
		for ($i = $count; $i < $this->segmentCount(); $i++) {
			$segments->append($this->segments[$i]);
		}
		return new Path($segments->join('/'));
	}
	
	/**
	 * Returns a copy of this path with the given number of segments removed from the end.
	 * 
	 * @param int $count
	 * @return Path
	 */
	public function removeLastSegments(int $count): Path {
		$segments = new ArrayObject();
		for ($i = 0; $i < $this->segmentCount() - $count; $i++) {
			$segments->append($this->segments[$i]);
		}
		return new Path($segments->join('/'));
	}
	
	/**
	 * Returns a copy of this path with the same segments as this path but with a trailing separator removed.
	 * 
	 * @return $this
	 */
	public function removeTrailingSeparator(): self {
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
	public function segment(int $index): ?string {
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
	public function segmentCount(): int {
		return $this->segments->count();
	}
	
	/**
	 * Returns the segments in this path in order.
	 * 
	 * @return ArrayObject<string>
	 */
	public function segments(): ArrayObject {
		return $this->segments;
	}
	
	/**
	 * Returns a FileDescriptor corresponding to this path.
	 * 
	 * @return FileDescriptor
	 */
	public function toFileDescriptor(): FileDescriptor {
		return new FileDescriptor($this->getPathname());
	}
	
	/**
	 * Returns a string representation of this path
	 * 
	 * @return string A string representation of this path
	 */
	public function toString(): string {
		return $this->stream . $this->pathname;
	}
	
	/**
	 * String representation as pathname
	 */
	public function __toString(): string {
		return $this->toString();
	}
	
	/**
	 * Returns a copy of this path truncated after the given number of segments.
	 * 
	 * @param int $count
	 * @return Path
	 */
	public function upToSegment(int $count): Path {
		$segments = new ArrayObject();
		for ($i = 0; $i < $count; $i++) {
			$segments->append($this->segments[$i]);
		}

		return new Path($segments->join('/'));
	}
	
	/**
	 * Checks whether both paths point to the same location
	 * 
	 * @param Path|string $anotherPath
	 * @return boolean true if the do, false if they don't
	 */
	public function equals($anotherPath): bool {
		$anotherPath = $anotherPath instanceof Path ? $anotherPath : new Path($anotherPath);

		if ($this->isStream() ^ $anotherPath->isStream()) {
			return false;
		}

		if ($this->isStream() && $anotherPath->isStream()) {
			return $this->toString() === $anotherPath->toString();
		}

		return realpath($this->pathname->toString()) == realpath($anotherPath->toString());
	}
}
