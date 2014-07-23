<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 11.06.2013
 * Time: 15:23:19
 * Format: http://book.cakephp.org/2.0/en/controllers.html
 */
App::uses('TaskAppController', 'Task.Controller');

/**
 * TasksController
 * 
 * @property TaskClient $TaskClient TaskClient model
 * @property TaskProfiler $TaskProfiler TaskProfiler model
 * 
 * @package Task.Controller
 */
class TaskController extends TaskAppController {

	/**
	 * {@inheritdoc}
	 *
	 * @var array 
	 */
	public $uses = array('Task.TaskClient', 'Task.TaskProfiler');
	
	/**
	 * {@inheritdoc}
	 *
	 * @var array 
	 */
	public $helpers = array('Task.Task');

	/**
	 * View list of tasks
	 */
	public function index() {
		$this->request->data('Task', $this->request->query);
		$this->paginate = array(
			'TaskClient' => array(
				'limit' => Configure::read('Pagination.limit'),
				'fields' => array(
					'command',
					'arguments',
					'status',
					'code_string',
					'stderr',
					'started',
					'stopped',
					'created',
					'modified',
					'runtime',
					'id',
					'process_id',
				),
				'conditions' => $this->_paginationFilter(),
				'order' => array('created' => 'desc'),
				'contain' => array('DependsOnTask' => array('id', 'status'))
			)
		);
		$this->set(array(
			'data' => $this->paginate("TaskClient")
		));

		$commandList = $this->TaskClient->find('list', array(
			'fields' => array('command', 'command'),
			'group' => array('command'),
		));
		$statusList = $this->TaskClient->find('list', array(
			'fields' => array('status', 'status'),
			'group' => array('status'),
		));
		$approximateRuntimes = array_map(function($command) {
			return $this->TaskProfiler->approximateRuntime($command);
		}, $commandList);
		$this->set(array(
			'commandList' => $commandList,
			'statusList' => $statusList,
			'approximateRuntimes' => $approximateRuntimes,
		));
	}

	/**
	 * View task
	 * 
	 * @param int $taskId
	 * @throws NotFoundException If task not found
	 */
	public function view($taskId) {
		$task = $this->TaskClient->find('first', array(
			'conditions' => array(
				'id' => $taskId
			),
			'contain' => array(
				'DependsOnTask' => array(
					'id',
					'status'
				)
			)
		));

		if (!$task) {
			throw new NotFoundException("Task id=$taskId not found!");
		}
		
		$commandList = array($task['TaskClient']['command'] => $task['TaskClient']['command']);
		$approximateRuntimes = array_map(function($command) {
			return $this->TaskProfiler->approximateRuntime($command);
		}, $commandList);
		$this->set('task', $task['TaskClient']);
		$this->set('dependentTasks', $task['DependsOnTask']);
		$this->set('approximateRuntimes', $approximateRuntimes);
	}

	/**
	 * Stop task
	 * 
	 * @param int $taskId
	 */
	public function stop($taskId) {
		if ($this->TaskClient->stop($taskId)) {
			$this->Session->setFlash("Task $taskId will be stopped shortly", 'alert/simple', array(
				'class' => 'alert-success', 'title' => 'Ok!'
			));
		} else {
			$this->Session->setFlash("Can't stop task $taskId!", 'alert/simple', array(
				'class' => 'alert-error', 'title' => 'Error!'
			));
		}
		$this->redirect($this->referer());
	}
	
	/**
	 * Restart task
	 * 
	 * @param int $taskId
	 */
	public function restart($taskId) {
		if ($this->TaskClient->restart($taskId)) {
			$this->Session->setFlash("Task $taskId will be restarted shortly", 'alert/simple', array(
				'class' => 'alert-success', 'title' => 'Ok!'
			));
		} else {
			$this->Session->setFlash("Can't restart task $taskId!", 'alert/simple', array(
				'class' => 'alert-error', 'title' => 'Error!'
			));
		}
		$this->redirect($this->referer());
	}
	
	/**
	 * Delete task
	 * 
	 * @param int $taskId
	 */
	public function remove($taskId) {
		if ($this->TaskClient->remove($taskId)) {
			$this->Session->setFlash("Task $taskId deleted", 'alert/simple', array(
				'class' => 'alert-success', 'title' => 'Ok!'
			));
		} else {
			$this->Session->setFlash("Can't delete task $taskId!", 'alert/simple', array(
				'class' => 'alert-error', 'title' => 'Error!'
			));
		}
		$this->redirect($this->referer());
	}
	
	/**
	 * Profile task command
	 */
	public function profile() {
		$this->request->data('Task', $this->request->query);
		$command = $this->request->query('command');
		$this->set('data', $command ? $this->TaskProfiler->profileCommand($command) : null);
		$this->set(array(
			'commandList' => $this->TaskClient->find('list', array(
				'fields' => array('command', 'command'),
				'group' => array('command'),
			)),
		));
		if (CakePlugin::loaded('GoogleChart')) {
			$this->helpers[] = 'GoogleChart.GoogleChart';
		}
	}

	/**
	 * Builds pagination conditions from search form
	 * 
	 * @return array
	 */
	protected function _paginationFilter() {
		$conditions = array_filter($this->request->query);
		unset($conditions['url']);
		foreach (array('started', 'created', 'stopped', 'modified') as $dateRangeField) {
			if (empty($conditions[$dateRangeField])) {
				continue;
			}
			if (preg_match('/(?P<start>\S*)\s([\w-]*+)\s(?P<end>\S*)/', $conditions[$dateRangeField], $range)) {
				$conditions[$dateRangeField . ' BETWEEN ? AND ?'] = array(
					(new DateTime($range['start']))->format('Y-m-d H:i:s'),
					(new DateTime($range['end']))->format('Y-m-d H:i:s')
				);
			}
			unset($conditions[$dateRangeField]);
		}
		return $conditions;
	}

}
