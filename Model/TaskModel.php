<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 11.06.2013
 * Time: 9:51:57
 * Format: http://book.cakephp.org/2.0/en/models.html
 */
App::uses('TaskType', 'Task.Lib/Task');

/**
 * Task base model
 * 
 * @package Task
 * @subpackage Model
 */
class TaskModel extends AppModel {

	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	public $name = 'TaskModel';

	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	public $useTable = 'tasks';

	/**
	 * {@inheritdoc}
	 *
	 * @var array
	 */
	public $actsAs = array(
		'Containable',
		'Serializable.Serializable' => array(
			'fields' => array('details', 'arguments')
		)
	);

	/**
	 * {@inheritdoc}
	 *
	 * @var array
	 */
	public $hasAndBelongsToMany = array(
		'DependsOnTask' => array(
			'className' => 'TaskModel',
			'joinTable' => 'dependent_tasks',
			'foreignKey' => 'task_id',
			'associationForeignKey' => 'depends_on_task_id'
		)
	);

	/**
	 * {@inheritdoc}
	 *
	 * @var bool
	 */
	public $recursive = false;

}
