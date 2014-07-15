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
 * 
 * @package Task.Controller
 */
class TaskController extends TaskAppController {

	/**
	 * {@inheritdoc}
	 *
	 * @var array 
	 */
	public $uses = array('Task.TaskClient');
	
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

		$this->set(array(
			'commandList' => $this->TaskClient->find('list', array(
				'fields' => array('command', 'command'),
				'group' => array('command'),
			)),
			'statusList' => $this->TaskClient->find('list', array(
				'fields' => array('status', 'status'),
				'group' => array('status'),
			)),
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
		
		$this->set('task', $task['TaskClient']);
		$this->set('dependentTasks', $task['DependsOnTask']);
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
			$conditions[$dateRangeField . ' BETWEEN ? AND ?'] = explode(' to ', $conditions[$dateRangeField]);
			unset($conditions[$dateRangeField]);
		}
		return $conditions;
	}

}
