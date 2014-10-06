<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 15.11.11
 * Time: 17:12
 * Format: http://book.cakephp.org/2.0/en/console-and-shells.html#creating-a-shell
 */
App::uses('AdvancedShell', 'AdvancedShell.Console/Command');

/**
 * Task shell
 * 
 * @property TaskServerTask $Server
 * 
 * @package Task
 * @subpackage Console.Command
 */
class TaskShell extends AdvancedShell {

	/**
	 * {@inheritdoc}
	 *
	 * @var array
	 */
	public $tasks = array(
		'Server' => array(
			'className' => 'Task.TaskServer'
		),
		'TaskServer' => array(
			'className' => 'Task.TaskServerOld'
		)
	);

	/**
	 * {@inheritdoc}
	 *
	 * @return ConsoleOptionParser
	 */
	public function getOptionParser() {
		return parent::getOptionParser()
						->description('Task shell');
	}

}
