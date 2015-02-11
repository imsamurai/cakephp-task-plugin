<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 11.09.2014
 * Time: 16:27:39
 * Format: http://book.cakephp.org/2.0/en/development/testing.html
 */
App::uses('Router', 'Routing');
App::uses('View', 'View');
App::uses('TaskHelper', 'Task.View/Helper');

/**
 * TaskHelperTest
 * 
 * @package TaskTest
 * @subpackage View.Helper
 */
class TaskHelperTest extends CakeTestCase {

	/**
	 * {@inheritdoc}
	 */
	public function setUp() {
		parent::setUp();
		Configure::write('App.fullBaseUrl', 'http://example.com');
		Configure::write('Task', array(
			'checkInterval' => 5,
			'stopTimeout' => 5,
			'maxSlots' => 16,
			'timeout' => 60 * 60 * 8,
			'dateFormat' => 'd.m.Y',
			'dateDiffFormat' => "%a days, %h hours, %i minutes",
			'dateDiffBarFormat' => "%ad, %hh, %im, %ss",
			'truncateError' => 200,
			'truncateOutput' => 200,
			'truncateArguments' => 10,
			'truncateCode' => 5,
			'truncateWaitFor' => 1,
			'profilerLimit' => 100,
			'approximateLimit' => 10,
		));
	}

	/**
	 * Test id formatter
	 * 
	 * @param array $task
	 * @param string $result
	 * @param array $settings
	 * @dataProvider idProvider
	 */
	public function testId(array $task, $result, array $settings) {
		$Helper = new TaskHelper(new View, $settings);
		$this->assertStringMatchesFormat($result, $Helper->id($task));
	}

	/**
	 * Data provider for testId
	 * 
	 * @return array
	 */
	public function idProvider() {
		return array(
			//set #0
			array(
				//task
				array('id' => '1'),
				//result
				'1',
				//settings
				array('cli' => true)
			),
			//set #1
			array(
				//task
				array('id' => '1'),
				//result
				'<a href="http://example.com/task%sview/1">1</a>',
				//settings
				array('cli' => false)
			),
		);
	}

	/**
	 * Test command name provider
	 */
	public function testCommand() {
		$Helper = new TaskHelper(new View);
		$task = array('command' => 'c1');
		$this->assertSame($task['command'], $Helper->command($task));
	}

	/**
	 * Test process id formatter
	 * 
	 * @param array $task
	 * @param string $result
	 * @param array $settings
	 * @dataProvider processIdProvider
	 */
	public function testProcessId(array $task, $result, array $settings) {
		$Helper = new TaskHelper(new View, $settings);
		$this->assertSame($result, $Helper->processId($task));
	}

	/**
	 * Data provider for testProcessId
	 * 
	 * @return array
	 */
	public function processIdProvider() {
		return array(
			//set #0
			array(
				//task
				array('process_id' => 0),
				//result
				'none',
				//settings
				array('cli' => true)
			),
			//set #1
			array(
				//task
				array('process_id' => 0),
				//result
				'<span class="task-not-yet" style="color: gray;">none</span>',
				//settings
				array('cli' => false)
			),
			//set #2
			array(
				//task
				array('process_id' => '123'),
				//result
				'123',
				//settings
				array('cli' => false)
			),
			//set #3
			array(
				//task
				array('process_id' => '123'),
				//result
				'123',
				//settings
				array('cli' => true)
			),
		);
	}

	/**
	 * Test running formatter
	 * 
	 * @param array $task
	 * @param string $result
	 * @param array $settings
	 * @dataProvider runningProvider
	 */
	public function testRunning(array $task, $result, array $settings) {
		$Helper = new TaskHelper(new View, $settings);
		$this->assertStringMatchesFormat($result, $Helper->running($task));
	}

