<?php
namespace phootwork\file\tests;

use org\bovigo\vfs\vfsStream;
use phootwork\file\Directory;

class DirectoryTest extends \PHPUnit_Framework_TestCase {

	private $root;
	
	public function setUp() {
		$this->root = vfsStream::setup();
	}
	
	public function testCreateDirectory() {
		$dir = new Directory($this->root->url() . '/prj');
		$this->assertFalse($dir->exists());
		
		$dir->create();
		$this->assertTrue($dir->exists());
	}
}
