<?php
namespace phootwork\file\tests;

use phootwork\file\Directory;
use phootwork\file\File;
use phootwork\file\Path;

class FileTest extends FilesystemTest {

	public function testReadWrite() {
		$json = '{"hello":"world!"}';
		$file = new File($this->root->url() . '/dir/composer.json');
		$file->write($json);

		$this->assertEquals($json, $file->read());
	}
	
	/**
	 * @expectedException phootwork\file\exception\FileException
	 */
	public function testContentsFromNonExistingFile() {
		$file = new File($this->root->url() . '/composer.json');
		$file->read();
	}
	
	public function testMove() {
		$file = new File($this->root->url() . '/dir/composer.json');
		$file->write('{}');
		$file->move($this->root->url() . '/composer.json');
		
		$this->assertTrue(file_exists($this->root->url() . '/composer.json'));
		$this->assertFalse(file_exists($this->root->url() . '/dir/composer.json'));
	}
	
	/**
	 * @expectedException phootwork\file\exception\FileException
	 */
	public function testMoveWithFailure() {
		$dir = new Directory($this->root->url() . '/dir');
		$dir->make(0555);
		$file = new File($this->root->url() . '/composer.json');
		$file->write('{}');
		$file->move(new Path($this->root->url() . '/dir/composer.json'));
	}
	
	public function testCopy() {
		$file = new File($this->root->url() . '/dir/composer.json');
		$file->write('{}');
		$file->copy($this->root->url() . '/composer.json');
	
		$this->assertTrue(file_exists($this->root->url() . '/composer.json'));
		$this->assertTrue(file_exists($this->root->url() . '/dir/composer.json'));
		
		$a = new File($this->root->url() . '/dir/composer.json');
		$b = new File($this->root->url() . '/composer.json');
		
		$this->assertEquals($a->read(), $b->read());
	}
	
	/**
	 * @expectedException phootwork\file\exception\FileException
	 */
	public function testCopyWithFailure() {
		$dir = new Directory($this->root->url() . '/dir');
		$dir->make(0555);
		$file = new File($this->root->url() . '/composer.json');
		$file->write('{}');
		$file->copy(new Path($this->root->url() . '/dir/composer.json'));
	}
	
	public function testLink() {
		$origin = new Path(tempnam(sys_get_temp_dir(), 'orig'));
		$target = new File(tempnam(sys_get_temp_dir(), 'target'));
		$target->delete();
		$target = new Path($target->getPathname());
		
		$file = new File($origin);
		$file->touch();
		$file->linkTo($target);
		$link = $target->toFileDescriptor();
		
		$this->assertNull($file->getLinkTarget());
		$this->assertTrue($link->exists());
		$this->assertTrue($link->isLink());
		$this->assertTrue($origin->equals($link->getLinkTarget()));
	}
	
	/**
	 * @expectedException phootwork\file\exception\FileException
	 */
	public function testTouchWithFailure() {
		$dir = new Directory($this->root->url() . '/dir');
		$dir->make(0555);
		$file = new File($this->root->url() . '/dir/composer.json');
		$file->touch();
	}
	
	public function testDelete() {
		$dir = new Directory($this->root->url() . '/dir');
		$dir->make();
		$file = new File($this->root->url() . '/dir/composer.json');
		$file->touch();
		
		$this->assertTrue($file->exists());
		$file->delete();
		$this->assertFalse($file->exists());
	}
	
	/**
	 * @expectedException phootwork\file\exception\FileException
	 */
	public function testDeleteWithFailure() {
		$dir = new Directory($this->root->url() . '/dir');
		$dir->make();
		$file = new File($this->root->url() . '/dir/composer.json');
		$file->touch();
		$this->assertTrue($file->exists());
		
		$dir->setMode(0555);
		$file->delete();
		$this->assertFalse($file->exists());
	}
}
