<?php
/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 08.07.2013
 * Time: 13:23:01
 */

use Symfony\Component\Process\ProcessUtils;

App::uses('TaskProcess', 'Task.Lib/Task');

/**
 * Task Runner
 *
 * @package Task
 * @subpackage Task
 */
class TaskRunner extends Object {

	/**
	 * TaskServer Model
	 *
	 * @var TaskServer
	 */
	protected $_TaskServer = null;

	/**
	 * TaskClient Model
	 *
	 * @var TaskClient
	 */
	protected $_TaskClient = null;

	/**
	 * Process
	 *
	 * @var Symfony\Component\Process\Process
	 */
	protected $_Process = null;

	/**
	 * Shell object
	 *
	 * @var Shell
	 */
	protected $_Shell = null;

	/**
	 * Result code
	 *
	 * @var int
	 */
	protected $_code = null;

	/**
	 * Result code text
	 *
	 * @var string
	 */
	protected $_codeString = null;

	/**
	 * Task
	 *
	 * @var array
	 */
	protected $_task = null;

	/**
	 * Constructor
	 *
	 * @param array $task
	 * @param TaskServer $TaskServer
	 * @param TaskClient $TaskClient
	 * @param Shell $Shell
	 */
	public function __construct(array $task, TaskServer $TaskServer, TaskClient $TaskClient, Shell $Shell = null) {
		$this->_task = $task;
		$this->_TaskServer = $TaskServer;
		$this->_TaskClient = $TaskClient;
		$this->_Shell = $Shell ? $Shell : new Shell();
	}

	/**
	 * Notify client about started task and run this task
	 */
	public function start() {
		ConnectionManager::getDataSource($this->_TaskServer->useDbConfig)->reconnect(array('persistent' => false));
		$this->_Shell->out("Task #{$this->_task['id']} started");
		$this->_task['started'] = $this->_getCurrentDateTime();
		$this->_task['stderr'] = '';
		$this->_task['stdout'] = '';
		$this->_TaskServer->started($this->_task);
		$this->_run();
		return $this->_task;
	}

	/**
	 * Notify client about stopped task
	 * 
	 * @param bool $manual True means process stopped manually
	 */
	protected function _stopped($manual = false) {
		$this->_task = array(
			'stdout' => $this->_Process->getOutput(),
			'stderr' => $this->_Process->getErrorOutput(),
			'stopped' => $this->_getCurrentDateTime(),
			'process_id' => 0,
				) + $this->_task;
		$this->_TaskServer->stopped($this->_task, $manual);
		$this->_Shell->out("Task #{$this->_task['id']} stopped, code " . (string)$this->_task['code']);
	}

	/**
	 * Runs task
	 */
	protected function _run() {
		$this->_Process = new TaskProcess($this->_task['command'] . $this->_argsToString($this->_task['arguments']), $this->_task['path']);
		$this->_Process->setTimeout($this->_task['timeout']);
		try {
			$this->_Process->start(function ($type, $buffer) {
				if ('err' === $type) {
					$this->_Shell->err($buffer);
					$this->_task['stderr'] .= $buffer;
				} else {
					$this->_Shell->out($buffer);
					$this->_task['stdout'] .= $buffer;
				}
				$this->_TaskServer->updated($this->_task);
			});

			while ($this->_Process->isRunning()) {
				$this->_task['process_id'] = (int)$this->_Process->getPid();
				$this->_TaskServer->updateStatistics($this->_task);
				$this->_Process->checkTimeout();
				sleep(Configure::read('Task.checkInterval'));
				if ($this->_TaskServer->mustStop($this->_task['id'])) {
					$this->_Process->stop(Configure::read('Task.stopTimeout'));
					$this->_task['code'] = 143;
					$this->_task['code_string'] = TaskProcess::$exitCodes[143];
					return $this->_stopped(true);
				}
			}

			$this->_task['code'] = $this->_Process->getExitCode();
			$this->_task['code_string'] = $this->_Process->getExitCodeText();
		} catch (Exception $Exception) {
			$this->_task['code'] = 134;
			$this->_task['code_string'] = $Exception->getMessage();
		}

		$this->_stopped(false);
	}

	/**
	 * Convert array of arguments into string
	 *
	 * @param array $arguments
	 * @return string
	 */
	protected function _argsToString(array $arguments) {
		$stringArguments = '';
		foreach ($arguments as $name => $value) {
			if (is_numeric($name)) {
				$stringArguments .= ' ' . $value;
			} else {
				$stringArguments .= ' ' . $name . ' ' . ProcessUtils::escapeArgument($value);
			}
		}

		return $stringArguments;
	}

	/**
	 * Returns current date for DB
	 *
	 * @return string
	 */
	protected function _getCurrentDateTime() {
		return (new DateTime('now'))->format('Y-m-d H:i:s');
	}

}
