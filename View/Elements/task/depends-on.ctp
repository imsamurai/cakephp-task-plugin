<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 18.06.2013
 * Time: 23:51:52
 * Format: http://book.cakephp.org/2.0/en/views.html
 *
 * @package Task.View
 */
/* @var $this View */

if (!$dependsOnTask) {
	echo 'none';
	return;
}

$dependsOnTaskFormatted = array();
foreach ($dependsOnTask as $task) {
	$name = "#{$task['id']}";
	if ((int) $task['status'] === TaskType::FINISHED) {
		$name = $this->Html->tag('s', $name);
	}
	$dependsOnTaskFormatted[] = $this->Html->link(
			$name, array('controller' => 'tasks', 'action' => 'view', $task['id']), array('escape' => false)
	);
}

echo implode(', ', $dependsOnTaskFormatted);
