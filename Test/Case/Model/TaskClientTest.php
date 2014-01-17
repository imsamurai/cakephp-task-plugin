<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 12.06.2013
 * Time: 17:27:41
 * Format: http://book.cakephp.org/2.0/en/development/testing.html
 */

/**
 * @package Task.Test.Case.Model
 */
class TaskClientTest extends CakeTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = array(
		'plugin.Task.Task',
		'plugin.Task.DependentTask',
	);

	/**
	 * TaskClient model
	 *
	 * @var TaskClient
	 */
	public $TaskClient = null;

	public function setUp() {
		parent::setUp();
		$this->TaskClient = ClassRegistry::init('Task.TaskClient');
	}

	public function testAdd() {
		$command = 'ls';
		$path = '';
		$arguments = array('-l');
		$task = $this->TaskClient->add($command, $path, $arguments);
		$this->assertTrue((bool)$task);
		$this->assertEqual(1, $task['id']);
		$this->assertEqual($command, $task['command']);
		$this->assertEqual($path, $task['path']);
		$this->assertEqual($arguments, $task['arguments']);
	}

	public function testDependentByHash() {
		$command = 'ls';
		$path = '';
		$arguments = array('-l');
		$task1 = $this->TaskClient->add($command, $path, $arguments);

		$task1WithDependends = $this->TaskClient->find('first', array(
			'conditions' => array(
				'id' => $task1['id']
			),
			'contain' => array('DependsOnTask')
		));

		$this->assertEmpty($task1WithDependends['DependsOnTask']);

		$task2 = $this->TaskClient->add($command, $path, $arguments);

		$task2WithDependends = $this->TaskClient->find('first', array(
			'conditions' => array(
				'id' => $task2['id']
			),
			'contain' => array('DependsOnTask')
		));

		$this->assertSame($task1['hash'], $task2['hash']);
		$this->assertNotEmpty($task2WithDependends['DependsOnTask']);
		$this->assertEqual($task2WithDependends['DependsOnTask'][0]['id'], $task1['id']);
	}

	public function testDependentById() {
		$command = 'ls';
		$path = '';
		$arguments = array('-l');
		$arguments2 = array('-la');
		$task1 = $this->TaskClient->add($command, $path, $arguments);

		$task2 = $this->TaskClient->add($command, $path, $arguments2, array('dependsOn' => array($task1['id'])));

		$task2WithDependends = $this->TaskClient->find('first', array(
			'conditions' => array(
				'id' => $task2['id']
			),
			'contain' => array('DependsOnTask')
		));

		$this->assertNotEquals($task1['hash'], $task2['hash']);
		$this->assertNotEmpty($task2WithDependends['DependsOnTask']);
		$this->assertEqual($task2WithDependends['DependsOnTask'][0]['id'], $task1['id']);
	}

}