	/**
	 * Data provider for testRunning
	 * 
	 * @return array
	 */
	public function runningProvider() {
		return array(
			//set #0
			array(
				//task
				array('started' => '2014-01-01 12:00:00', 'stopped' => '2014-01-03 15:13:05'),
				//result
				'2 days, 3 hours, %d minutes',
				//settings
				array('cli' => true)
			),
			//set #1
			array(
				//task
				array('started' => new DateTime('2014-01-01 12:00:00'), 'stopped' => new DateTime('2014-01-03 15:13:05')),
				//result
				'2 days, 3 hours, %d minutes',
				//settings
				array('cli' => true)
			),
			//set #2
			array(
				//task
				array('started' => null, 'stopped' => null),
				//result
				'none',
				//settings
				array('cli' => true)
			),
			//set #3
			array(
				//task
				array('started' => new DateTime('now -1 day -5 hour -3 minutes'), 'stopped' => null),
				//result
				'1 days, 5 hours, %d minutes',
				//settings
				array('cli' => true)
			),
			//set #4
			array(
				//task
				array('started' => '2014-01-01 12:00:00', 'stopped' => '2014-01-03 15:13:05'),
				//result
				'<span>2 days, 3 hours, %d minutes</span>',
				//settings
				array('cli' => false)
			),
			//set #5
			array(
				//task
				array('started' => new DateTime('2014-01-01 12:00:00'), 'stopped' => new DateTime('2014-01-03 15:13:05')),
				//result
				'<span>2 days, 3 hours, %d minutes</span>',
				//settings
				array('cli' => false)
			),
			//set #6
			array(
				//task
				array('started' => null, 'stopped' => null),
				//result
				'<span class="task-not-yet" style="color: gray;">none</span>',
				//settings
				array('cli' => false)
			),
			//set #7
			array(
				//task
				array('started' => new DateTime('now -1 day -5 hour -3 minutes'), 'stopped' => null),
				//result
				'<span>1 days, 5 hours, %d minutes</span>',
				//settings
				array('cli' => false)
			),
		);
	}

	/**
	 * Test running bar formatter
	 * 
	 * @param array $task
	 * @param array $runtimes
	 * @param string $result
	 * @param array $settings
	 * @dataProvider runningBarProvider
	 */
	public function testRunningBar(array $task, array $runtimes, $result, array $settings) {
		$Helper = new TaskHelper(new View, $settings);
		$this->assertSame($result, $Helper->runningBar($task, $runtimes));
	}

	/**
	 * Data provider for testRunningBar
	 * 
	 * @return array
	 */
	public function runningBarProvider() {
		return array(
			//set #0
			array(
				//task
				array(
					'status' => TaskType::FINISHED
				),
				//runtimes
				array(),
				//result
				'',
				//settings
				array('cli' => true)
			),
			//set #1
			array(
				//task
				array(
					'status' => TaskType::STOPPED
				),
				//runtimes
				array(),
				//result
				'',
				//settings
				array('cli' => true)
			),
			//set #2
			array(
				//task
				array(
					'status' => TaskType::RUNNING,
					'command' => 'c1'
				),
				//runtimes
				array(),
				//result
				'100%',
				//settings
				array('cli' => true)
			),
			//set #3
			array(
				//task
				array(
					'status' => TaskType::RUNNING,
					'command' => 'c1',
					'runtime' => 0
				),
				//runtimes
				array(
					'c1' => 50
				),
				//result
				'0%',
				//settings
				array('cli' => true)
			),
			//set #4
			array(
				//task
				array(
					'status' => TaskType::RUNNING,
					'command' => 'c1',
					'runtime' => 25
				),
				//runtimes
				array(
					'c1' => 50
				),
				//result
				'50%',
				//settings
				array('cli' => true)
			),
			//set #5
			array(
				//task
				array(
					'status' => TaskType::RUNNING,
					'command' => 'c1',
					'runtime' => 125
				),
				//runtimes
				array(
					'c1' => 50
				),
				//result
				'100%',
				//settings
				array('cli' => true)
			),
			//set #6
			array(
				//task
				array(
					'status' => TaskType::FINISHED
				),
				//runtimes
				array(),
				//result
				'',
				//settings
				array('cli' => false)
			),
			//set #7
			array(
				//task
				array(
					'status' => TaskType::STOPPED
				),
				//runtimes
				array(),
				//result
				'',
				//settings
				array('cli' => false)
			),
			//set #8
			array(
				//task
				array(
					'status' => TaskType::RUNNING,
					'command' => 'c1'
				),
				//runtimes
				array(),
				//result
				'<div class="progress progress-striped active progress-success"><div style="width: 100%;color:black;" class="bar">0d, 0h, 0m, 0s</div></div>',
				//settings
				array('cli' => false)
			),
			//set #9
			array(
				//task
				array(
					'status' => TaskType::RUNNING,
					'command' => 'c1',
					'runtime' => 0
				),
				//runtimes
				array(
					'c1' => 50
				),
				//result
				'<div class="progress progress-success"><div style="width: 0%;color:black;" class="bar">- 0d, 0h, 0m, 50s</div></div>',
				//settings
				array('cli' => false)
			),
			//set #10
			array(
				//task
				array(
					'status' => TaskType::RUNNING,
					'command' => 'c1',
					'runtime' => 25
				),
				//runtimes
				array(
					'c1' => 50
				),
				//result
				'<div class="progress progress-success"><div style="width: 50%;color:black;" class="bar">- 0d, 0h, 0m, 25s</div></div>',
				//settings
				array('cli' => false)
			),
			//set #11
			array(
				//task
				array(
					'status' => TaskType::RUNNING,
					'command' => 'c1',
					'runtime' => 76
				),
				//runtimes
				array(
					'c1' => 50
				),
				//result
				'<div class="progress progress-striped active progress-danger"><div style="width: 100%;color:black;" class="bar">+ 0d, 0h, 0m, 26s</div></div>',
				//settings
				array('cli' => false)
			),
			//set #11
			array(
				//task
				array(
					'status' => TaskType::RUNNING,
					'command' => 'c1',
					'runtime' => 76
				),
				//runtimes
				array(
					'c1' => 0
				),
				//result
				'<div class="progress progress-striped active progress-success"><div style="width: 100%;color:black;" class="bar">0d, 0h, 0m, 0s</div></div>',
				//settings
				array('cli' => false)
			),
		);
	}

