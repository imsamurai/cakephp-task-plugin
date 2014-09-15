<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 10.06.2013
 * Time: 17:52:43
 * Format: http://book.cakephp.org/2.0/en/console-and-shells.html#shell-tasks
 */
App::uses('TaskRunner', 'Task.Lib/Task');
App::uses('AdvancedTask', 'AdvancedShell.Console/Command/Task');

/**
 * Task server script
 * 
 * @property TaskServer $TaskServer TaskServer model
 * @property TaskClient $TaskClient TaskClient model
 * 
 * @package Task
 * @subpackage Console.Command.Task
 */
class TaskServerTask extends AdvancedTask {

	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	public $name = 'Server';
	
	/**
	 * {@inheritdoc}
	 *
	 * @var array
	 */
	public $uses = array('Task.TaskServer', 'Task.TaskClient');

	/**
	 * Process manager
	 *
	 * @var Spork\ProcessManager 
	 */
	public $ProcessManager = null;

	/**
	 * {@inheritdoc}
	 * 
	 * @param ConsoleOutput $stdout
	 * @param ConsoleOutput $stderr
	 * @param ConsoleInput $stdin
	 */
	public function __construct($stdout = null, $stderr = null, $stdin = null) {
		parent::__construct($stdout, $stderr, $stdin);
		$this->ProcessManager = new Spork\ProcessManager();
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return void
	 */
	public function execute() {
		$tasks = array();
		$this->TaskServer->killZombies();
		while ($this->TaskServer->freeSlots() > 0 && ($task = $this->TaskServer->getPending())) {
			$tasks[] = $task;
		}

		if (empty($tasks)) {
			return;
		}

		$events = (array)Configure::read('Task.processEvents');
		
		$models = array();
		foreach ($events as $event) {
			$models[$event['model']] = ClassRegistry::init($event['model']);
			CakeEventManager::instance()->attach($models[$event['model']], $event['key'], $event['options']);
		}

		foreach ($tasks as $task) {
			$this->_run($task);
		}

		foreach ($events as $event) {
			CakeEventManager::instance()->detach($models[$event['model']], $event['key'], $event['options']);
		}
	}
	
	/**
	 * {@inheritdoc}
	 *
	 * @return ConsoleOptionParser
	 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		$parser->description('Task server');
		return $parser;
	}

	/**
	 * Run task
	 * 
	 * @param array $task
	 */
	protected function _run(array $task) {
		$this->ProcessManager->fork(function () use ($task) {
			$this->_makeRunner($task)->start();
		});
	}
	
	/**
	 * Make task runner
	 * 
	 * @param array $task
	 * @return \TaskRunner
	 */
	protected function _makeRunner(array $task) {
		return new TaskRunner($task, $this->TaskServer, $this->TaskClient);
	}

}
