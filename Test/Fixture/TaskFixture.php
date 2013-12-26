<?php

/**
 * Task Fixture
 */
class TaskFixture extends CakeTestFixture {

	public $useDbConfig = 'test';
	/**
	 * Fields
	 *
	 * @var array
	 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 20, 'key' => 'primary'),
		'process_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 10),
		'server_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 10),
		'command' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 500, 'collate' => 'utf8_general_ci'),
		'path' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 500, 'collate' => 'utf8_general_ci'),
		'arguments' => array('type' => 'string', 'null' => false, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'hash' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 100, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'status' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 3, 'collate' => 'utf8_general_ci'),
		'code' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 10),
		'code_string' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' =>500, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'stdout' => array('type' => 'string', 'null' => false, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'stderr' => array('type' => 'string', 'null' => false, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'details' => array('type' => 'string', 'null' => false, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
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
