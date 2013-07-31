<?

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
 * @package Task.Test.Lib.Task
 */
class TaskRunnerTest extends CakeTestCase {

	public function setUp() {
		parent::setUp();
	}

	public function testTimeout() {
		$TaskClient = $this->getMock('TaskClient');
		$TaskServer = $this->getMock('TaskServer', array(
			'stopped',
			'started',
			'updated'
		));
		$TaskServer->useDbConfig = 'test';
		$TaskServer->expects($this->once())->method('started');
		$TaskServer->expects($this->once())->method('stopped');
		$TaskServer->expects($this->never())->method('updated');
		$Shell = $this->getMock('Shell', array(
			'out',
			'err'
		));

		$task = array(
			'id' => 1,
			'path' => '',
			'command' => 'php',
			'arguments' => array(
				'-r' => 'sleep(2);'
			),
			'timeout' => 1
		);
		$TaskRunner = new TaskRunner($task, $TaskServer, $TaskClient, $Shell);


		$runnedTask = $TaskRunner->start();
		debug($runnedTask);

		$this->assertSame(134, $runnedTask['code']);
	}

	public function testUpdate() {
		$TaskClient = $this->getMock('TaskClient');
		$TaskServer = $this->getMock('TaskServer', array(
			'stopped',
			'started',
			'updated'
		));
		$TaskServer->useDbConfig = 'test';
		$TaskServer->expects($this->once())->method('started');
		$TaskServer->expects($this->once())->method('stopped');
		$TaskServer->expects($this->exactly(3))->method('updated')->will($this->returnCallback(function($task) {
			debug($task['stdout']); 
		}));
		$Shell = $this->getMock('Shell', array(
			'out',
			'err'
		));

		$task = array(
			'id' => 1,
			'path' => '',
			'command' => 'php',
			'arguments' => array(
				'-r' => 'echo 123;sleep(1);echo 555;sleep(1);echo 321;echo 444;'
			),
			'timeout' => 10
		);
		$TaskRunner = new TaskRunner($task, $TaskServer, $TaskClient, $Shell);


		$runnedTask = $TaskRunner->start();
		debug($runnedTask);

		$this->assertSame('123555321444', $runnedTask['stdout']);
	}

}