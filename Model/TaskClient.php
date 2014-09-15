<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 10.06.2013
 * Time: 17:25:07
 * Format: http://book.cakephp.org/2.0/en/models.html
 */
App::uses('TaskModel', 'Task.Model');

/**
 * Task client model
 * 
 * @package Task
 * @subpackage Model
 */
class TaskClient extends TaskModel {

	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	public $name = 'Task';

	/**
	 * Adds new task
	 *
	 * @param string $command
	 * @param string $path
	 * @param array $arguments
	 * @param array $options
	 * - `unique` - if true and found active duplicates wont add new task
	 * - `dependsOn` - an array of task ids that must be done before this task starts
	 * @return bool
	 */
	public function add($command, $path, array $arguments = array(), array $options = array()) {
		$dependsOn = (array)Hash::get($options, 'dependsOn');
		$unique = (bool)Hash::get($options, 'unique');
		unset($options['dependsOn'], $options['unique']);

		$task = compact('command', 'path', 'arguments') + $options;
		$task += array(
			'timeout' => 60 * 60,
			'status' => TaskType::UNSTARTED,
			'code' => 0,
			'stdout' => '',
			'stderr' => '',
			'details' => array(),
			'server_id' => 0,
			'scheduled' => null,
			'hash' => $this->_hash($command, $path, $arguments)
		);

		$dependsOnIds = $this->find('list', array(
			'fields' => array('id', 'id'),
			'conditions' => array(
				'hash' => $task['hash'],
				'status' => array(TaskType::UNSTARTED, TaskType::DEFFERED, TaskType::RUNNING)
			)
		));

		if ($unique && $dependsOnIds) {
			return false;
		} elseif ($dependsOnIds) {
			$dependsOn = array_merge($dependsOn, $dependsOnIds);
		}

		$this->create();
		if ($dependsOn) {
			$data = array(
				$this->alias => $task,
				$this->DependsOnTask->alias => $dependsOn
			);
			$success = $this->saveAssociated($data);
		} else {
			$success = $this->save($task);
		}

		if (!$success) {
			return false;
		} else {
			return $this->read()[$this->alias];
		}
	}

	/**
	 * Stop task by id
	 * 
	 * @param int $taskId Unique task id
	 * @param int $maxRetries Maximum retry count
	 * @return boolean True if success
	 * @throws NotFoundException If no such task found
	 */
	public function stop($taskId, $maxRetries = 10) {
		$task = $this->read(null, $taskId);

		if (!$task) {
			throw new NotFoundException("Task id=$taskId not found!");
		}

		switch ($task[$this->alias]['status']) {
			case TaskType::FINISHED:
			case TaskType::STOPPING: 
			case TaskType::STOPPED: {
					return true;
				}
			case TaskType::UNSTARTED: {
					return (bool)$this->saveField('status', TaskType::STOPPED);
				}
			case TaskType::RUNNING:
					return (bool)$this->saveField('status', TaskType::STOPPING);
				
			case TaskType::DEFFERED: {
					if ($maxRetries <= 0) {
						return (bool)$this->saveField('status', TaskType::STOPPED);
					}
					sleep(1);
					return $this->stop($taskId, --$maxRetries);
				}
			default:
				return false;
		}
	}
	
	/**
	 * Restart task by id
	 * 
	 * @param int $taskId Unique task id
	 * @return int|false New task id on success, else false
	 * @throws NotFoundException If no such task found
	 */
	public function restart($taskId) {
		if (!$this->stop($taskId)) {
			return false;
		}

		$task = array(
			'id' => null,
			'stderr' => '',
			'stdout' => '',
			'status' => TaskType::UNSTARTED,
			'code' => '',
			'code_string' => '',
			'started' => '',
			'stopped' => '',
			'server_id' => 0,
				) + $this->read(null, $taskId)[$this->alias];
		$this->create();		
		$success = (bool)$this->saveAssociated(array(
					$this->alias => $task,
					$this->DependsOnTask->alias => array($taskId)
		));
		return $success ? $this->id : $success;
	}
	
	/**
	 * Delete task by id
	 * 
	 * @param int $taskId Unique task id
	 * @param int $maxRetries Maximum retry count
	 * @return bool True if success
	 * @throws NotFoundException If no such task found
	 */
	public function remove($taskId, $maxRetries = 10) {
		if (!$this->stop($taskId, $maxRetries)) {
			return false;
		}

		return (bool)$this->delete($taskId);
	}

	/**
	 * Unique hash of the command
	 *
	 * @param string $command
	 * @param string $path
	 * @param array $arguments
	 * @return type
	 */
	protected function _hash($command, $path, array $arguments = array()) {
		return md5($path . $command . serialize($arguments));
	}

}
