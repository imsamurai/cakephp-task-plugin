<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 12.06.2013
 * Time: 17:27:41
 * Format: http://book.cakephp.org/2.0/en/development/testing.html
 */

/**
 * Task client test
 * 
 * @package TaskTest
 * @subpackage Model
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

	/**
	 * {@inheritdoc}
	 */
	public function setUp() {
		parent::setUp();
		$this->TaskClient = ClassRegistry::init('Task.TaskClient');
	}

	/**
	 * Test add task
	 */
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

	/**
	 * Test get dependent task by hash
	 */
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

		$this->assertFalse($this->TaskClient->add($command, $path, $arguments, array('unique' => true)));
	}

	/**
	 * Test get dependent task by id
	 */
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

	/**
	 * Test task stopping
	 * 
	 * @param array $task
	 * @param int $taskId
	 * @param bool $result
	 * @param string $status
	 * @param string $exception
	 * @dataProvider stopProvider
	 */
	public function testStop(array $task, $taskId, $result, $status, $exception) {
		if ($exception) {
			$this->expectException($exception);
		}
		if ($task) {
			$this->assertTrue((bool)$this->TaskClient->save($task), 'Can\'t save task for test');
		}

		$this->assertSame($result, $this->TaskClient->stop($taskId, 1));
		$updatedTask = $this->TaskClient->read(null, $taskId)[$this->TaskClient->alias];
		$this->assertSame($status, (int)$updatedTask['status']);
	}

	/**
	 * Data provider for testStop
	 * 
	 * @return array
	 */
	public function stopProvider() {
		return array(
			//set #0
			array(
				//task
				array(),
				//taskId
				10001,
				//result
				null,
				//status
				null,
				//exception
				'NotFoundException'
			),
			//set #1
			array(
				//task
				array(
					'id' => 10001,
					'status' => TaskType::FINISHED
				),
				//taskId
				10001,
				//result
				true,
				//status
				TaskType::FINISHED,
				//exception
				null
			),
			//set #2
			array(
				//task
				array(
					'id' => 10001,
					'status' => TaskType::STOPPING
				),
				//taskId
				10001,
				//result
				true,
				//status
				TaskType::STOPPING,
				//exception
				null
			),
			//set #3
			array(
				//task
				array(
					'id' => 10001,
					'status' => TaskType::STOPPED
				),
				//taskId
				10001,
				//result
				true,
				//status
				TaskType::STOPPED,
				//exception
				null
			),
			//set #4
			array(
				//task
				array(
					'id' => 10001,
					'status' => TaskType::UNSTARTED
				),
				//taskId
				10001,
				//result
				true,
				//status
				TaskType::STOPPED,
				//exception
				null
			),
			//set #5
			array(
				//task
				array(
					'id' => 10001,
					'status' => TaskType::RUNNING
				),
				//taskId
				10001,
				//result
				true,
				//status
				TaskType::STOPPING,
				//exception
				null
			),
			//set #6
			array(
				//task
				array(
					'id' => 10001,
					'status' => TaskType::DEFFERED
				),
				//taskId
				10001,
				//result
				true,
				//status
				TaskType::STOPPED,
				//exception
				null
			),
			//set #7
			array(
				//task
				array(
					'id' => 10001,
					'status' => 22
				),
				//taskId
				10001,
				//result
				false,
				//status
				22,
				//exception
				null
			),
		);
	}

	/**
	 * Test task restarting
	 * 
	 * @param array $task
	 * @param int $taskId
	 * @param array $newTask
	 * @param string $exception
	 * @dataProvider restartProvider
	 */
	public function testRestart(array $task, $taskId, array $newTask, $exception) {
		if ($exception) {
			$this->expectException($exception);
		}
		if ($task) {
			$this->assertTrue((bool)$this->TaskClient->save($task), 'Can\'t save task for test');
		}

		$newTaskId = $this->TaskClient->restart($taskId);
		if (!$newTask) {
			$this->assertFalse($newTaskId);
		} else {
			$this->assertNotEquals($taskId, $newTask);
			$this->assertEqual($newTask, $this->TaskClient->find('first', array(
						'fields' => array(
							'id',
							'process_id',
							'server_id',
							'command',
							'path',
							'arguments',
							'hash',
							'status',
							'code',
							'code_string',
							'stdout',
							'stderr',
							'details',
							'timeout',
							'scheduled',
							'started',
							'stopped',
							'errored',
							'runtime',
							'waittime'
						),
						'conditions' => array(
							'id' => $newTaskId,
						),
						'contain' => array(
							'DependsOnTask' => array(
								'fields' => array(
									'id',
									'process_id',
									'server_id',
									'command',
									'path',
									'arguments',
									'hash',
									'status',
									'code',
									'code_string',
									'stdout',
									'stderr',
									'details',
									'timeout',
									'scheduled'
								)
							)
						)
			)));
		}
	}

	/**
	 * Data provider for testRestart
	 * 
	 * @return array
	 */
	public function restartProvider() {
		return array(
			//set #0
			array(
				//task
				array(),
				//taskId
				10001,
				//newTask
				array(),
				//exception
				'NotFoundException'
			),
			//set #1
			array(
				//task
				array(
					'id' => 10001,
					'status' => TaskType::FINISHED,
					'stderr' => 'dsfds',
					'stdout' => 'dsfgsdgfgdb',
					'code' => '2',
					'code_string' => 'dfsgdsf',
					'server_id' => '21341342',
					'started' => '2014-01-01 12:34:09',
					'stopped' => '2014-01-01 14:34:09'
				),
				//taskId
				10001,
				//newTask
				array(
					'TaskClient' => array(
						'id' => '10002',
						'process_id' => '0',
						'server_id' => '0',
						'command' => '',
						'path' => '',
						'arguments' => false,
						'hash' => '',
						'status' => '0',
						'code' => null,
						'code_string' => '',
						'stdout' => '',
						'stderr' => '',
						'details' => false,
						'timeout' => '0',
						'scheduled' => null,
						'started' => null,
						'stopped' => null,
						'errored' => '0',
						'runtime' => '0',
						'waittime' => null
					),
					'DependsOnTask' => array(
						(int)0 => array(
							'id' => '10001',
							'process_id' => '0',
							'server_id' => '21341342',
							'command' => '',
							'path' => '',
							'arguments' => '',
							'hash' => '',
							'status' => '3',
							'code' => '2',
							'code_string' => 'dfsgdsf',
							'stdout' => 'dsfgsdgfgdb',
							'stderr' => 'dsfds',
							'details' => '',
							'timeout' => '0',
							'scheduled' => null
						)
					)
				),
				//exception
				null
			),
			//set #2
			array(
				//task
				array(
					'id' => 10001,
					'status' => TaskType::FINISHED,
					'stderr' => '',
					'stdout' => 'dsfgsdgfgdb',
					'code' => '2',
					'code_string' => 'dfsgdsf',
					'server_id' => '21341342',
					'started' => '2014-01-01 12:34:09',
					'stopped' => '2014-01-01 14:34:09'
				),
				//taskId
				10001,
				//newTask
				array(
					'TaskClient' => array(
						'id' => '10002',
						'process_id' => '0',
						'server_id' => '0',
						'command' => '',
						'path' => '',
						'arguments' => false,
						'hash' => '',
						'status' => '0',
						'code' => null,
						'code_string' => '',
						'stdout' => '',
						'stderr' => '',
						'details' => false,
						'timeout' => '0',
						'scheduled' => null,
						'started' => null,
						'stopped' => null,
						'errored' => '0',
						'runtime' => '0',
						'waittime' => null
					),
					'DependsOnTask' => array(
						(int)0 => array(
							'id' => '10001',
							'process_id' => '0',
							'server_id' => '21341342',
							'command' => '',
							'path' => '',
							'arguments' => '',
							'hash' => '',
							'status' => '3',
							'code' => '2',
							'code_string' => 'dfsgdsf',
							'stdout' => 'dsfgsdgfgdb',
							'stderr' => '',
							'details' => '',
							'timeout' => '0',
							'scheduled' => null
						)
					)
				),
				//exception
				null
			),
			//set #3
			array(
				//task
				array(
					'id' => 10001,
					'status' => 22
				),
				//taskId
				10001,
				//newTask
				array(),
				//exception
				null
			),
		);
	}

	/**
	 * Test remove task
	 * 
	 * @param array $task
	 * @param int $taskId
	 * @param string $exception
	 * @param bool $result
	 * @dataProvider removeProvider
	 */
	public function testRemove(array $task, $taskId, $exception, $result) {
		if ($exception) {
			$this->expectException($exception);
		}
		if ($task) {
			$this->assertTrue((bool)$this->TaskClient->save($task), 'Can\'t save task for test');
		}
		
		$this->assertSame($result, $this->TaskClient->remove($taskId));
	}
	
	/**
	 * Data provider for testRemove
	 * 
	 * @return array
	 */
	public function removeProvider() {
		return array(
			//set #0
			array(
				//task
				array(),
				//taskId
				10001,
				//exception
				'NotFoundException',
				//result
				null
			),
			//set #1
			array(
				//task
				array(
					'id' => 10001,
					'status' => 22
				),
				//taskId
				10001,
				//exception
				'',
				//result
				false
			),
			//set #2
			array(
				//task
				array(
					'id' => 10001,
					'status' => TaskType::STOPPED
				),
				//taskId
				10001,
				//exception
				'',
				//result
				true
			),
			//set #3
			array(
				//task
				array(
					'id' => 10001,
					'status' => TaskType::FINISHED
				),
				//taskId
				10001,
				//exception
				'',
				//result
				true
			),
			//set #4
			array(
				//task
				array(
					'id' => 10001,
					'status' => TaskType::UNSTARTED
				),
				//taskId
				10001,
				//exception
				'',
				//result
				true
			),
			
		);
	}

}
