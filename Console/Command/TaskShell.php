<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 15.11.11
 * Time: 17:12
 * Format: http://book.cakephp.org/2.0/en/console-and-shells.html#creating-a-shell
 */

/**
 * @package Task.Console.Command
 */
class TaskShell extends Shell {

	public $tasks = array(
		'Task.TaskServer'
	);

	/**
	 * {@inheritdoc}
	 *
	 * @return ConsoleOptionParser
	 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		$parser->description('Task shell global options');

		foreach ($this->tasks as $task) {
			list(, $taskName) = pluginSplit($task);
			$parser->addSubcommand(Inflector::underscore($taskName), array(
				'help' => $this->{$taskName}->getOptionParser()->description(),
				'parser' => $this->{$taskName}->getOptionParser()
			));
		}
		return $parser;
	}

}
