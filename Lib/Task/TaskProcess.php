<?php

use Symfony\Component\Process\Process;

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 19.06.2014
 * Time: 13:12:39
 */

/**
 * Task Process wrapper
 *
 * @package Task.Lib.Task
 */
class TaskProcess extends Process {

	/**
	 * {@inheritdoc}
	 *
	 * @param int|float     $timeout The timeout in seconds
	 * @param int           $signal  A POSIX signal to send in case the process has not stop at timeout, default is SIGKILL
	 *
	 * @return int     The exit-code of the process
	 *
	 * @throws RuntimeException if the process got signaled
	 */
	public function stop($timeout = 10, $signal = /* SIGTERM */ 15) {
		return $this->_terminate($timeout, $signal);
	}

	/**
	 * Terminate current process
	 */
	protected function _terminate($timeout = 10, $signal = /* SIGTERM */ 15) {
		$ppid = $this->getPid();
		$pids = preg_split('/\s+/', `ps -o pid --no-heading --ppid $ppid`);
		foreach ($pids as $pid) {
			if (is_numeric($pid)) {
				posix_kill($pid, $signal);
			}
		}
		parent::stop($timeout, $signal);
	}

}
