<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 12.10.2012
 * Time: 13:44:28
 *
 */

/**
 * Task types enum
 * 
 * @package Task
 * @subpackage Task
 */
abstract class TaskType {

	/**
	 * Types
	 */
	const UNSTARTED = 0;
	const DEFFERED = 1;
	const RUNNING = 2;
	const FINISHED = 3;
	const STOPPING = 4;
	const STOPPED = 5;

	/**
	 * Get all types as array
	 *
	 * @return array
	 */
	public static function getTypes() {
		$class = new ReflectionClass(get_called_class());
		return $class->getConstants();
	}

}
