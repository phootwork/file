<?php
namespace phootwork\file\tests;

use org\bovigo\vfs\vfsStream;
use phootwork\file\Path;
use phootwork\lang\ArrayObject;

class PathTest extends \PHPUnit_Framework_TestCase {

	public function testBasicNaming() {
		$p = new Path('this/is/the/path/to/my/file.ext');
		
		$this->assertEquals('this/is/the/path/to/my', $p->getDirectory());
		$this->assertEquals('file.ext', $p->getFilename());
		$this->assertEquals('ext', $p->getExtension());
		$this->assertEquals('this/is/the/path/to/my/file.ext', $p->getPathname());
	}
	
	public function testSegments() {
		$p = new Path('this/is/the/path/to/my/file.ext');
		
		$this->assertEquals(new ArrayObject(['this', 'is', 'the', 'path', 'to', 'my', 'file.ext']), $p->segments());
		$this->assertEquals(7, $p->segmentCount());
		$this->assertEquals('is', $p->segment(1));
	}
}
