<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: Mar 31, 2014
 * Time: 6:25:11 PM
 */

/**
 * Task Fixture
 * 
 * @package TaskTest
 * @subpackage Test.Fixture
 */
class TaskFixture extends CakeTestFixture {

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
		'process_id' => array('type' => 'biginteger', 'null' => false, 'default' => '0', 'length' => 20),
		'server_id' => array('type' => 'biginteger', 'null' => false, 'default' => '0', 'length' => 20),
		'command' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 500, 'collate' => 'utf8_general_ci'),
		'path' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 500, 'collate' => 'utf8_general_ci'),
		'arguments' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 1000, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'statistics' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 100000, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'hash' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'status' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 3, 'collate' => 'utf8_general_ci'),
		'code' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 10),
		'code_string' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 500, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'stdout' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'stderr' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'details' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 1000, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'timeout' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 10, 'collate' => 'utf8_general_ci'),
		'scheduled' => array('type' => 'datetime'),
		'started' => array('type' => 'datetime'),
		'stopped' => array('type' => 'datetime'),
		'created' => array('type' => 'datetime'),
		'modified' => array('type' => 'datetime'),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	/**
	 * Records
	 *
	 * @var array
	 */
	public $records = array();

}
