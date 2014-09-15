<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 31.07.2013
 * Time: 12:12:55
 * Format: http://book.cakephp.org/2.0/en/development/testing.html
 */
App::uses('TaskRunner', 'Task.Lib/Task');
App::uses('TaskClient', 'Task.Model');
App::uses('Shell', 'Console');

/**
 * Task runner tests
 * 
 * @package TaskTest
 * @subpackage Task
 */
class TaskRunnerTest extends CakeTestCase {

	/**
	 * PHP or HHVM executable
	 *
	 * @var string
	 */
	public $executable = null;
	
	/**
	 * {@inheritdoc}
	 */
	public function setUp() {
		parent::setUp();
		Configure::write('Task', array(
			'checkInterval' => 1,
			'stopTimeout' => 1
		));
		$this->executable = defined('HHVM_VERSION') ? 'hhvm' : 'php';
	}

	/**
	 * Test terminate task
	 */
	public function testTerminate() {
		$TaskClient = $this->getMock('TaskClient');
		$TaskServer = $this->getMock('TaskServer', array(
			'stopped',
			'started',
			'updated',
			'mustStop'
		));
		$TaskServer->useDbConfig = 'test';
		$TaskServer->expects($this->once())->method('started');
		$TaskServer->expects($this->once())->method('stopped');
		$TaskServer->expects($this->any())->method('updated');
		$TaskServer->expects($this->atLeastOnce())->method('mustStop')->will($this->returnValue(true));
		$Shell = $this->getMock('Shell', array(
			'out',
			'err'
		));

		$task = array(
			'id' => 1,
			'path' => '',
			'command' => $this->executable,
			'arguments' => array(
				'-f' => $this->_code2File('sleep(2);')
			),
			'timeout' => 1
		);
		$TaskRunner = new TaskRunner($task, $TaskServer, $TaskClient, $Shell);

		$runnedTask = $TaskRunner->start();

		$this->assertNotEqual((int)$runnedTask['code'], 0);
	}

	/**
	 * Test task timeout
	 */
	public function testTimeout() {
		$TaskClient = $this->getMock('TaskClient');
		$TaskServer = $this->getMock('TaskServer', array(
			'stopped',
			'started',
			'updated',
			'mustStop'
		));
		$TaskServer->useDbConfig = 'test';
		$TaskServer->expects($this->once())->method('started');
		$TaskServer->expects($this->once())->method('stopped');
		$TaskServer->expects($this->never())->method('updated');
		$TaskServer->expects($this->atLeastOnce())->method('mustStop')->will($this->returnValue(false));
		$Shell = $this->getMock('Shell', array(
			'out',
			'err'
		));

		$task = array(
			'id' => 1,
			'path' => '',
			'command' => $this->executable,
			'arguments' => array(
				'-f' => $this->_code2File('while (true) {};'),
				'hello'
			),
			'timeout' => 10
		);
		$TaskRunner = new TaskRunner($task, $TaskServer, $TaskClient, $Shell);

		$runnedTask = $TaskRunner->start();

		$this->assertSame(134, $runnedTask['code']);
	}

	/**
	 * Test update task
	 */
	public function testUpdate() {
		$TaskClient = $this->getMock('TaskClient');
		$TaskServer = $this->getMock('TaskServer', array(
			'stopped',
			'started',
			'updated',
			'mustStop'
		));
		$TaskServer->useDbConfig = 'test';
		$TaskServer->expects($this->once())->method('started');
		$TaskServer->expects($this->once())->method('stopped');
		$TaskServer->expects($this->atLeastOnce())->method('updated');
		$TaskServer->expects($this->atLeastOnce())->method('mustStop')->will($this->returnValue(false));
		$Shell = $this->getMock('Shell', array(
			'out',
			'err'
		));

		$task = array(
			'id' => 1,
			'path' => '',
			'command' => $this->executable,
			'arguments' => array(
				'-f' => $this->_code2File('echo 123;sleep(1);echo 555;sleep(1);echo 321;sleep(1);echo 444;file_put_contents("php://stderr", "error", FILE_APPEND);')
			),
			'timeout' => 10
		);
		$TaskRunner = new TaskRunner($task, $TaskServer, $TaskClient, $Shell);

		$runnedTask = $TaskRunner->start();

		$this->assertSame('123555321444', $runnedTask['stdout']);
		$this->assertSame('error', $runnedTask['stderr']);
	}
	
	/**
	 * Helper for make file with code
	 * 
	 * @param string $code
	 * @return string
	 */
	protected function _code2File($code) {
		$name = tempnam('/tmp', 'task_test');
		file_put_contents($name, "<?php \n".$code);
		return $name;
	}

}