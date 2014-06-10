<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 10.06.2013
 * Time: 17:52:43
 * Format: http://book.cakephp.org/2.0/en/console-and-shells.html#shell-tasks
 */
App::uses('TaskRunner', 'Task.Lib/Task');

/**
 * @package Task.Console.Command.Task
 */
class TaskServerTask extends Shell {

	public $uses = array('Task.TaskServer', 'Task.TaskClient');

	/**
	 * {@inheritdoc}
	 *
	 * @return void
	 */
	public function execute() {
		$tasks = array();
		while ($this->TaskServer->freeSlots() > 0 && ($task = $this->TaskServer->getPending())) {
			$tasks[] = $task;
		}

		if (empty($tasks)) {
			return;
		}

		$events = (array)Configure::read('Task.processEvents');
		foreach ($events as $event) {
			CakeEventManager::instance()->attach(ClassRegistry::init($event['model']), $event['key'], $event['options']);
		}

		$ProcessManager = new Spork\ProcessManager();
		foreach ($tasks as $task) {
			$ProcessManager->fork(function () use ($task) {
				$TaskRunner = new TaskRunner($task, $this->TaskServer, $this->TaskClient);
				$TaskRunner->start();
			});
		}

		foreach ($events as $event) {
			CakeEventManager::instance()->detach(ClassRegistry::init($event['model']), $event['key'], $event['options']);
		}
	}

}
