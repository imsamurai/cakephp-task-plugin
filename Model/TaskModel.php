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
	 * @var array
	 */
	public $virtualFields = array(
		'errored' => 'stderr!=""',
		'runtime' => 'TIME_TO_SEC(TIMEDIFF(IFNULL(stopped, \'%s\'), started))',
		'waittime' => 'TIME_TO_SEC(TIMEDIFF(started, created))',
		'modified_since' => 'TIME_TO_SEC(TIMEDIFF(NOW(), modified))',
	);

	/**
	 * {@inheritdoc}
	 *
	 * @var bool
	 */
	public $recursive = false;

	/**
	 * {@inheritdoc}
	 * 
	 * @param boolean|integer|string|array $id Set this ID for this model on startup,
	 * can also be an array of options, see above.
	 * @param string $table Name of database table to use.
	 * @param string $ds DataSource connection name.
	 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->virtualFields['runtime'] = sprintf($this->virtualFields['runtime'], date('Y-m-d H:i:s'));
	}

}
