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
			'stoped',
			'started'
		));
		$TaskServer->useDbConfig = 'test';
		$TaskServer->expects($this->once())->method('started');
		$TaskServer->expects($this->once())->method('stoped');
		$Shell = $this->getMock('Shell', array(
			'out',
			'err'
		));

		$TaskRunner = new TaskRunner($TaskServer, $TaskClient, $Shell);
		$task = array(
			'id' => 1,
			'path' => '',
			'command' => 'php',
			'arguments' => array(
				'-r' => 'sleep(2);echo 123;'
			),
			'timeout' => 1
		);

		$runnedTask = $TaskRunner->start($task);
		debug($runnedTask);

		$this->assertSame(134, $runnedTask['code']);
	}

}