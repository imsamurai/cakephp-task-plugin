<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 09.09.2014
 * Time: 11:46:52
 * Format: http://book.cakephp.org/2.0/en/development/testing.html
 */

/**
 * TaskControllerTest
 * 
 * @package TaskTEst
 * @subpackage Controller
 */
class TaskControllerTest extends ControllerTestCase {

	/**
	 * {@inheritdoc}
	 */
	public function setUp() {
		parent::setUp();
		Configure::write('Pagination.limit', 10);
	}

	/**
	 * Test index action
	 * 
	 * @param array $query
	 * @param array $paginate
	 * @param array $commands
	 * @param array $statuses
	 * @param array $approximateRuntimes
	 * @dataProvider indexProvider
	 */
	public function testIndex(array $query, array $paginate, array $commands, array $statuses, array $approximateRuntimes) {
		$Controller = $this->generate('Task.Task', array(
			'models' => array(
				'Task.TaskClient' => array('find'),
				'Task.TaskProfiler' => array('approximateRuntime'),
			),
			'methods' => array(
				'paginate'
			)
		));

		$Controller->expects($this->once())->method('paginate')->with('TaskClient')->willReturn(array('TaskClient pagination data'));

		$Controller->TaskClient
				->expects($this->at(0))
				->method('find')
				->with('list', array(
					'fields' => array('command', 'command'),
					'group' => array('command'),
				))
				->willReturn($commands);

		$Controller->TaskClient
				->expects($this->at(1))
				->method('find')
				->with('list', array(
					'fields' => array('status', 'status'),
					'group' => array('status'),
				))
				->willReturn($statuses);

		if (!$approximateRuntimes) {
			$Controller->TaskProfiler
					->expects($this->never())
					->method('approximateRuntime');
		} else {
			$at = 0;
			foreach ($approximateRuntimes as $command => $runtime) {
				$Controller->TaskProfiler
						->expects($this->at($at++))
						->method('approximateRuntime')
						->with($command)
						->willReturn($runtime);
			}
		}

		$this->testAction('/task/task/index', array(
			'method' => 'GET',
			'data' => $query
		));

		$this->assertEqual($paginate, $Controller->paginate);
		$this->assertSame($query, $Controller->request->data('Task'));
		$this->assertSame(array('TaskClient pagination data'), $Controller->viewVars['data']);
		$this->assertSame($commands, $Controller->viewVars['commandList']);
		$this->assertSame($statuses, $Controller->viewVars['statusList']);
		$this->assertSame($approximateRuntimes, $Controller->viewVars['approximateRuntimes']);
	}

	/**
	 * Data provider for testIndex
	 * 
	 * @return array
	 */
	public function indexProvider() {
		return array(
			//set #0
			array(
				//query
				array(
					'status' => '1'
				),
				//paginate
				array(
					'TaskClient' => array(
						'limit' => 10,
						'fields' => array(
							'command',
							'arguments',
							'status',
							'code_string',
							'stderr',
							'started',
							'stopped',
							'created',
							'modified',
							'runtime',
							'id',
							'process_id',
						),
						'conditions' => array(
							'status' => '1'
						),
						'order' => array('created' => 'desc'),
						'contain' => array('DependsOnTask' => array('id', 'status'))
					)
				),
				//commands
				array(),
				//statuses
				array(),
				//approximateRuntimes
				array()
			),
			//set #1
			array(
				//query
				array(
					'id' => 11,
					'started' => '01.01.2014 - 02.02.2014',
					'created' => '12:00:01 01.01.2014 to 01:00:10 02.03.2014',
					'stopped' => '01.01.2014 to 02.02.2014',
					'modified' => '2014/01/01 11:05:23 - 2014/02/02 17:05:23',
					'status' => '0'
				),
				//paginate
				array(
					'TaskClient' => array(
						'limit' => 10,
						'fields' => array(
							'command',
							'arguments',
							'status',
							'code_string',
							'stderr',
							'started',
							'stopped',
							'created',
							'modified',
							'runtime',
							'id',
							'process_id',
						),
						'conditions' => array(
							'id' => 11,
							'started BETWEEN ? AND ?' => array('2014-01-01 00:00:00', '2014-02-02 00:00:00'),
							'created BETWEEN ? AND ?' => array('2014-01-01 12:00:01', '2014-03-02 01:00:10'),
							'stopped BETWEEN ? AND ?' => array('2014-01-01 00:00:00', '2014-02-02 00:00:00'),
							'modified BETWEEN ? AND ?' => array('2014-01-01 11:05:23', '2014-02-02 17:05:23'),
							'status' => '0'
						),
						'order' => array('created' => 'desc'),
						'contain' => array('DependsOnTask' => array('id', 'status'))
					)
				),
				//commands
				array(
					'c1' => 'c1', 'c2' => 'c2', 'c3' => 'c3'
				),
				//statuses
				array(
					's1' => 's1', 's2' => 's2', 's3' => 's3'
				),
				//approximateRuntimes
				array(
					'c1' => 1, 'c2' => 20, 'c3' => 300
				)
			),
		);
	}

