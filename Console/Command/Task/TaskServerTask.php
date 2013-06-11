<?

use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessUtils;

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 10.06.2013
 * Time: 17:52:43
 * Format: http://book.cakephp.org/2.0/en/console-and-shells.html#shell-tasks
 */

/**
 * @package Task.Console.Command.Task
 */
class TaskServerTask extends Shell {

	public $uses = array('Task.TaskServer');

	/**
	 * Process
	 *
	 * @var Symfony\Component\Process\Process
	 */
	protected $_Process = null;

	/**
	 * {@inheritdoc}
	 *
	 * @return void
	 */
	public function execute() {
		$task = $this->TaskServer->getPending();
		if (!$task || $this->TaskServer->freeSlots() < 1) {
			return;
		}

		$this->start($task);
	}

	/**
	 * Notify client about stopped task
	 *
	 * @param array $task
	 */
	public function stop(array $task) {
		$task = array(
			'code' => $this->_Process->getExitCode(),
			'code_string' => $this->_Process->getExitCodeText(),
			'stdout' => $this->_Process->getOutput(),
			'stderr' => $this->_Process->getErrorOutput(),
			'stopped' => $this->_getCurrentDateTime()
				) + $task;
		$this->TaskServer->stoped($task);
		$this->out("Task #{$task['id']} stopped, code " . (string) $task['code']);
	}

	/**
	 * Notify client about started task and run this task
	 *
	 * @param array $task
	 */
	public function start(array $task) {
		$this->out("Task #{$task['id']} started");
		$task['started'] = $this->_getCurrentDateTime();
		$this->TaskServer->started($task);
		$this->run($task);
	}

	/**
	 * Runs task
	 *
	 * @param array $task
	 */
	public function run(array $task) {
		$this->_Process = new Process($task['command'] . $this->_argsToString($task['arguments']), $task['path']);
		$this->_Process->setTimeout($task['timeout']);
		$this->_Process->run(function ($type, $buffer) {
					if ('err' === $type) {
						$this->err($buffer);
					} else {
						$this->out($buffer);
					}
				});
		$this->stop($task);
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