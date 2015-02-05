<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 11.06.2013
 * Time: 15:23:19
 * Format: http://book.cakephp.org/2.0/en/controllers.html
 */
App::uses('AppController', 'Controller');

/**
 * TasksController
 * 
 * @property TaskClient $TaskClient TaskClient model
 * @property TaskProfiler $TaskProfiler TaskProfiler model
 * @property TaskStatistics $TaskStatistics TaskStatistics model
 * 
 * @package Task
 * @subpackage Controller
 */
class TaskController extends AppController {

	/**
	 * {@inheritdoc}
	 *
	 * @var array 
	 */
	public $uses = array('Task.TaskClient', 'Task.TaskProfiler', 'Task.TaskStatistics');

	/**
	 * {@inheritdoc}
	 *
	 * @var array 
	 */
	public $helpers = array('Task.Task');

	/**
	 * Allowed actions for batch
	 *
	 * @var array
	 */
	public $batchActions = array('stop', 'restart', 'remove');

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
					'stderr_truncated',
					'started',
					'stopped',
					'created',
					'modified',
					'runtime',
					'id',
					'process_id'
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
			'batchActions' => array_combine($this->batchActions, $this->batchActions)
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
		$statistics = $this->TaskStatistics->find('all', array(
			'conditions' => array(
				'task_id' => $taskId
			),
			'group' => array('CEIL(memory)', 'CEIL(cpu)', 'status'),
			'order' => array(
				'created' => 'asc'
			)
		));
		$this->set('statistics', Hash::extract($statistics, '{n}.{s}'));
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
	 * Batch operations
	 * 
	 * @throws ForbiddenException
	 */
	public function batch() {
		$success = array();
		$action = $this->request->query('batch_action');

		if (!in_array($action, $this->batchActions, true)) {
			throw new ForbiddenException("Method '$action' not allowed");
		}
		
		if ($this->request->query('batch_conditions')) {
			$ids = array_values($this->TaskClient->find('list', array(
						'conditions' => $this->_paginationFilter()
			)));
		} else {
			$ids = array_filter((array)$this->request->query('ids'));
		}
		
		if ($ids) {
			foreach ($ids as $id) {
				$success[$id] = $this->TaskClient->{$action}($id);
			}
		}

		if (empty($success)) {
			$this->Session->setFlash("No ids specified", 'alert/simple', array(
				'class' => 'alert-warning', 'title' => 'Warning!'
			));
		} elseif (array_filter($success) == $success) {
			$this->Session->setFlash("Batch $action successfully applied to " . count($success) . " task(s)", 'alert/simple', array(
				'class' => 'alert-success', 'title' => 'Ok!'
			));
		} else {
			$failIds = array_keys(
					array_filter($success, function($a) {
						return !$a;
					})
			);
			$this->Session->setFlash(count($failIds) . " of " . count($success) . " operations was errored (with ids: " . implode(', ', $failIds) . ")", 'alert/simple', array(
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
		$conditions = array_filter($this->request->query, function($var) {
			return $var !== '';
		});
		unset($conditions['url'], $conditions['batch_conditions'], $conditions['batch_action']);
		foreach (array('started', 'created', 'stopped', 'modified') as $dateRangeField) {
			if (empty($conditions[$dateRangeField])) {
				continue;
			}
			if (preg_match('/^(?P<start>.*)\s(-|to)\s(?P<end>.*)$/is', $conditions[$dateRangeField], $range)) {
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