	/**
	 * Test dates formatters
	 * 
	 * @param string $formatter
	 * @param array $task
	 * @param string $result
	 * @param array $settings
	 * @dataProvider datesProvider
	 */
	public function testDates($formatter, array $task, $result, array $settings) {
		$Helper = new TaskHelper(new View, $settings);
		$this->assertStringMatchesFormat($result, $Helper->{$formatter}($task));
	}

	/**
	 * Data provider for testDates
	 * 
	 * @return array
	 */
	public function datesProvider() {
		return array(
			//set #0
			array(
				//formatter
				'created',
				//task
				array(
					'created' => null
				),
				//result
				'none',
				//settings
				array('cli' => true)
			),
			//set #1
			array(
				//formatter
				'created',
				//task
				array(
					'created' => new DateTime('now -1 day -11 hours -3 minutes -5 seconds')
				),
				//result
				'1 day, 11 hours ago',
				//settings
				array('cli' => true)
			),
			//set #2
			array(
				//formatter
				'created',
				//task
				array(
					'created' => new DateTime('now -11 hours -3 minutes -5 seconds')
				),
				//result
				'11 hours, %d minutes ago',
				//settings
				array('cli' => true)
			),
			//set #3
			array(
				//formatter
				'created',
				//task
				array(
					'created' => new DateTime('now +11 hours +30 minutes +30 seconds')
				),
				//result
				'11 hours, %d minute%A',
				//settings
				array('cli' => true)
			),
			//set #4
			array(
				//formatter
				'created',
				//task
				array(
					'created' => null
				),
				//result
				'<span><span class="task-not-yet" style="color: gray;">none</span></span>',
				//settings
				array('cli' => false)
			),
			//set #5
			array(
				//formatter
				'created',
				//task
				array(
					'created' => new DateTime('now -1 day -11 hours -3 minutes -5 seconds')
				),
				//result
				'<span title="' . (new DateTime('now -1 day -11 hours -3 minutes -5 seconds'))->format(Configure::read('Task.dateFormat')) . '">1 day, 11 hours ago</span>',
				//settings
				array('cli' => false)
			),
			//set #6
			array(
				//formatter
				'created',
				//task
				array(
					'created' => (new DateTime('now -1 day -11 hours -3 minutes -5 seconds'))->format('Y-m-d H:i:s')
				),
				//result
				'<span title="' . (new DateTime('now -1 day -11 hours -3 minutes -5 seconds'))->format('Y-m-d H:i:s') . '">1 day, 11 hours ago</span>',
				//settings
				array('cli' => false)
			),
			//set #7
			array(
				//formatter
				'created',
				//task
				array(
					'created' => (new DateTime('now +11 hours +30 minutes +30 seconds'))->format('Y-m-d H:i:s')
				),
				//result
				'<span title="' . (new DateTime('now +11 hours +30 minutes +30 seconds'))->format('Y-m-d H:i:s') . '">11 hours, %d minute%A</span>',
				//settings
				array('cli' => false)
			),
			//set #8
			array(
				//formatter
				'modified',
				//task
				array(
					'modified' => (new DateTime('now -1 day -11 hours -3 minutes -5 seconds'))->format('Y-m-d H:i:s')
				),
				//result
				'<span title="' . (new DateTime('now -1 day -11 hours -3 minutes -5 seconds'))->format('Y-m-d H:i:s') . '">1 day, 11 hours ago</span>',
				//settings
				array('cli' => false)
			),
			//set #9
			array(
				//formatter
				'started',
				//task
				array(
					'started' => (new DateTime('now -1 day -11 hours -3 minutes -5 seconds'))->format('Y-m-d H:i:s')
				),
				//result
				'<span title="' . (new DateTime('now -1 day -11 hours -3 minutes -5 seconds'))->format('Y-m-d H:i:s') . '">1 day, 11 hours ago</span>',
				//settings
				array('cli' => false)
			),
			//set #10
			array(
				//formatter
				'stopped',
				//task
				array(
					'stopped' => (new DateTime('now -1 day -11 hours -3 minutes -5 seconds'))->format('Y-m-d H:i:s')
				),
				//result
				'<span title="' . (new DateTime('now -1 day -11 hours -3 minutes -5 seconds'))->format('Y-m-d H:i:s') . '">1 day, 11 hours ago</span>',
				//settings
				array('cli' => false)
			),
		);
	}

