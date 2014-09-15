<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 08.09.2014
 * Time: 16:07:55
 * Format: http://book.cakephp.org/2.0/en/development/testing.html
 */
App::uses('TaskServerTask', 'Task.Console/Command/Task');
App::uses('TaskServer', 'Task.Model');
App::uses('TaskClient', 'Task.Model');
App::uses('CakeEventListener', 'Event');
App::uses('Shell', 'Console');

/**
 * TaskServerTaskTest
 * 
 * @package TaskTest
 * @subpackage Console.Command.Task
 */
class TaskServerTaskTest extends CakeTestCase {

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
	 * {@inheritdoc}
	 */
	public function setUp() {
		parent::setUp();
		TaskServerTaskTestEventModel1::$calls = array();
	}
	
	/**
	 * Test option parser
	 */
	public function testGetOptionParser() {
		$Task = new TaskServerTask;
		$Parser = $Task->getOptionParser();
		$this->assertSame('Task server', $Parser->description());
	}

	/**
	 * Test task execution
	 * 
	 * @param array $freeSlots
	 * @param array $getPending
	 * @param array $processEvents
	 * @dataProvider executeProvider
	 */
	public function testExecute(array $freeSlots, array $getPending, array $processEvents) {
		$tasksCount = count($getPending);
		$slotsCount = count($freeSlots);
		$ProcessManager = $this->getMockBuilder('ProcessManager')
				->disableAutoload()
				->setMethods(array(
					'fork'
				))
				->getMock();
		
		$ProcessManager->expects($this->exactly(count($getPending)))->method('fork')
				->willReturnCallback(function($callable) {
					$callable();
				});
				
		$Task = $this->getMockBuilder('TaskServerTask')
				->setMethods(array(
					'_makeRunner'
				))
				->getMock();

		$Task->ProcessManager = $ProcessManager;
		if ($tasksCount > 0) {
			for ($number = 0; $number < $tasksCount; $number++) {
				$Task->expects($this->at($number))->method('_makeRunner')->with($getPending[$number])
						->willReturnCallback($this->_makeRunner($processEvents));
			}
		} else {
			$Task->expects($this->never())->method('_run');
		}

		$Task->TaskClient = ClassRegistry::init('TaskClient');
		$Task->TaskServer = $this->getMockBuilder('TaskServer')
				->setConstructorArgs(array(false, null, 'test'))
				->setMethods(array(
					'freeSlots',
					'getPending',
					'killZombies'
				))
				->getMock();
		
		$Task->TaskServer->expects($this->at(0))->method('killZombies');
		
		$at = 1;
		for ($number = 0; $number < $slotsCount; $number++) {
			$Task->TaskServer->expects($this->at($at++))->method('freeSlots')->willReturn($freeSlots[$number]);
			if (isset($getPending[$number])) {
				$Task->TaskServer->expects($this->at($at++))->method('getPending')->willReturn($getPending[$number]);
			}
		}

		Configure::write('Task.processEvents', $processEvents);

		$Task->execute();
		foreach ($processEvents as $processEvent) {
			$this->assertNotEmpty(TaskServerTaskTestEventModel1::$calls[$processEvent['key']]);
			$this->assertCount(count($getPending), TaskServerTaskTestEventModel1::$calls[$processEvent['key']]);
		}
	}

	/**
	 * Data provider for testExecute
	 * 
	 * @return array
	 */
	public function executeProvider() {
		return array(
			//set #0
			array(
				//freeSlots
				array(0),
				//$getPending
				array(),
				//processEvents
				array()
			),
			//set #1
			array(
				//freeSlots
				array(1, 0),
				//$getPending
				array(
					array('id' => 1)
				),
				//processEvents
				array()
			),
			//set #2
			array(
				//freeSlots
				array(1, 0),
				//$getPending
				array(
					array('id' => 1)
				),
				//processEvents
				array(
					array(
						'key' => 'taskServerTaskTestEvent1',
						'model' => 'TaskServerTaskTestEventModel1',
						'options' => array()
					)
				)
			),
			//set #3
			array(
				//freeSlots
				array(3, 2, 1, 0),
				//$getPending
				array(
					array('id' => 3),
					array('id' => 2),
					array('id' => 1),
				),
				//processEvents
				array(
					array(
						'key' => 'taskServerTaskTestEvent1',
						'model' => 'TaskServerTaskTestEventModel1',
						'options' => array()
					)
				)
			),
		);
	}

	/**
	 * Tets helper. Makes task runner mock
	 * 
	 * @param array $processEvents
	 * @return object
	 */
	protected function _makeRunner(array $processEvents) {
		return function() use ($processEvents) {
			$Runner = $this->getMockBuilder('TaskRunner')
					->setMethods(array(
						'start'
					))
					->disableOriginalConstructor()
					->getMock();
			$Runner->expects($this->once())->method('start')->willReturnCallback(function() use ($processEvents) {
				foreach ($processEvents as $processEvent) {
					CakeEventManager::instance()->dispatch(new CakeEvent($processEvent['key'], $this, array()));
				}
			});
			return $Runner;
		};
	}

}

/**
 * TaskServerTaskTestEventModel1
 * 
 * @package TaskTest
 * @subpackage Model
 */
class TaskServerTaskTestEventModel1 implements CakeEventListener {

	/**
	 * Method calls by test
	 *
	 * @var array
	 */
	public static $calls = array();

	/**
	 * {@inheritdoc}
	 * 
	 * @return array
	 */
	public function implementedEvents() {
		return array(
			'taskServerTaskTestEvent1' => array('callable' => 'taskServerTaskTestEvent1', 'passParams' => true)
		);
	}

	/**
	 * Test event methods
	 * 
	 * @param string $name
	 * @param array $arguments
	 */
	public function __call($name, $arguments) {
		static::$calls[$name][] = $arguments;
	}

}
