<?php

/**
 * Copyright (c) 2016.  Profenter Systems <service@profenter.de>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
class directoryTest extends PHPUnit_Framework_TestCase {
	/**
	 * @var \profenter\tools\directory
	 */
	protected $directory;

	/**
	 * set up test class
	 */
	public function setUp() {
		$this->directory = \profenter\tools\directory::init( getcwd() . "/.." );
	}

	/**
	 * checks  ->find()
	 *
	 * @throws \profenter\exceptions\FileNotFoundException
	 */
	public function testFindArray() {
		$test = $this->directory->addIgnore( [ ".git", ".gitignore", ".idea" ] )
		                        ->setRoot( getcwd() . "/.." )
		                        ->get()
		                        ->cd( "/" )
		                        ->find( "/lib/*/cache/*.json" )
		                        ->asArray();
		$this->assertInternalType( 'array', $test );
		$expected = json_decode( '{"lib":{"tools":{".profenter":{"cache":{"config.json":"\/lib\/tools\/.profenter\/cache\/config.json"}}}}}', true );
		$this->assertEquals( $expected, $test );
	}

	/**
	 * checks  ->cd()
	 *
	 * @throws \profenter\exceptions\FileNotFoundException
	 */
	public function testCDArray() {
		$test = $this->directory->addIgnore( [ ".git", ".gitignore", ".idea" ] )
		                        ->setRoot( getcwd() . "/.." )
		                        ->get()
		                        ->cd( "/lib/" )
		                        ->find( "*.json" )
		                        ->asArray();
		$this->assertInternalType( 'array', $test );
		$expected = json_decode( '{"tools":{".profenter":{"files.json":"\/lib\/tools\/.profenter\/files.json","cache":{"config.json":"\/lib\/tools\/.profenter\/cache\/config.json"}}}}', true );
		$this->assertEquals( $expected, $test );
	}

	/**
	 * checks  ->find('../')
	 *
	 * @throws \profenter\exceptions\FileNotFoundException
	 */
	public function testCDArray2() {
		$test = \profenter\tools\directory::init( getcwd() )
		                                  ->addIgnore( [ ".git", ".gitignore", ".idea" ] )
		                                  ->setRoot( getcwd() . "/.." )
		                                  ->get()
		                                  ->cd( "../" )
		                                  ->find( "config.json" )
		                                  ->asArray();
		$this->assertInternalType( 'array', $test );
		$expected = json_decode( '{"lib":{"tools":{".profenter":{"cache":{"config.json":"\/lib\/tools\/.profenter\/cache\/config.json"}}}}}', true );
		$this->assertEquals( $expected, $test );
	}
}