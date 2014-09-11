<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 09.09.2014
 * Time: 16:17:01
 * Format: http://book.cakephp.org/2.0/en/development/testing.html
 */
App::uses('TaskProcess', 'Task.Lib/Task');

/**
 * TaskProcessTest
 * 
 * @package TaskTest
 * @subpackage Task
 */
class TaskProcessTest extends CakeTestCase {

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
		$this->executable = defined('HHVM_VERSION') ? 'hhvm' : 'php';
		`rm -rf /tmp/task_process_test_stop*`;
	}

	/**
	 * Test stop
	 * 
	 * @throws Exception
	 */
	public function testStop() {
		$fileName = '/tmp/task_process_test_stop' . mt_rand();
		$Process = new TaskProcess($this->executable . ' -f task_process_test_stop.php ' . $fileName, dirname(dirname(dirname(dirname(__FILE__)))) . DS . 'Data' . DS);
		$Process->start();
		sleep(1);
		$error = $Process->getErrorOutput();
		if ($error) {
			throw new Exception($error);
		}
		$pids = file($fileName);
		$this->assertCount(2, $this->_activePids($pids), 'not all childrens started');
		$Process->stop(1);
		sleep(1);
		$this->assertCount(0, $this->_activePids($pids), 'not all childrens killed');
	}

	/**
	 * Helper for filter pids by active ones
	 * 
	 * @param array $pids
	 * @return array
	 */
	protected function _activePids(array $pids) {
		$active = array();
		foreach ($pids as $pid) {
			$fixPid = '[' . substr($pid, 0, 1) . ']' . substr($pid, 1);
			$check = trim(`ps ax | grep $fixPid`);
			if ($check) {
				$active[] = $pid;
			}
		}

		return $active;
	}

}
