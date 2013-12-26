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

		$this->set('commandList', $this->TaskClient->find('list', array(
					'fields' => array('command', 'command'),
					'group' => array('command'),
		)));

		$this->set('statuses', array(
			TaskType::UNSTARTED => array(
				'name' => 'new',
				'class' => ''
			),
			TaskType::DEFFERED => array(
				'name' => 'deffered',
				'class' => ''
			),
			TaskType::RUNNING => array(
				'name' => 'running',
				'class' => 'label-warning'
			),
			TaskType::FINISHED => array(
				'name' => 'finished',
				'class' => 'label-inverse'
			),
			TaskType::STOPPING => array(
				'name' => 'stopping',
				'class' => 'label-warning'
			),
			TaskType::STOPPED => array(
				'name' => 'stopped',
				'class' => 'label-danger'
			)
		));
	}

	/**
	 * View task
	 * 
	 * @param int $taskId
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
		$this->set($task);
	}
	
	/**
	 * Stop task
	 * 
	 * @param int $taskId
	 */
	public function stop($taskId) {
		$task = $this->TaskClient->read(null, $taskId);

		if (!$task) {
			throw new NotFoundException("Task id=$taskId not found!");
		}
	
		$success = $task[$this->TaskClient->alias]['process_id'] && $this->TaskClient->saveField('status', TaskType::STOPPING);
		
		if ($success) {
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
			$conditions[$dateRangeField.' BETWEEN ? AND ?'] = explode(' to ', $conditions[$dateRangeField]);
			unset($conditions[$dateRangeField]);
		}
		return $conditions;
	}

}