	/**
	 * Test stderr formatter
	 * 
	 * @param array $task
	 * @param bool $full
	 * @param string $result
	 * @dataProvider stderrProvider
	 */
	public function testStderr(array $task, $full, $result) {
		if (!$full) {
			$task['stderr_truncated'] = $task['stderr'];
			unset($task['stderr']);
		}
		$Helper = new TaskHelper(new View);
		$this->assertSame($result, $Helper->stderr($task, $full));
	}

	/**
	 * Data provider for testStderr
	 * 
	 * @return array
	 */
	public function stderrProvider() {
		return array(
			//set #0
			array(
				//task
				array(
					'stderr' => ''
				),
				//full
				false,
				//result
				''
			),
			//set #1
			array(
				//task
				array(
					'stderr' => 'efcw4rg243g52435gf234'
				),
				//full
				false,
				//result
				'efcw4rg243g52435gf234'
			),
			//set #2
			array(
				//task
				array(
					'stderr' => str_repeat('z', 201)
				),
				//full
				true,
				//result
				str_repeat('z', 201)
			),
			//set #3
			array(
				//task
				array(
					'stderr' => str_repeat('z', 200)
				),
				//full
				false,
				//result
				str_repeat('z', 200)
			),
			//set #4
			array(
				//task
				array(
					'stderr' => str_repeat('z', 201)
				),
				//full
				false,
				//result
				str_repeat('z', 199) . '…'
			),
			//set #5
			array(
				//task
				array(
					'stderr' => str_repeat('z', 201)
				),
				//full
				false,
				//result
				str_repeat('z', 199) . '…'
			),
			//set #6
			array(
				//task
				array(
					'stderr' => '<a href="#">' . str_repeat('z', 100) . '[9;m</a>'
				),
				//full
				false,
				//result
				Sanitize::html('<a href="#">' . str_repeat('z', 100) . '</a>')
			),
		);
	}

	/**
	 * Test stdout formatter
	 * 
	 * @param array $task
	 * @param bool $full
	 * @param string $result
	 * @dataProvider stdoutProvider
	 */
	public function testStdout(array $task, $full, $result) {
		$Helper = new TaskHelper(new View);
		$this->assertSame($result, $Helper->stdout($task, $full));
	}

	/**
	 * Data provider for testStdout
	 * 
	 * @return array
	 */
	public function stdoutProvider() {
		return array(
			//set #0
			array(
				//task
				array(
					'stdout' => ''
				),
				//full
				false,
				//result
				''
			),
			//set #1
			array(
				//task
				array(
					'stdout' => 'efcw4rg243g52435gf234'
				),
				//full
				false,
				//result
				'efcw4rg243g52435gf234'
			),
			//set #2
			array(
				//task
				array(
					'stdout' => str_repeat('z', 201)
				),
				//full
				true,
				//result
				str_repeat('z', 201)
			),
			//set #3
			array(
				//task
				array(
					'stdout' => str_repeat('z', 200)
				),
				//full
				false,
				//result
				str_repeat('z', 200)
			),
			//set #4
			array(
				//task
				array(
					'stdout' => str_repeat('z', 201)
				),
				//full
				false,
				//result
				str_repeat('z', 199) . '…'
			),
			//set #5
			array(
				//task
				array(
					'stdout' => str_repeat('z', 201)
				),
				//full
				false,
				//result
				str_repeat('z', 199) . '…'
			),
			//set #6
			array(
				//task
				array(
					'stdout' => '<a href="#">' . str_repeat('z', 100) . '[9;m</a>'
				),
				//full
				false,
				//result
				Sanitize::html('<a href="#">' . str_repeat('z', 100) . '</a>')
			),
		);
	}

