<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 09.09.2014
 * Time: 11:19:14
 * Format: http://book.cakephp.org/2.0/en/development/testing.html
 */
App::uses('TaskShell', 'Task.Console/Command');

/**
 * TaskShellTest
 */
class TaskShellTest extends CakeTestCase {

	/**
	 * {@inheritdoc}
	 */
	public function setUp() {
		parent::setUp();
	}

	/**
	 * Test option parser
	 */
	public function testGetOptionParser() {
		$Shell = new TaskShell;
		$Shell->loadTasks();
		$Parser = $Shell->getOptionParser();
		$this->assertSame('Task shell', $Parser->description());
		$this->assertSame('Task server', $Shell->Server->getOptionParser()->description());
	}

	/**
	 * Test tasl list
	 */
	public function testTasks() {
		$Shell = new TaskShell;
		$this->assertSame(array(
			'Server' => array(
				'className' => 'Task.TaskServer'
			),
			'TaskServer' => array(
				'className' => 'Task.TaskServerOld'
			)
				), $Shell->tasks);
	}

}
