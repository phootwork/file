<?php
namespace phootwork\file\tests;

use phootwork\file\Directory;
use phootwork\file\FileDescriptor;
use phootwork\lang\ArrayObject;

class DirectoryTest extends FilesystemTest {

	public function testCreateDirectory() {
		$dir = new Directory($this->root->url() . '/prj');
		$this->assertFalse($dir->exists());
		
		$dir->make();
		$this->assertTrue($dir->exists());
	}
	
	/**
	 * @expectedException phootwork\file\exception\FileException
	 */
	public function testCreateDirectoryWithFailure() {
		$root = new Directory($this->root->url());
		$root->setMode(0555);
		
		$dir = new Directory($this->root->url() . '/prj');
		$dir->make();
	}
	
	public function testIterator() {
		$dir = new Directory($this->root->url() . '/prj');
		$dir->make();
		$path = $dir->toPath();
		$composer = $path->append('composer.json');
		$file = $composer->toFileDescriptor()->toFile();
		$file->write('{}');
		
		$vendor = $path->append('vendor');
		$folder = $vendor->toFileDescriptor()->toDirectory();
		$folder->make();
		
		$arr = new ArrayObject();
		foreach ($dir as $k => $file) {
			if (!$file->isDot()) {
				$this->assertTrue($file instanceof FileDescriptor);
				$arr[$k] = $file->getFilename();
				
				if ($file->isFile()) {
					$this->assertEquals('composer.json', $file->getFilename());
				}
				
				if ($file->isDir()) {
					$this->assertEquals('vendor', $file->getFilename());
				}
			}
		}
		
		$this->assertEquals(['composer.json', 'vendor'], $arr->sort()->toArray());
	}
	
	public function testDelete() {
		$prj = new Directory($this->createProject());
		$prj->delete();
		
		$this->assertFalse($prj->exists());
	}
	
	/**
	 * @expectedException phootwork\file\exception\FileException
	 */
	public function testDeleteWithFailure() {
		$prj = new Directory($this->createProject());
		$root = new Directory($this->root->url());
		$root->setMode(0555);
		$prj->delete();
	}
}