	/**
	 * Test code string formatter
	 * 
	 * @param array $task
	 * @param bool $full
	 * @param string $result
	 * @param array $settings
	 * @dataProvider codeStringProvider
	 */
	public function testCodeString(array $task, $full, $result, array $settings) {
		$Helper = new TaskHelper(new View, $settings);
		$this->assertSame($result, $Helper->codeString($task, $full));
	}

	/**
	 * Data provider for code string
	 * 
	 * @return array
	 */
	public function codeStringProvider() {
		return array(
			//set #0
			array(
				//task
				array(
					'code_string' => ''
				),
				//full
				true,
				//result
				'',
				//settings
				array('cli' => true)
			),
			//set #1
			array(
				//task
				array(
					'code_string' => 'all is better than you think'
				),
				//full
				true,
				//result
				'all is better than you think',
				//settings
				array('cli' => true)
			),
			//set #2
			array(
				//task
				array(
					'code_string' => 'all is better than you think'
				),
				//full
				false,
				//result
				'all is better than you think',
				//settings
				array('cli' => true)
			),
			//set #3
			array(
				//task
				array(
					'code_string' => ''
				),
				//full
				true,
				//result
				'',
				//settings
				array('cli' => false)
			),
			//set #4
			array(
				//task
				array(
					'code_string' => 'ok, dude!'
				),
				//full
				true,
				//result
				'<span class="label label-important"><span title="ok, dude!">ok, dude!</span></span>',
				//settings
				array('cli' => false)
			),
			//set #5
			array(
				//task
				array(
					'code_string' => 'ok, dude!'
				),
				//full
				false,
				//result
				'<span class="label label-important"><span title="ok, dude!">ok, …</span></span>',
				//settings
				array('cli' => false)
			),
			//set #6
			array(
				//task
				array(
					'code_string' => 'OK'
				),
				//full
				false,
				//result
				'<span class="label label-success"><span title="OK">OK</span></span>',
				//settings
				array('cli' => false)
			),
		);
	}

	/**
	 * Test code formatter
	 */
	public function testCode() {
		$task = array(
			'code' => 123
		);
		$Helper = new TaskHelper(new View);
		$this->assertSame($task['code'], $Helper->code($task));
	}

	/**
	 * Test hash formatter
	 */
	public function testHash() {
		$task = array(
			'hash' => md5(123)
		);
		$Helper = new TaskHelper(new View);
		$this->assertSame($task['hash'], $Helper->hash($task));
	}

	/**
	 * Test path formatter
	 */
	public function testPath() {
		$task = array(
			'path' => '/var/www/blah-blah'
		);
		$Helper = new TaskHelper(new View);
		$this->assertSame($task['path'], $Helper->path($task));
	}

	/**
	 * Test server id formatter
	 */
	public function testServerId() {
		$task = array(
			'server_id' => 672542783
		);
		$Helper = new TaskHelper(new View);
		$this->assertSame($task['server_id'], $Helper->serverId($task));
	}

	/**
	 * Test timeout formatter
	 * 
	 * @param array $task
	 * @param string $result
	 * @param array $settings
	 * @dataProvider timeoutProvider
	 */
	public function testTimeout(array $task, $result, array $settings) {
		$Helper = new TaskHelper(new View, $settings);
		$this->assertSame($result, $Helper->timeout($task));
	}

	/**
	 * Data provider for testTimeout
	 * 
	 * @return array
	 */
	public function timeoutProvider() {
		return array(
			//set #0
			array(
				//task
				array(
					'timeout' => 60 * 60 * 24 * 4 + 60 * 60 * 3 + 60 * 2
				),
				//result
				'4 days, 3 hours, 2 minutes',
				//settings
				array('cli' => true)
			),
			//set #1
			array(
				//task
				array(
					'timeout' => 60 * 60 * 24 * 4 + 60 * 60 * 3 + 60 * 2
				),
				//result
				'<span>4 days, 3 hours, 2 minutes</span>',
				//settings
				array('cli' => false)
			),
		);
	}