	/**
	 * Test view action
	 * 
	 * @param int $taskId
	 * @param array $task
	 * @param string $exception
	 * @dataProvider viewProvider
	 */
	public function testView($taskId, array $task, $exception) {
		if ($exception) {
			$this->expectException($exception);
		}

		$Controller = $this->generate('Task.Task', array(
			'models' => array(
				'Task.TaskClient' => array('find'),
				'Task.TaskProfiler' => array('approximateRuntime'),
			)
		));

		$Controller->TaskClient
				->expects($this->once())
				->method('find')
				->with('first', array(
					'conditions' => array(
						'id' => $taskId
					),
					'contain' => array(
						'DependsOnTask' => array(
							'id',
							'status'
						),
						'Statistics'
					)
				))
				->willReturn($task);
		if ($task) {
			$Controller->TaskProfiler
					->expects($this->once())
					->method('approximateRuntime')
					->with($task['TaskClient']['command'])
					->willReturn(5);
		} else {
			$Controller->TaskProfiler->expects($this->never())->method('approximateRuntime');
		}

		$this->testAction('/task/task/view/' . $taskId, array(
			'method' => 'GET'
		));

		if ($task) {
			$this->assertSame(array($task['TaskClient']['command'] => 5), $Controller->viewVars['approximateRuntimes']);
			$this->assertSame($task['TaskClient'], $Controller->viewVars['task']);
			$this->assertSame($task['DependsOnTask'], $Controller->viewVars['dependentTasks']);
		}
	}

	/**
	 * Data source for testView
	 * 
	 * @return array
	 */
	public function viewProvider() {
		return array(
			//set #0
			array(
				//taskId
				1,
				//task
				array(),
				//exception
				'NotFoundException'
			),
			//set #1
			array(
				//taskId
				1,
				//task
				array(
					'TaskClient' => array(
						'command' => 'whoami',
					),
					'DependsOnTask' => array(2, 3, 4)
				),
				//exception
				''
			),
		);
	}

