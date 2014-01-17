<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 12.10.2012
 * Time: 13:44:28
 *
 */
App::uses('Type', 'Task.Lib/Task');

final class TaskType extends Type {

	const _DEFAULT = TaskType::UNSTARTED;
	const UNSTARTED = 0;
	const DEFFERED = 1;
	const RUNNING = 2;
	const FINISHED = 3;
	const STOPPING = 4;
	const STOPPED = 5;

}
