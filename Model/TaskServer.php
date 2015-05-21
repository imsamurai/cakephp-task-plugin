<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 11.06.2013
 * Time: 9:07:59
 * Format: http://book.cakephp.org/2.0/en/models.html
 */
App::uses('TaskModel', 'Task.Model');

/**
 * Task server model
 * 
 * @package Task
 * @subpackage Model
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
		$savedTask = $this->save($task);
		$event = new CakeEvent('Task.taskStarted', $this, $savedTask);
		$this->getEventManager()->dispatch($event);
		return $savedTask;
	}

	/**
	 * Must be called when process write output
	 *
	 * @param array $task
	 *
	 * @return mixed
	 */
	public function updated(array &$task) {
		$savedTask = $this->save($task);
		$event = new CakeEvent('Task.taskUpdated', $this, $savedTask);
		$this->getEventManager()->dispatch($event);
		return $savedTask;
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
		$savedTask = $this->save($task);
		$event = new CakeEvent('Task.taskStopped', $this, $savedTask);
		$this->getEventManager()->dispatch($event);
		return $savedTask;
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
	 * Kill zombie processes
	 * 
	 * @return boolean True on success, false on failure
	 */
	public function killZombies() {
		return $this->updateAll(array(
			'status' => TaskType::STOPPED,
			'code' => 1,
			'code_string' => "'zombie'"
				), array(
			'OR' => array(
				array(
					'status' => TaskType::RUNNING,
					'process_id' => '0',
					'modified_since >=' => Configure::read('Task.zombieTimeout')
				),
				array(
					'status' => array(TaskType::STOPPING, TaskType::DEFFERED),
					'modified_since >=' => Configure::read('Task.zombieTimeout')
				),
				array(
					'status' => TaskType::RUNNING,
					"if((@a:= {$this->virtualFields['runtime']}) >= timeout,  @a - timeout, 0) >=" => Configure::read('Task.zombieTimeout')
				)
			)
		));
	}
	
	/**
	 * Update task process statistics
	 * 
	 * @param array $task
	 * @return bool
	 */
	public function updateStatistics(array $task) {
		if (!$task['process_id'] || !$task['id']) {
			return;
		}
		$statistics = array_values(array_filter(preg_split('/\s+/', `ps -o '%mem,%cpu,stat' --ppid {$task['process_id']} --no-headers`)));
		if (!$statistics) {
			$statistics = array_values(array_filter(preg_split('/\s+/', `ps -o '%mem,%cpu,stat' --pid {$task['process_id']} --no-headers`)));
		}
		$this->Statistics->create();
		return (bool)$this->Statistics->save(array(
			'task_id' => $task['id'],
			'memory' => (float)$statistics[0],
			'cpu' => (float)$statistics[1],
			'status' => (string)$statistics[2]
		));
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
					'server_id' => array(0, $this->_serverId())
				),
				'order' => array(
					'id' => 'asc'
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