	/**
	 * Test details formatter
	 */
	public function testDetails() {
		$task = array(
			'details' => array(
				'something' => 'new',
				'or' => array(
					'not' => 'new'
				),
				'no?'
			)
		);
		$result = 'new, new, no?';
		$Helper = new TaskHelper(new View);
		$this->assertSame($result, $Helper->details($task));
	}

	/**
	 * Test status formatter
	 * 
	 * @param array $task
	 * @param string $result
	 * @param array $settings
	 * @dataProvider statusProvider
	 */
	public function testStatus(array $task, $result, array $settings) {
		$Helper = new TaskHelper(new View, $settings);
		$this->assertSame($result, $Helper->status($task));
	}

	/**
	 * Data provider for testStatus
	 * 
	 * @return array
	 */
	public function statusProvider() {
		return array(
			//set #0
			array(
				//task
				array('status' => TaskType::UNSTARTED),
				//result
				'new',
				//settings
				array('cli' => true)
			),
			//set #1
			array(
				//task
				array('status' => TaskType::DEFFERED),
				//result
				'deffered',
				//settings
				array('cli' => true)
			),
			//set #2
			array(
				//task
				array('status' => TaskType::RUNNING),
				//result
				'running',
				//settings
				array('cli' => true)
			),
			//set #3
			array(
				//task
				array('status' => TaskType::FINISHED),
				//result
				'finished',
				//settings
				array('cli' => true)
			),
			//set #4
			array(
				//task
				array('status' => TaskType::STOPPING),
				//result
				'stopping',
				//settings
				array('cli' => true)
			),
			//set #5
			array(
				//task
				array('status' => TaskType::STOPPED),
				//result
				'stopped',
				//settings
				array('cli' => true)
			),
			//set #6
			array(
				//task
				array('status' => TaskType::UNSTARTED),
				//result
				'<span class="label label-success">new</span>',
				//settings
				array('cli' => false)
			),
			//set #7
			array(
				//task
				array('status' => TaskType::DEFFERED),
				//result
				'<span class="label ">deffered</span>',
				//settings
				array('cli' => false)
			),
			//set #8
			array(
				//task
				array('status' => TaskType::RUNNING),
				//result
				'<span class="label label-warning">running</span>',
				//settings
				array('cli' => false)
			),
			//set #9
			array(
				//task
				array('status' => TaskType::FINISHED),
				//result
				'<span class="label label-inverse">finished</span>',
				//settings
				array('cli' => false)
			),
			//set #10
			array(
				//task
				array('status' => TaskType::STOPPING),
				//result
				'<span class="label label-info">stopping</span>',
				//settings
				array('cli' => false)
			),
			//set #11
			array(
				//task
				array('status' => TaskType::STOPPED),
				//result
				'<span class="label label-important">stopped</span>',
				//settings
				array('cli' => false)
			),
		);
	}

	/**
	 * Test arguments
	 * 
	 * @param array $task
	 * @param bool $full
	 * @param string $result
	 * @param array $settings
	 * @dataProvider argumentsProvider
	 */
	public function testArguments(array $task, $full, $result, array $settings) {
		$Helper = new TaskHelper(new View, $settings);
		$this->assertSame($result, $Helper->arguments($task, $full));
	}

	/**
	 * Data provider for testArguments
	 * 
	 * @return array
	 */
	public function argumentsProvider() {
		return array(
			//set #0
			array(
				//task
				array(
					'arguments' => array(
					)
				),
				//full
				true,
				//result
				'',
				//settings
				array('cli' => true)
			),
			//set #1
			array(
				//task
				array(
					'arguments' => array(
						'asdfs',
						'--sdfs' => 'dsf',
						'-d' => 232423
					)
				),
				//full
				true,
				//result
				'asdfs --sdfs dsf -d 232423',
				//settings
				array('cli' => true)
			),
			//set #2
			array(
				//task
				array(
					'arguments' => array(
						'asdfs',
						'--sdfs' => 'dsf',
						'-d' => 232423
					)
				),
				//full
				false,
				//result
				'asdfs --s…',
				//settings
				array('cli' => true)
			),
			//set #3
			array(
				//task
				array(
					'arguments' => array(
					)
				),
				//full
				true,
				//result
				'<span title=""></span>',
				//settings
				array('cli' => false)
			),
			//set #4
			array(
				//task
				array(
					'arguments' => array(
						'asdfs',
						'--sdfs' => 'dsf',
						'-d' => 232423
					)
				),
				//full
				true,
				//result
				'<span title="asdfs --sdfs dsf -d 232423">asdfs --sdfs dsf -d 232423</span>',
				//settings
				array('cli' => false)
			),
			//set #5
			array(
				//task
				array(
					'arguments' => array(
						'asdfs',
						'--sdfs' => 'dsf',
						'-d' => 232423
					)
				),
				//full
				false,
				//result
				'<span title="asdfs --sdfs dsf -d 232423">asdfs --s…</span>',
				//settings
				array('cli' => false)
			),
		);
	}

