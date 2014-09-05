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
	 * {@inheritdoc}
	 *
	 * @var array
	 */
	public $virtualFields = array(
		'errored' => '!ISNULL(stderr)',
		'runtime' => 'TIME_TO_SEC(TIMEDIFF(IFNULL(stopped, \'%s\'), started))',
		'waittime' => 'TIME_TO_SEC(TIMEDIFF(started, created))',
	);
	
	/**
	 * {@inheritdoc}
	 * 
	 * @param boolean|integer|string|array $id Set this ID for this model on startup,
	 * can also be an array of options, see above.
	 * @param string $table Name of database table to use.
	 * @param string $ds DataSource connection name.
	 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->virtualFields['runtime'] = sprintf($this->virtualFields['runtime'], date('Y-m-d H:i:s'));
	}

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
	 * @param int $retry Retry count
	 * @return boolean True if success
	 * @throws NotFoundException If no such task found
	 */
	public function stop($taskId, $retry = 0) {
		$task = $this->read(null, $taskId);

		if (!$task) {
			throw new NotFoundException("Task id=$taskId not found!");
		}

		switch ($task[$this->alias]['status']) {
			case TaskType::FINISHED:
			case TaskType::STOPPED: {
					return true;
				}
			case TaskType::UNSTARTED: {
					return $this->saveField('status', TaskType::STOPPED);
				}
			case TaskType::RUNNING:
			case TaskType::STOPPED: 
					return $this->saveField('status', TaskType::STOPPING);
				
			case TaskType::DEFFERED: {
					if ($retry >= 10) {
						return $this->saveField('status', TaskType::STOPPED);
					}
					sleep(1);
					return $this->stop($taskId, ++$retry);
				}
			default:
				return false;
		}
	}
	
	/**
	 * Restart task by id
	 * 
	 * @param int $taskId Unique task id
	 * @return bool True if success
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
			'stopped' => ''
				) + $this->read(null, $taskId)[$this->alias];
		$this->create();		
		return $this->saveAssociated(array(
					$this->alias => $task,
					$this->DependsOnTask->alias => array($taskId)
		));
	}
	
	/**
	 * Delete task by id
	 * 
	 * @param int $taskId Unique task id
	 * @return bool True if success
	 * @throws NotFoundException If no such task found
	 */
	public function remove($taskId) {
		if (!$this->stop($taskId)) {
			return false;
		}
		$this->id = $taskId;
		while (!in_array($this->field('status'), array(TaskType::STOPPED, TaskType::FINISHED))) {
			sleep(1);
		}

		return $this->delete($taskId);
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
