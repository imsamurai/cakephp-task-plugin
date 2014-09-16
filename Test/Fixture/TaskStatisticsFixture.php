<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: Mar 31, 2014
 * Time: 6:25:11 PM
 */

/**
 * Task Statistics Fixture
 * 
 * @package TaskTest
 * @subpackage Test.Fixture
 */
class TaskStatisticsFixture extends CakeTestFixture {

	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	public $useDbConfig = 'test';

	/**
	 * Fields
	 *
	 * @var array
	 */
	public $fields = array(
		'id' => array('type' => 'biginteger', 'null' => false, 'default' => null, 'length' => 20, 'key' => 'primary'),
		'task_id' => array('type' => 'biginteger', 'null' => false, 'default' => null, 'length' => 20),
		'memory' => array('type' => 'float', 'null' => false, 'default' => 0.0, 'length' => '4,1'),
		'cpu' => array('type' => 'float', 'null' => false, 'default' => 0.0, 'length' => '4,1'),
		'status' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 10, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime'),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	/**
	 * Records
	 *
	 * @var array
	 */
	public $records = array();

}