	/**
	 * Test waiting for tasks formatter
	 * 
	 * @param array $tasks
	 * @param bool $full
	 * @param string $result
	 * @param array $settings
	 * @dataProvider waitingProvider
	 */
	public function testWaiting(array $tasks, $full, $result, array $settings) {
		$Helper = new TaskHelper(new View, $settings);
		$this->assertStringMatchesFormat($result, $Helper->waiting($tasks, $full));
	}

	/**
	 * Data provider for testWaitings
	 * 
	 * @return array
	 */
	public function waitingProvider() {
		return array(
			//set #0
			array(
				//tasks
				array(),
				//full
				true,
				//result
				'none',
				//settings
				array('cli' => true)
			),
			//set #1
			array(
				//tasks
				array(
					array(
						'status' => TaskType::FINISHED,
					),
					array(
						'status' => TaskType::STOPPED,
					),
				),
				//full
				true,
				//result
				'none',
				//settings
				array('cli' => true)
			),
			//set #2
			array(
				//tasks
				array(
					array(
						'id' => 1,
						'status' => TaskType::UNSTARTED,
					),
					array(
						'id' => 2,
						'status' => TaskType::DEFFERED,
					),
				),
				//full
				true,
				//result
				'1, 2',
				//settings
				array('cli' => true)
			),
			//set #3
			array(
				//tasks
				array(
					array(
						'id' => 1,
						'status' => TaskType::UNSTARTED,
					),
					array(
						'id' => 2,
						'status' => TaskType::DEFFERED,
					),
				),
				//full
				false,
				//result
				'1, 2',
				//settings
				array('cli' => true)
			),
			//set #4
			array(
				//tasks
				array(),
				//full
				true,
				//result
				'<span class="task-not-yet" style="color: gray;">none</span>',
				//settings
				array('cli' => false)
			),
			//set #1
			array(
				//tasks
				array(
					array(
						'status' => TaskType::FINISHED,
					),
					array(
						'status' => TaskType::STOPPED,
					),
				),
				//full
				true,
				//result
				'<span class="task-not-yet" style="color: gray;">none</span>',
				//settings
				array('cli' => false)
			),
			//set #2
			array(
				//tasks
				array(
					array(
						'id' => 1,
						'status' => TaskType::UNSTARTED,
					),
					array(
						'id' => 2,
						'status' => TaskType::DEFFERED,
					),
				),
				//full
				true,
				//result
				'<a href="http://example.com/task%sview/1">1</a>, <a href="http://example.com/task%sview/2">2</a>',
				//settings
				array('cli' => false)
			),
			//set #3
			array(
				//tasks
				array(
					array(
						'id' => 1,
						'status' => TaskType::UNSTARTED,
					),
					array(
						'id' => 2,
						'status' => TaskType::DEFFERED,
					),
				),
				//full
				false,
				//result
				'<span title="1, 2"><a href="http://example.com/task%sview/1">1</a>...</span>',
				//settings
				array('cli' => false)
			),
			//set #3
			array(
				//tasks
				array(
					array(
						'id' => 1,
						'status' => TaskType::UNSTARTED,
					)
				),
				//full
				false,
				//result
				'<span title="1"><a href="http://example.com/task%sview/1">1</a></span>',
				//settings
				array('cli' => false)
			),
		);
	}

	/**
	 * Test statistics
	 * 
	 * @param array $statistics
	 * @param string $result
	 * @param array $settings
	 * @dataProvider statisticsProvider
	 */
	public function testStatistics(array $statistics, $result, array $settings) {
		$Helper = new TaskHelper(new View, $settings);
		$this->assertSame($result, $Helper->statistics($statistics));
	}

