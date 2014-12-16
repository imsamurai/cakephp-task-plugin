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

	/**
	 * Test kill zombie
	 * 
	 * @param array $tasks
	 * @param array $zombieId
	 * @dataProvider killZombiesProvider
	 */
	public function testKillZombies(array $tasks, array $zombieId) {
		Configure::write('Task.zombieTimeout', 900 * 60 * 24);
		$this->TaskServer->deleteAll(array(1 => 1));
		$this->TaskServer->saveAll($tasks);
		$this->TaskServer->killZombies();
		$this->assertSame(count($zombieId), $this->TaskServer->find('count', array(
					'conditions' => array(
						'code_string' => 'zombie',
						'status' => TaskType::STOPPED,
						'code' => 1
					)
		)));
		$this->assertSame(count($zombieId), $this->TaskServer->find('count', array(
					'conditions' => array(
						'code_string' => 'zombie',
						'status' => TaskType::STOPPED,
						'code' => 1,
						'id' => $zombieId
					)
		)));
	}

	/**
	 * Data provider for testKillZombies
	 * 
	 * @return array
	 */
	public function killZombiesProvider() {
		return array(
			//set #0
			array(
				//tasks
				array(),
				//zombieId
				array()
			),
			//set #1
			array(
				//tasks
				array(
					array(
						'id' => 1,
						'status' => TaskType::STOPPING,
						'modified' => (new DateTime('now -1000 days'))->format('Y-m-d H:i:s'),
						'process_id' => 123
					)
				),
				//zombieId
				array(1)
			),
			//set #2
			array(
				//tasks
				array(
					array(
						'id' => 1,
						'status' => TaskType::STOPPING,
						'modified' => (new DateTime('now -1000 days'))->format('Y-m-d H:i:s'),
						'process_id' => 123
					),
					array(
						'id' => 2,
						'status' => TaskType::STOPPING,
						'modified' => (new DateTime('now -5 days'))->format('Y-m-d H:i:s'),
						'process_id' => 456
					),
					array(
						'id' => 3,
						'status' => TaskType::RUNNING,
						'modified' => (new DateTime('now -50 days'))->format('Y-m-d H:i:s'),
						'process_id' => 789
					),
				),
				//zombieId
				array(1)
			),
			//set #3
			array(
				//tasks
				array(
					array(
						'id' => 1,
						'status' => TaskType::STOPPING,
						'modified' => (new DateTime('now -1000 days'))->format('Y-m-d H:i:s'),
						'process_id' => 123
					),
					array(
						'id' => 2,
						'status' => TaskType::STOPPING,
						'modified' => (new DateTime('now -5 days'))->format('Y-m-d H:i:s'),
						'process_id' => 456
					),
					array(
						'id' => 3,
						'status' => TaskType::RUNNING,
						'modified' => (new DateTime('now -50 days'))->format('Y-m-d H:i:s'),
						'timeout' => 1000 * 60 * 60 * 24,
						'started' => (new DateTime('now -2000 days'))->format('Y-m-d H:i:s'),
						'process_id' => 789
					),
					array(
						'id' => 4,
						'status' => TaskType::RUNNING,
						'modified' => (new DateTime('now -50 days'))->format('Y-m-d H:i:s'),
						'timeout' => 2000 * 60 * 60 * 24,
						'started' => (new DateTime('now -1000 days'))->format('Y-m-d H:i:s'),
						'process_id' => 111
					),
				),
				//zombieId
				array(1, 3)
			),
			//set #4
			array(
				//tasks
				array(
					array(
						'id' => 1,
						'status' => TaskType::RUNNING,
						'process_id' => false,
						'modified' => (new DateTime('now -1000 days'))->format('Y-m-d H:i:s')
					),
					array(
						'id' => 2,
						'status' => TaskType::RUNNING,
						'process_id' => 0,
						'modified' => (new DateTime('now -1000 days'))->format('Y-m-d H:i:s')
					),
					array(
						'id' => 3,
						'status' => TaskType::RUNNING,
						'process_id' => '0',
						'modified' => (new DateTime('now -1000 days'))->format('Y-m-d H:i:s')
					),
					array(
						'id' => 4,
						'status' => TaskType::RUNNING,
						'process_id' => '123',
						'modified' => (new DateTime('now -1000 days'))->format('Y-m-d H:i:s')
					),
					array(
						'id' => 5,
						'status' => TaskType::RUNNING,
						'process_id' => 123,
						'modified' => (new DateTime('now -1000 days'))->format('Y-m-d H:i:s')
					),
				),
				//zombieId
				array(1, 2, 3)
			),
		);
	}
	
	/**
	 * Test virtual fields
	 * 
	 * @param array $task
	 * @param array $fieldValues
	 * @dataProvider virtualFieldsProvider
	 */
	public function testVirtualFields(array $task, array $fieldValues) {
		$this->TaskServer->deleteAll(array(1 => 1));
		foreach ($task as $field => &$value) {
			if (!in_array($field, array('stderr'), true)) {
				$value = (new DateTime($value))->format('Y-m-d H:i:s');
			}
		}
		$this->TaskServer->save($task);
		$taskData = $this->TaskServer->read();
		foreach ($fieldValues as $field => $value) {
			$this->assertEquals($value, (int)$taskData[$this->TaskServer->alias][$field], $field, (int)($value / 10));
		}
	}

	/**
	 * Data provider for testVirtualFields 
	 * 
	 * @return array
	 */
	public function virtualFieldsProvider() {
		return array(
			//set #0
			array(
				//task
				array(
					'stderr' => '',
					'created' => '2014-01-01 00:00:00',
					'modified' => 'now -1500000 seconds',
					'started' => '2014-01-01 00:00:10',
					'stopped' => '2014-01-01 00:00:40',
				),
				//fieldValues
				array(
					'errored' => 0,
					'runtime' => 30,
					'waittime' => 10,
					'modified_since' => 1500000,
				)
			),
			//set #1
			array(
				//task
				array(
					'stderr' => 'error',
					'created' => '2014-01-01 00:00:00',
					'modified' => 'now -90 days',
					'started' => '2014-01-01 00:00:10',
					'stopped' => '2014-01-01 00:00:40',
				),
				//fieldValues
				array(
					'errored' => 1,
					'runtime' => 30,
					'waittime' => 10,
					'modified_since' => 7776000,
				)
			),
			//set #2
			array(
				//task
				array(
					'created' => '2013-10-01 00:00:10',
					'started' => '2014-01-01 00:00:10',
					'stopped' => '2014-04-01 00:00:10'
				),
				//fieldValues
				array(
					'runtime' => 7776000,
					'waittime' => 7948800
				)
			),
			//set #4
			array(
				//task
				array(
					'started' => 'now -90 days',
				),
				//fieldValues
				array(
					'runtime' => 7776000,
				)
			),
		);
	}

}
