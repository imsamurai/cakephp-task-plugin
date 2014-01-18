<?php

/**
 * DependentTask Fixture
 */
class DependentTaskFixture extends CakeTestFixture {

	public $useDbConfig = 'test';

	/**
	 * Fields
	 *
	 * @var array
	 */
	public $fields = array(
		'task_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 20),
		'depends_on_task_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 20),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	/**
	 * Records
	 *
	 * @var array
	 */
	public $records = array();

}
