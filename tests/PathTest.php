<?php
namespace phootwork\file\tests;

use phootwork\file\Path;
use phootwork\lang\ArrayObject;

class PathTest extends \PHPUnit_Framework_TestCase {

	public function testBasicNaming() {
		$p = new Path('this/is/the/path/to/my/file.ext');
		
		$this->assertEquals('this/is/the/path/to/my', $p->getDirname());
		$this->assertEquals('file.ext', $p->getFilename());
		$this->assertEquals('ext', $p->getExtension());
		$this->assertEquals('this/is/the/path/to/my/file.ext', $p->getPathname());
		
		$p = new Path('another/path');
		$this->assertEmpty($p->getExtension());
		$p = $p->append('to');
		$this->assertEquals('another/path/to', $p->getPathname());
		$p = $p->append(new Path('my/stuff'));
		$this->assertEquals('another/path/to/my/stuff', $p->getPathname());		
	}
	
	public function testExtension() {
		$p = new Path('my/file.ext');
		$this->assertEquals('ext', $p->getExtension());
		$this->assertEquals('bla', $p->setExtension('bla')->getExtension());
		$this->assertEmpty($p->removeExtension()->getExtension());
	}

	public function testSegments() {
		$p = new Path('this/is/the/path/to/my/file.ext');
		
		$this->assertEquals(new ArrayObject(['this', 'is', 'the', 'path', 'to', 'my', 'file.ext']), $p->segments());
		$this->assertEquals(7, $p->segmentCount());
		$this->assertNull($p->segment(-1));
		$this->assertEquals('is', $p->segment(1));
		$this->assertEquals('file.ext', $p->lastSegment());
		$this->assertEquals('this/is/the', $p->upToSegment(3)->toString());
		$this->assertEquals('the/path/to/my/file.ext', $p->removeFirstSegments(2)->toString());
		$this->assertEquals('this/is/the/path/to', $p->removeLastSegments(2)->toString());
		$this->assertEquals('file.ext', $p->lastSegment());
		$this->assertEquals('', $p->upToSegment(0)->toString());
	}
	
	public function testTrailingSlash() {
		$p = new Path('stairway/to/hell');
		
		$this->assertFalse($p->hasTrailingSeparator());
		$p->addTrailingSeparator();
		$this->assertTrue($p->hasTrailingSeparator());
		$p->removeTrailingSeparator();
		$this->assertFalse($p->hasTrailingSeparator());
	}
	
	public function testMatching() {
		$base = new Path('this/is/the/path/to/my/file.ext');
		$prefix = new Path('this/is/the');
		$anotherPath = new Path('this/is/another/path');
		
		$this->assertTrue($prefix->isPrefixOf($base));
		
		$this->assertEquals(3, $base->matchingFirstSegments($prefix));
		$this->assertEquals(2, $base->matchingFirstSegments($anotherPath));
		$this->assertEquals('/path/to/my/file.ext', $base->makeRelativeTo($prefix)->toString());
	}

	public function testAbsolute() {
		$win = new Path('c:\\\\windows');
		$this->assertTrue($win->isAbsolute());
		
		$unix = new Path('/etc');
		$this->assertTrue($unix->isAbsolute());
		
		$null = new Path('');
		$this->assertFalse($null->isAbsolute());
		
		$current = new Path('./some/dir');
		$this->assertFalse($current->isAbsolute());
		
		$abs = new Path(__FILE__);
		$this->assertTrue($abs->isAbsolute());
	}
	
	public function testEquals() {
		$current = new Path(__FILE__);
		$cwd = new Path(getcwd());
		$relative = new Path('.'.$current->makeRelativeTo($cwd));
		
		$this->assertTrue($current->equals($relative));
		
		// with virtual path
		$current = new Path('vfs://root/dir/file.ext');
		$relative = new Path('vfs://root/file.ext');
		$this->assertFalse($current->equals($relative));
		$this->assertFalse($current->equals($cwd));
	}

}
