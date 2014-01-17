<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 11.06.2013
 * Time: 9:07:59
 * Format: http://book.cakephp.org/2.0/en/models.html
 */
App::uses('TaskModel', 'Task.Model');

/**
 * @package Task.Model
 */
class TaskServer extends TaskModel {

	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	public $name = 'TaskServer';

	/**
	 * Returns number of free slots
	 *
	 * @return int
	 */
	public function freeSlots() {
		$maxSlots = (int)Configure::read('Task.maxSlots');
		if (!$maxSlots) {
			$maxSlots = 10;
		}
		$runnedCount = $this->find('count', array(
			'conditions' => array(
				'status' => array(TaskType::DEFFERED, TaskType::RUNNING, TaskType::STOPPING),
				'server_id' => $this->_serverId()
			)
		));
		return $maxSlots - (int)$runnedCount;
	}

	/**
	 * Returns task or false
	 *
	 * @return bool|array
	 */
	public function getPending() {
		$pendingId = $this->_getPendingId();

		if (!$pendingId) {
			return false;
		}

		$task = $this->find('first', array(
			'conditions' => array(
				'id' => $pendingId
			)
		));

		$task[$this->alias]['status'] = TaskType::DEFFERED;
		$task[$this->alias]['server_id'] = $this->_serverId();
		$this->save($task);
		return $task[$this->alias];
	}

	/**
	 * Must be called when task starts running
	 *
	 * @param array $task
	 *
	 * @return mixed
	 */
	public function started(array &$task) {
		$task['status'] = TaskType::RUNNING;
		return $this->save($task);
	}

	/**
	 * Must be called when process write output
	 *
	 * @param array $task
	 *
	 * @return mixed
	 */
	public function updated(array &$task) {
		return $this->save($task);
	}

	/**
	 * Must be called when task has been stopped
	 *
	 * @param array $task
	 * @param bool $manual True means process stopped manually
	 *
	 * @return mixed
	 */
	public function stopped(array &$task, $manual) {
		$task['status'] = $manual ? TaskType::STOPPED : TaskType::FINISHED;
		return $this->save($task);
	}

	/**
	 * Checks if task must be stopped
	 *
	 * @param int $taskId
	 *
	 * @return bool
	 */
	public function mustStop($taskId) {
		return $this->field('status', array('id' => $taskId)) == TaskType::STOPPING;
	}

	/**
	 * Returns first task id that can be run
	 *
	 * @return bool|int
	 */
	protected function _getPendingId() {
		$taskNumber = 0;
		while (true) {
			$this->contain(array(
				'DependsOnTask' => array(
					'id', 'status'
				)
					)
			);
			$taskCandidate = $this->find('first', array(
				'fields' => 'id',
				'conditions' => array(
					'status' => TaskType::UNSTARTED,
					'server_id' => 0
				),
				'order' => array(
					'created' => 'asc'
				),
				'offset' => $taskNumber
			));

			if (!$taskCandidate) {
				return false;
			}


			$waitForOtherTask = false;
			foreach ($taskCandidate['DependsOnTask'] as $DependsOnTask) {
				if (!in_array((int)$DependsOnTask['status'], array(TaskType::FINISHED, TaskType::STOPPED))) {
					$waitForOtherTask = true;
					break;
				}
			}
			if (!$waitForOtherTask) {
				return $taskCandidate[$this->alias]['id'];
			}

			$taskNumber++;
		}

		return false;
	}

	/**
	 * Returns current server id that unique while script runs
	 *
	 * @staticvar int $serverId
	 * @return int
	 */
	protected function _serverId() {
		static $serverId = null;
		if (is_null($serverId)) {
			$File = new File(TMP . 'task_server_id', true);
			$serverId = (int)$File->read();
			if (!$serverId) {
				$serverId = mt_rand();
				$File->write($serverId);
			}
			$File->close();
		}

		return $serverId;
	}

}
