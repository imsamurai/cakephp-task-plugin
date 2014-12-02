<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 22.07.2014
 * Time: 11:54:03
 * Format: http://book.cakephp.org/2.0/en/models.html
 */

/**
 * TaskProfiler Model
 */
class TaskProfiler extends AppModel {

	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	public $name = 'TaskProfiler';
	
	/**
	 * {@inheritdoc}
	 *
	 * @var bool
	 */
	public $useTable = false;
	
	/**
	 * Task model
	 *
	 * @var TaskClient
	 */
	public $Task = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->Task = ClassRegistry::init('Task.TaskClient');
	}

	/**
	 * Profile command
	 * 
	 * @param string $command
	 * @return array
	 */
	public function profileCommand($command) {
		$tasks = $this->Task->find('all', array(
			'conditions' => array(
				'command' => $command,
				'status' => array(TaskType::STOPPED, TaskType::FINISHED),
				'runtime >' => 0
			),
			'fields' => array('runtime', 'id', 'started', 'stopped', 'created', 'waittime'),
			'limit' => Configure::read('Task.profilerLimit'),
			'order' => array(
				'id' => 'DESC'
			)
		));

		if (!$tasks) {
			return null;
		}

		$statistics = array_map(function($item) {
			$endDate = new DateTime($item['stopped']);
			$startDate = new DateTime($item['started']);
			$createdDate = new DateTime($item['created']);
			$item['runtimeHuman'] = $startDate->diff($endDate)->format(Configure::read('Task.dateDiffFormat'));
			$item['waittimeHuman'] = $createdDate->diff($startDate)->format(Configure::read('Task.dateDiffFormat'));
			$item['startedTimestamp'] = $startDate->getTimestamp();
			$item['runtime'] = (int)$item['runtime'];
			$item['waittime'] = (int)$item['waittime'];
			return $item;
		}, Hash::extract($tasks, '{n}.{s}'));
		
		$runtimes = Hash::extract($statistics, '{n}.runtime');
		$runtimeAverage = (int)round($runtimes ? array_sum($runtimes) / count($runtimes) : 0);
		$runtimeMax = $runtimes ? max($runtimes) : 0;
		$runtimeMin = $runtimes ? min($runtimes) : 0;

		$waittimes = Hash::extract($statistics, '{n}.waittime');
		$waittimeAverage = (int)round($waittimes ? array_sum($waittimes) / count($waittimes) : 0);
		$waittimeMax = $waittimes ? max($waittimes) : 0;
		$waittimeMin = $waittimes ? min($waittimes) : 0;

		$countByStatus = array();
		foreach (TaskType::getTypes() as $statusCode) {
			$countByStatus[$statusCode] = $this->Task->find('count', array(
				'conditions' => array(
					'command' => $command,
					'status' => $statusCode
				)
			));
		}

		$errored = $this->Task->find('count', array(
			'conditions' => array(
				'command' => $command,
				'errored' => true
			)
		));

		return array(
			'command' => $command,
			'countByStatus' => $countByStatus,
			'errored' => $errored,
			'statistics' => $statistics,
			'runtimeAverage' => $runtimeAverage,
			'runtimeAverageHuman' => $this->_secondsToHuman($runtimeAverage),
			'runtimeMax' => $runtimeMax,
			'runtimeMaxHuman' => $this->_secondsToHuman($runtimeMax),
			'runtimeMin' => $runtimeMin,
			'runtimeMinHuman' => $this->_secondsToHuman($runtimeMin),
			'waittimeAverage' => $waittimeAverage,
			'waittimeAverageHuman' => $this->_secondsToHuman($waittimeAverage),
			'waittimeMax' => $waittimeMax,
			'waittimeMaxHuman' => $this->_secondsToHuman($waittimeMax),
			'waittimeMin' => $waittimeMin,
			'waittimeMinHuman' => $this->_secondsToHuman($waittimeMin),
		);
	}
	
	/**
	 * Approximate runtime of the command
	 * 
	 * @param string $command
	 * @return int
	 */
	public function approximateRuntime($command) {
		$tasks = $this->Task->find('list', array(
			'conditions' => array(
				'command' => $command,
				'status' => TaskType::FINISHED,
				'runtime >' => 0
			),
			'fields' => array('id', 'runtime'),
			'limit' => Configure::read('Task.approximateLimit'),
			'order' => array(
				'id' => 'DESC'
			)
		));
		
		return $tasks ? (int)round(array_sum($tasks) / count($tasks)) : 0;
	}
	
	/**
	 * Convert seconds to human readable format
	 * 
	 * @param int $seconds
	 * @return string
	 */
	protected function _secondsToHuman($seconds) {
		return (new DateTime('today'))->diff(new DateTime('today +' . (int)$seconds . ' seconds'))->format(Configure::read('Task.dateDiffFormat'));
	}

}
