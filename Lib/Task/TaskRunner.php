<?php

use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessUtils;

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 08.07.2013
 * Time: 13:23:01
 *
 */

/**
 * Task Runner
 *
 * @package Task.Lib.Task
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
	protected $_code = null;
	protected $_codeString = null;

	/**
	 * Constructor
	 *
	 * @param TaskServer $TaskServer
	 * @param TaskClient $TaskClient
	 * @param Shell $Shell
	 */
	public function __construct(TaskServer $TaskServer, TaskClient $TaskClient, Shell $Shell = null) {
		$this->_TaskServer = $TaskServer;
		$this->_TaskClient = $TaskClient;
		$this->_Shell = $Shell ? $Shell : new Shell();
	}

	/**
	 * Notify client about stopped task
	 *
	 * @param array $task
	 */
	public function stop(array $task) {
		$task = array(
			'code' => $this->_code,
			'code_string' => $this->_codeString,
			'stdout' => $this->_Process->getOutput(),
			'stderr' => $this->_Process->getErrorOutput(),
			'stopped' => $this->_getCurrentDateTime()
				) + $task;
		$this->_TaskServer->stoped($task);
		$this->_Shell->out("Task #{$task['id']} stopped, code " . (string) $task['code']);
		return $task;
	}

	/**
	 * Notify client about started task and run this task
	 *
	 * @param array $task
	 */
	public function start(array $task) {
		ConnectionManager::getDataSource($this->_TaskServer->useDbConfig)->reconnect(array('persistent' => false));
		$this->_Shell->out("Task #{$task['id']} started");
		$task['started'] = $this->_getCurrentDateTime();
		$this->_TaskServer->started($task);
		return $this->run($task);
	}

	/**
	 * Runs task
	 *
	 * @param array $task
	 */
	public function run(array $task) {
		$this->_Process = new Process($task['command'] . $this->_argsToString($task['arguments']), $task['path']);
		$this->_Process->setTimeout($task['timeout']);
		try {
			$this->_Process->run(function ($type, $buffer) {
						if ('err' === $type) {
							$this->_Shell->err($buffer);
						} else {
							$this->_Shell->out($buffer);
						}
					});
			$this->_code = $this->_Process->getExitCode();
			$this->_codeString = $this->_Process->getExitCodeText();
		} catch (Exception $Exception) {
			$this->_code = 134;
			$this->_codeString = $Exception->getMessage();
		}

		return $this->stop($task);
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
				$stringArguments.=' ' . $value;
			} else {
				$stringArguments.=' ' . $name . ' ' . ProcessUtils::escapeArgument($value);
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