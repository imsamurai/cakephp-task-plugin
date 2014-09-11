<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 10.09.2014
 * Time: 13:55:03
 * Format: http://book.cakephp.org/2.0/en/development/testing.html
 */
App::uses('TaskType', 'Task.Lib/Task');

/**
 * TaskTypeTest
 * 
 * @package TaskTest
 * @subpackage Task
 */
class TaskTypeTest extends CakeTestCase {

	/**
	 * {@inheritdoc}
	 */
	public function setUp() {
		parent::setUp();
	}

	/**
	 * Test types
	 */
	public function testType() {
		$this->assertSame(array(
			'UNSTARTED' => (int)0,
			'DEFFERED' => (int)1,
			'RUNNING' => (int)2,
			'FINISHED' => (int)3,
			'STOPPING' => (int)4,
			'STOPPED' => (int)5
				), TaskType::getTypes());
	}

}