	/**
	 * Test stop action
	 * 
	 * @param id $taskId
	 * @param bool $stopped
	 * @param string $message
	 * @dataProvider stopProvider
	 */
	public function testStop($taskId, $stopped, $message) {
		$Controller = $this->generate('Task.Task', array(
			'models' => array(
				'Task.TaskClient' => array('stop')
			),
			'components' => array(
				'Session' => array('setFlash')
			),
			'methods' => array('redirect', 'referer')
		));

		$Controller->TaskClient
				->expects($this->once())
				->method('stop')
				->with($taskId)
				->willReturn($stopped);

		$Controller->Session
				->expects($this->once())
				->method('setFlash')
				->with($message);

		$Controller
				->expects($this->once())
				->method('referer')
				->willReturn('referer');
		$Controller
				->expects($this->once())
				->method('redirect')
				->with('referer');

		$this->testAction('/task/task/stop/' . $taskId, array(
			'method' => 'GET'
		));
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
				//taskId
				1,
				//stopped
				false,
				//message
				"Can't stop task 1!"
			),
			//set #2
			array(
				//taskId
				2,
				//stopped
				true,
				//message
				"Task 2 will be stopped shortly"
			),
		);
	}

	/**
	 * Test restart action
	 * 
	 * @param id $taskId
	 * @param bool $restarted
	 * @param string $message
	 * @dataProvider restartProvider
	 */
	public function testRestart($taskId, $restarted, $message) {
		$Controller = $this->generate('Task.Task', array(
			'models' => array(
				'Task.TaskClient' => array('restart')
			),
			'components' => array(
				'Session' => array('setFlash')
			),
			'methods' => array('redirect', 'referer')
		));

		$Controller->TaskClient
				->expects($this->once())
				->method('restart')
				->with($taskId)
				->willReturn($restarted);

		$Controller->Session
				->expects($this->once())
				->method('setFlash')
				->with($message);

		$Controller
				->expects($this->once())
				->method('referer')
				->willReturn('referer');
		$Controller
				->expects($this->once())
				->method('redirect')
				->with('referer');

		$this->testAction('/task/task/restart/' . $taskId, array(
			'method' => 'GET'
		));
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
				//taskId
				1,
				//restarted
				false,
				//message
				"Can't restart task 1!"
			),
			//set #2
			array(
				//taskId
				2,
				//restarted
				true,
				//message
				"Task 2 will be restarted shortly"
			),
		);
	}

	/**
	 * Test remove action
	 * 
	 * @param id $taskId
	 * @param bool $removed
	 * @param string $message
	 * @dataProvider removeProvider
	 */
	public function testRemove($taskId, $removed, $message) {
		$Controller = $this->generate('Task.Task', array(
			'models' => array(
				'Task.TaskClient' => array('remove')
			),
			'components' => array(
				'Session' => array('setFlash')
			),
			'methods' => array('redirect', 'referer')
		));

		$Controller->TaskClient
				->expects($this->once())
				->method('remove')
				->with($taskId)
				->willReturn($removed);

		$Controller->Session
				->expects($this->once())
				->method('setFlash')
				->with($message);

		$Controller
				->expects($this->once())
				->method('referer')
				->willReturn('referer');
		$Controller
				->expects($this->once())
				->method('redirect')
				->with('referer');

		$this->testAction('/task/task/remove/' . $taskId, array(
			'method' => 'GET'
		));
	}

	/**
	 * Data provider for testRestart
	 * 
	 * @return array
	 */
	public function removeProvider() {
		return array(
			//set #0
			array(
				//taskId
				1,
				//removed
				false,
				//message
				"Can't delete task 1!"
			),
			//set #2
			array(
				//taskId
				2,
				//removed
				true,
				//message
				"Task 2 deleted"
			),
		);
	}

	/**
	 * Test profile action
	 * 
	 * @param string $command
	 * @param array $commandList
	 * @param mixed $profileData
	 * @dataProvider profileProvider
	 */
	public function testProfile($command, array $commandList, $profileData) {
		$Controller = $this->generate('Task.Task', array(
			'models' => array(
				'Task.TaskClient' => array('find'),
				'Task.TaskProfiler' => array('profileCommand'),
			)
		));

		$Controller->TaskClient
				->expects($this->once())
				->method('find')
				->with('list', array(
					'fields' => array('command', 'command'),
					'group' => array('command'),
				))
				->willReturn($commandList);

		if ($command) {
			$Controller->TaskProfiler
					->expects($this->once())
					->method('profileCommand')
					->with($command)
					->willReturn($profileData);
		} else {
			$Controller->TaskProfiler
					->expects($this->never())
					->method('profileCommand');
		}

		try {
			CakePlugin::load('GoogleChart');
			$googleChartLoaded = true;
		} catch (Exception $E) {
			$googleChartLoaded = false;
		}
		
		$this->testAction('/task/task/profile/', array(
			'method' => 'GET',
			'data' => compact('command')
		));

		$this->assertSame($profileData, $Controller->viewVars['data']);
		$this->assertSame($commandList, $Controller->viewVars['commandList']);
		if ($googleChartLoaded) {
			$this->assertContains('GoogleChart.GoogleChart', $Controller->helpers);
		}
	}

	/**
	 * Data provider for testProfile
	 * 
	 * @return array
	 */
	public function profileProvider() {
		return array(
			//set #0
			array(
				//command
				'run',
				//$commandList
				array('c1', 'c2', 'c3'),
				//profileData
				'data'
			),
			//set #1
			array(
				//command
				'',
				//$commandList
				array('c1', 'c2', 'c3'),
				//profileData
				null
			),
			//set #2
			array(
				//command
				'',
				//$commandList
				array(),
				//profileData
				null
			),
		);
	}

}
