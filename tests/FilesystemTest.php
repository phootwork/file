<?php
namespace phootwork\file\tests;

use org\bovigo\vfs\vfsStream;

abstract class FilesystemTest extends \PHPUnit_Framework_TestCase {

	protected $root;
	
	public function setUp() {
		$this->root = vfsStream::setup();
	}
	
	protected function createProject() {
		vfsStream::create([
			'prj' => [
				'composer.json' => '{}',
				'vendor' => [
					'autoload.php' => '// autoload'
				],
				'dir' => []
			]
		]);
		
		return $this->root->url() . '/prj';
	}
}
