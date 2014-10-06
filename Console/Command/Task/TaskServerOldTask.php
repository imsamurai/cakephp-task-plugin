<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 10.06.2013
 * Time: 17:52:43
 * Format: http://book.cakephp.org/2.0/en/console-and-shells.html#shell-tasks
 */
App::uses('TaskServerTask', 'Task.Console/Command/Task');

/**
 * Old task server script (for backward compatrability)
 * 
 * @package Task
 * @subpackage Console.Command.Task
 * @deprecated since 1.0.17
 */
class TaskServerOldTask extends TaskServerTask {

	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	public $name = 'TaskServer';

	/**
	 * {@inheritdoc}
	 *
	 * @return void
	 */
	public function execute() {
		$this->out('<warning>"Console/cake Task.task task_server" command is deprecated, use "Console/cake Task.task server"</warning>');
		return parent::execute();
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return ConsoleOptionParser
	 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		$parser->description('Old task server (deprecated)');
		return $parser;
	}

}
