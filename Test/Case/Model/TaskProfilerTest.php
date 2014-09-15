<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 10.09.2014
 * Time: 19:19:12
 * Format: http://book.cakephp.org/2.0/en/development/testing.html
 */

/**
 * TaskProfilerTest
 * 
 * @package TaskTest
 * @subpackage Model
 */
class TaskProfilerTest extends CakeTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = array(
		'plugin.Task.TaskProfiler',
		'plugin.Task.DependentTask',
	);

	/**
	 * Task profiler model
	 *
	 * @var TaskProfiler
	 */
	public $Profiler = null;

	/**
	 * {@inheritdoc}
	 */
	public function setUp() {
		parent::setUp();
		Configure::write('Task.dateDiffFormat', "%a days, %h hours, %i minutes");
		$this->Profiler = ClassRegistry::init('Task.TaskProfiler');
	}

	/**
	 * Test profile command
	 * 
	 * @param string $command
	 * @param array $profile
	 * @param int $profilerLimit
	 * @dataProvider profileCommandProvider
	 */
	public function testProfileCommand($command, $profile, $profilerLimit) {
		Configure::write('Task.profilerLimit', $profilerLimit);
		$this->assertEqual($profile, $this->Profiler->profileCommand($command));
	}

	/**
	 * Data provider for testProfileCommand
	 * 
	 * @return array
	 */
	public function profileCommandProvider() {
		return array(
			//set #0
			array(
				//command
				'',
				//profile,
				null,
				//profilerLimit,
				10
			),
			//set #1
			array(
				//command
				'Console/cake segment generate',
				//profile,
				array(
					'command' => 'Console/cake segment generate',
					'countByStatus' => array(
						TaskType::UNSTARTED => 1,
						TaskType::DEFFERED => 1,
						TaskType::RUNNING => 1,
						TaskType::FINISHED => 1,
						TaskType::STOPPING => 1,
						TaskType::STOPPED => 1
					),
					'errored' => 3,
					'statistics' => array(
						0 => array(
							'id' => '6',
							'started' => '2014-09-07 11:12:03',
							'stopped' => '2014-09-07 11:13:09',
							'created' => '2014-09-06 12:51:30',
							'runtime' => 66,
							'waittime' => 80433,
							'runtimeHuman' => '0 days, 0 hours, 1 minutes',
							'waittimeHuman' => '0 days, 22 hours, 20 minutes',
							'startedTimestamp' => (new DateTime('2014-09-07 11:12:03'))->getTimestamp()
						)
					),
					'runtimeAverage' => 66,
					'runtimeAverageHuman' => '0 days, 0 hours, 1 minutes',
					'runtimeMax' => 66,
					'runtimeMaxHuman' => '0 days, 0 hours, 1 minutes',
					'runtimeMin' => 66,
					'runtimeMinHuman' => '0 days, 0 hours, 1 minutes',
					'waittimeAverage' => 80433,
					'waittimeAverageHuman' => '0 days, 22 hours, 20 minutes',
					'waittimeMax' => 80433,
					'waittimeMaxHuman' => '0 days, 22 hours, 20 minutes',
					'waittimeMin' => 80433,
					'waittimeMinHuman' => '0 days, 22 hours, 20 minutes'
				),
				//profilerLimit,
				1
			),
			//set #2
			array(
				//command
				'Console/cake segment generate',
				//profile,
				array(
					'command' => 'Console/cake segment generate',
					'countByStatus' => array(
						TaskType::UNSTARTED => 1,
						TaskType::DEFFERED => 1,
						TaskType::RUNNING => 1,
						TaskType::FINISHED => 1,
						TaskType::STOPPING => 1,
						TaskType::STOPPED => 1
					),
					'errored' => 3,
					'statistics' => array(
						0 => array(
							'id' => '6',
							'started' => '2014-09-07 11:12:03',
							'stopped' => '2014-09-07 11:13:09',
							'created' => '2014-09-06 12:51:30',
							'runtime' => 66,
							'waittime' => 80433,
							'runtimeHuman' => '0 days, 0 hours, 1 minutes',
							'waittimeHuman' => '0 days, 22 hours, 20 minutes',
							'startedTimestamp' => (new DateTime('2014-09-07 11:12:03'))->getTimestamp()
						),
						1 => array(
							'id' => '4',
							'started' => '2014-09-07 11:11:04',
							'stopped' => '2014-09-07 11:11:54',
							'created' => '2014-09-06 12:51:30',
							'runtime' => 50,
							'waittime' => 80374,
							'runtimeHuman' => '0 days, 0 hours, 0 minutes',
							'waittimeHuman' => '0 days, 22 hours, 19 minutes',
							'startedTimestamp' => (new DateTime('2014-09-07 11:11:04'))->getTimestamp()
						)
					),
					'runtimeAverage' => 58,
					'runtimeAverageHuman' => '0 days, 0 hours, 0 minutes',
					'runtimeMax' => 66,
					'runtimeMaxHuman' => '0 days, 0 hours, 1 minutes',
					'runtimeMin' => 50,
					'runtimeMinHuman' => '0 days, 0 hours, 0 minutes',
					'waittimeAverage' => 80404,
					'waittimeAverageHuman' => '0 days, 22 hours, 20 minutes',
					'waittimeMax' => 80433,
					'waittimeMaxHuman' => '0 days, 22 hours, 20 minutes',
					'waittimeMin' => 80374,
					'waittimeMinHuman' => '0 days, 22 hours, 19 minutes'
				),
				//profilerLimit,
				10
			),
		);
	}

	/**
	 * Test task approximate runtime
	 * 
	 * @param string $command
	 * @param int $approximateRuntime
	 * @param int $approximateLimit
	 * @dataProvider approximateRuntimeProfiler
	 */
	public function testApproximateRuntime($command, $approximateRuntime, $approximateLimit) {
		Configure::write('Task.approximateLimit', $approximateLimit);
		$this->assertSame($approximateRuntime, $this->Profiler->approximateRuntime($command));
	}

	/**
	 * Data provider for testApproximateRuntime
	 * 
	 * @return array
	 */
	public function approximateRuntimeProfiler() {
		return array(
			//set #0
			array(
				//command
				'',
				//approximateRuntime
				0,
				//approximateLimit
				10
			),
			//set #1
			array(
				//command
				'Console/cake clusterization_archive generate',
				//approximateRuntime
				563,
				//approximateLimit
				1
			),
			//set #1
			array(
				//command
				'Console/cake clusterization_archive generate',
				//approximateRuntime
				546,
				//approximateLimit
				10
			),
		);
	}

}
