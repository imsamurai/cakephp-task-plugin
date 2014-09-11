<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 19.06.2014
 * Time: 13:12:39
 */
use Symfony\Component\Process\Process;

/**
 * Task Process wrapper
 *
 * @package Task
 * @subpackage Task
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
	 * 
	 * @param int $timeout
	 * @param int $signal
	 */
	protected function _terminate($timeout = 10, $signal = /* SIGTERM */ 15) {
		foreach ($this->_getPidRecursive($this->getPid()) as $pid) {
			posix_kill($pid, $signal);
		}
		parent::stop($timeout, $signal);
	}

	/**
	 * Collect all childrens pids recursively
	 * 
	 * @param int $ppid
	 * @return array
	 */
	protected function _getPidRecursive($ppid) {
		if (!$ppid) {
			return array();
		}
		$allPids = $pids = array_filter(preg_split('/\s+/', `ps -o pid --no-heading --ppid $ppid`));

		foreach ($pids as $pid) {
			$allPids = array_merge($allPids, $this->_getPidRecursive($pid));
		}
		return $allPids;
	}

}
