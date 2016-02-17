<?php
/**
 * Author: imsamurai <im.samuray@gmail.com>
 */

class TaskSchema extends CakeSchema {

	public function before($event = array()) {
		return true;
	}

	public function after($event = array()) {
	}

	public $dependent_tasks = array(
		'task_id' => array('type' => 'biginteger', 'null' => false, 'default' => null, 'unsigned' => true, 'key' => 'primary'),
		'depends_on_task_id' => array('type' => 'biginteger', 'null' => false, 'default' => null, 'unsigned' => true),
		'indexes' => array(
			'task_id_depends_on_task_id' => array('column' => array('task_id','depends_on_task_id'), 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);
	
	public $task_statistics = array(
		'id' => array('type' => 'biginteger', 'null' => false, 'default' => null, 'unsigned' => true, 'key' => 'primary'),
		'task_id' => array('type' => 'biginteger', 'null' => false, 'default' => null, 'unsigned' => true, 'key' => 'index'),
		'memory' => array('type' => 'decimal', 'null' => true, 'default' => '0.0', 'length' => '4,1', 'unsigned' => false),
		'cpu' => array('type' => 'decimal', 'null' => true, 'default' => '0.0', 'length' => '4,1', 'unsigned' => false),
		'status' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 10, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'id_UNIQUE' => array('column' => 'id', 'unique' => 1),
			'task_id' => array('column' => 'task_id', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);
	
	public $tasks = array(
		'id' => array('type' => 'biginteger', 'null' => false, 'default' => null, 'unsigned' => true, 'key' => 'primary'),
		'process_id' => array('type' => 'biginteger', 'null' => true, 'default' => '0', 'unsigned' => true),
		'server_id' => array('type' => 'biginteger', 'null' => true, 'default' => '0', 'unsigned' => false),
		'command' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 500, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'path' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 500, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'arguments' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'hash' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'status' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 3, 'unsigned' => true, 'comment' => '0 - new
1 - deffered
2 - runned
3 - finished
4 - stopping
5 - stopped'),
		'code' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 10, 'unsigned' => false, 'comment' => '0 - ok
other - error code'),
		'code_string' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 500, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'stdout' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'stderr' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'details' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'timeout' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 10, 'unsigned' => true),
		'scheduled' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'started' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'stopped' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'id_UNIQUE' => array('column' => 'id', 'unique' => 1),
			'created' => array('column' => 'created', 'unique' => 0),
			'status-command' => array('column' => array('status','command'), 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

}
