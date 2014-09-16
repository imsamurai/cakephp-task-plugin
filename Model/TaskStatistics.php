<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 11.06.2013
 * Time: 9:07:59
 * Format: http://book.cakephp.org/2.0/en/models.html
 */
App::uses('AppModel', 'Model');

/**
 * Task statistics model
 * 
 * @package Task
 * @subpackage Model
 */
class TaskStatistics extends AppModel {

	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	public $name = 'TaskStatistics';

	/**
	 * {@inheritdoc}
	 *
	 * @var int
	 */
	public $recursive = -1;

	/**
	 * {@inheritdoc}
	 *
	 * @var array
	 */
	public $belongsTo = array(
		'TaskServer' => array(
			'className' => 'Task.TaskServer',
			'dependent' => true
		),
		'TaskClient' => array(
			'className' => 'Task.TaskClient',
			'dependent' => true
		),
	);

}
