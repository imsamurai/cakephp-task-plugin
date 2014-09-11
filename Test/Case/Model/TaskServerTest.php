<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 12.06.2013
 * Time: 17:27:41
 * Format: http://book.cakephp.org/2.0/en/development/testing.html
 */
App::uses('TaskClient', 'Task.Model');
App::uses('TaskServer', 'Task.Model');

/**
 * Task server test
 * 
 * @package TaskTest
 * @subpackage Model
 */
class TaskServerTest extends CakeTestCase {

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

	/**
	 * TaskServer model
	 *
	 * @var TaskServer
	 */
	public $TaskServer = null;

	/**
	 * {@inheritdoc}
	 */
	public function setUp() {
		parent::setUp();
		$this->TaskClient = new TaskClient(false, null, 'test');
		$this->TaskServer = new TaskServer(false, null, 'test');
	}

	/**
	 * Test get free slots count
	 */
	public function testFreeSlots() {
		$maxSlots = 5;
		Configure::write('Task.maxSlots', $maxSlots);

		$this->TaskClient->add('ls', '', array('-l'));
		$this->assertSame($maxSlots, $this->TaskServer->freeSlots());
		$this->TaskServer->getPending();
		$this->assertSame($maxSlots - 1, $this->TaskServer->freeSlots());
	}

	/**
	 * Test get pending tasks
	 */
	public function testGetPending() {
		$task = $this->TaskClient->add('ls', '', array('-l'));
		$pendedTask = $this->TaskServer->getPending();
		$this->assertEqual($task['status'], TaskType::UNSTARTED);
		$this->assertEqual($pendedTask['status'], TaskType::DEFFERED);
		$this->assertSame($task['id'], $pendedTask['id']);
		$this->assertFalse($this->TaskServer->getPending());
	}

	/**
	 * Test `started` callback
	 */
	public function testStarted() {
		$this->TaskClient->add('ls', '', array('-l'));
		$task = $this->TaskServer->getPending();
		$this->TaskServer->started($task);
		$startedTask = $this->TaskClient->find('first', array('conditions' => array('id' => $task['id'])));
		$this->assertEqual($startedTask['Task']['status'], TaskType::RUNNING);
	}

	/**
	 * Test `stopped` callback
	 */
	public function testStopped() {
		$this->TaskClient->add('ls', '', array('-l'));
		$task = $this->TaskServer->getPending();
		$this->TaskServer->stopped($task, false);
		$startedTask = $this->TaskClient->find('first', array('conditions' => array('id' => $task['id'])));
		$this->assertEqual($startedTask['Task']['status'], TaskType::FINISHED);
	}

	/**
	 * Test `started` manual callback
	 */
	public function testStoppedManual() {
		$this->TaskClient->add('ls', '', array('-l'));
		$task = $this->TaskServer->getPending();
		$this->TaskServer->stopped($task, true);
		$startedTask = $this->TaskClient->find('first', array('conditions' => array('id' => $task['id'])));
		$this->assertEqual($startedTask['Task']['status'], TaskType::STOPPED);
	}

	/**
	 * Test dependent tasks
	 */
	public function testDependent() {
		$task1 = $this->TaskClient->add('ls', '', array('-l'));
		$this->TaskClient->add('ls', '', array('-l'));

		$task2 = $this->TaskClient->add('ls', '', array('-la'));
		$this->TaskClient->add('ls', '', array('-la'));

		$pendedTask1 = $this->TaskServer->getPending();
		$pendedTask2 = $this->TaskServer->getPending();

		$this->assertEqual($pendedTask1['id'], $task1['id']);
		$this->assertEqual($pendedTask2['id'], $task2['id']);
	}

	/**
	 * Tast if task must be stopped
	 * 
	 * @param array $task
	 * @param int $taskId
	 * @param bool $mustStop
	 * @dataProvider mustStopProvider
	 */
	public function testMustStop(array $task, $taskId, $mustStop) {
		$this->TaskClient->save($task);
		$this->assertSame($mustStop, $this->TaskServer->mustStop($taskId));
	}

	/**
	 * Data provider for testMustStop
	 * 
	 * @return array
	 */
	public function mustStopProvider() {
		return array(
			//set #0
			array(
				//task
				array(),
				//taskId
				10001,
				//mustStop
				false
			),
			//set #1
			array(
				//task
				array(
					'id' => 10001,
					'status' => TaskType::STOPPING
				),
				//taskId
				10001,
				//mustStop
				true
			),
			//set #2
			array(
				//task
				array(
					'id' => 10001,
					'status' => TaskType::DEFFERED
				),
				//taskId
				10001,
				//mustStop
				false
			),
		);
	}

}
