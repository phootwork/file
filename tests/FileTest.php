<?php
namespace phootwork\file\tests;

use org\bovigo\vfs\vfsStream;
use phootwork\file\File;

class FileTest extends \PHPUnit_Framework_TestCase {

	private $root;
	
	public function setUp() {
		$this->root = vfsStream::setup();
	}
	
	
	public function testCreateFile() {
		$file = new File($this->root->url() . '/composer.json');
		$file->setContents('{"hello":"world!"}');
	}
}