	/**
	 * Data provider for testStatistics
	 * 
	 * @return array
	 */
	public function statisticsProvider() {
		return array(
			//set #0
			array(
				//statistics
				array(),
				//result
				'Not allowed in cli',
				//settings
				array('cli' => true)
			),
			//set #1
			array(
				//statistics
				array(),
				//result
				'<span class="task-not-yet" style="color: gray;">Please install <b>imsamurai/cakephp-google-chart</b> plugin to view graph</span>',
				//settings
				array('cli' => false, 'chartEnabled' => false)
			)
		);
	}

	/**
	 * Test statistics chart
	 * 
	 * @param array $statistics
	 * @param string $result
	 * @param array $settings
	 * @dataProvider statisticsChartProvider
	 */
	public function testStatisticsChart(array $statistics, $result, array $settings) {
		$this->skipUnless(CakePlugin::loaded('GoogleChart'), 'Please install imsamurai/cakephp-google-chart for this test');
		$View = new View;
		$Helper = new TaskHelper($View, $settings);
		$this->assertStringMatchesFormat($result, $Helper->statistics($statistics) . $View->fetch('script'));
	}

	/**
	 * Data provider for testStatistics
	 * 
	 * @return array
	 */
	public function statisticsChartProvider() {
		return array(
			//set #0
			array(
				//statistics
				array(),
				//result
				'<span class="task-not-yet" style="color: gray;">none</span>',
				//settings
				array('cli' => false)
			),
			//set #1
			array(
				//statistics
				array(
					(int)0 => array(
						'id' => '1',
						'task_id' => '1',
						'memory' => '0.0',
						'cpu' => '0.0',
						'status' => 'R',
						'created' => '2014-09-16 14:31:47'
					),
					(int)1 => array(
						'id' => '2',
						'task_id' => '1',
						'memory' => '0.4',
						'cpu' => '102.0',
						'status' => 'R',
						'created' => '2014-09-16 14:31:48'
					),
					(int)2 => array(
						'id' => '3',
						'task_id' => '1',
						'memory' => '0.4',
						'cpu' => '101.0',
						'status' => 'R',
						'created' => '2014-09-16 14:31:49'
					),
					(int)3 => array(
						'id' => '4',
						'task_id' => '1',
						'memory' => '0.4',
						'cpu' => '101.0',
						'status' => 'R',
						'created' => '2014-09-16 14:31:50'
					),
					(int)4 => array(
						'id' => '5',
						'task_id' => '1',
						'memory' => '0.4',
						'cpu' => '101.0',
						'status' => 'R',
						'created' => '2014-09-16 14:31:51'
					),
				),
				//result
				'<div id="%s"></div><script type="text/javascript" src="https://www.google.com/jsapi"></script><script type="text/javascript">
//<![CDATA[
google.load(\'visualization\', 1.0, {"packages":["corechart","controls"]});
//]]>
</script><script type="text/javascript">
//<![CDATA[
setTimeout(function(){$(document).ready(function () {var data = new google.visualization.DataTable({"cols":[{"id":"date","label":"date","type":"datetime"},{"id":"memory","label":"memory","type":"number"},{"id":"cpu","label":"cpu","type":"number"},{"p":{"role":"annotation"},"type":"string"}],"rows":[{"c":[{"v":new Date(%d),"f":"2014-09-16 14:31:47"},{"v":0,"f":"0"},{"v":0,"f":"0"},{"v":"R","f":"R"}]},{"c":[{"v":new Date(%d),"f":"2014-09-16 14:31:48"},{"v":0.4,"f":"0.4"},{"v":102,"f":"102"},{"v":"R","f":"R"}]},{"c":[{"v":new Date(%d),"f":"2014-09-16 14:31:49"},{"v":0.4,"f":"0.4"},{"v":101,"f":"101"},{"v":"R","f":"R"}]},{"c":[{"v":new Date(%d),"f":"2014-09-16 14:31:50"},{"v":0.4,"f":"0.4"},{"v":101,"f":"101"},{"v":"R","f":"R"}]},{"c":[{"v":new Date(%d),"f":"2014-09-16 14:31:51"},{"v":0.4,"f":"0.4"},{"v":101,"f":"101"},{"v":"R","f":"R"}]}]});var chart = new google.visualization.LineChart(document.getElementById("%s"));chart.draw(data, {"height":300,"width":800,"pointSize":5,"vAxis":{"title":"Percentage"},"hAxis":{"title":"Time"},"chartArea":{"left":50,"top":10,"height":230,"width":650}});});}, 100);
//]]>
</script>',
				//settings
				array('cli' => false)
			),
		);
	}

}
