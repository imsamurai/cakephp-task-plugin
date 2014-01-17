<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 10.06.2013
 * Time: 17:25:07
 * Format: http://book.cakephp.org/2.0/en/models.html
 */
App::uses('TaskModel', 'Task.Model');

/**
 * @package Task.Model
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
		} else if ($dependsOnIds) {
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
	 * @return boolean True if success
	 * @throws NotFoundException If no such task found
	 */
	public function stop($taskId) {
		$task = $this->read(null, $taskId);

		if (!$task) {
			throw new NotFoundException("Task id=$taskId not found!");
		}

		switch ($task[$this->alias]['status']) {
			case TaskType::UNSTARTED: {
					return $this->saveField('status', TaskType::STOPPED);
				}
			case TaskType::RUNNING: {
					return $this->saveField('status', TaskType::STOPPING);
				}
			case TaskType::DEFFERED: {
					sleep(1);
					return $this->stop($taskId);
				}
			default: return false;
		}
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
