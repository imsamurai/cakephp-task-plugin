<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: Mar 31, 2014
 * Time: 6:25:11 PM
 */
App::uses('TaskFixture', 'Task.Test/Fixture');

/**
 * Task Profiler Fixture
 * 
 * @package TaskTest
 * @subpackage Test.Fixture
 */
class TaskProfilerFixture extends TaskFixture {

	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	public $name = 'Task';

	/**
	 * Records
	 *
	 * @var array
	 */
	public $records = array(
		0 => array(
			'command' => 'Console/cake segment generate',
			'arguments' => '',
			'status' => '0',
			'code_string' => 'OK',
			'stderr' => 'stderr', 'stdout' => '',
			'started' => '2014-09-07 11:08:04',
			'stopped' => '2014-09-07 11:08:54',
			'created' => '2014-09-06 12:51:30',
			'modified' => '2014-09-06 12:51:30',
			'id' => '1',
			'process_id' => '0'
		),
		1 => array(
			'command' => 'Console/cake segment generate',
			'arguments' => '',
			'status' => '1',
			'code_string' => 'OK',
			'stderr' => 'stderr', 'stdout' => '',
			'started' => '2014-09-07 11:09:03',
			'stopped' => '2014-09-07 11:09:59',
			'created' => '2014-09-06 12:51:30',
			'modified' => '2014-09-06 12:51:30',
			'id' => '2',
			'process_id' => '0'
		),
		2 => array(
			'command' => 'Console/cake segment generate',
			'arguments' => '',
			'status' => '2',
			'code_string' => 'OK',
			'stderr' => 'stderr', 'stdout' => '',
			'started' => '2014-09-07 11:10:03',
			'stopped' => '2014-09-07 11:10:49',
			'created' => '2014-09-06 12:51:30',
			'modified' => '2014-09-06 12:51:30',
			'id' => '3',
			'process_id' => '0'
		),
		3 => array(
			'command' => 'Console/cake segment generate',
			'arguments' => '',
			'status' => '3',
			'code_string' => 'OK',
			'stderr' => '', 'stdout' => 'stdout',
			'started' => '2014-09-07 11:11:04',
			'stopped' => '2014-09-07 11:11:54',
			'created' => '2014-09-06 12:51:30',
			'modified' => '2014-09-06 12:51:30',
			'id' => '4',
			'process_id' => '0'
		),
		4 => array(
			'command' => 'Console/cake segment generate',
			'arguments' => '',
			'status' => '4',
			'code_string' => 'OK',
			'stderr' => '', 'stdout' => '',
			'started' => '2014-09-07 11:12:03',
			'stopped' => '2014-09-07 11:13:09',
			'created' => '2014-09-06 12:51:30',
			'modified' => '2014-09-06 12:51:30',
			'id' => '5',
			'process_id' => '0'
		),
		5 => array(
			'command' => 'Console/cake segment generate',
			'arguments' => '',
			'status' => '5',
			'code_string' => 'OK',
			'stderr' => '', 'stdout' => '',
			'started' => '2014-09-07 11:12:03',
			'stopped' => '2014-09-07 11:13:09',
			'created' => '2014-09-06 12:51:30',
			'modified' => '2014-09-06 12:51:30',
			'id' => '6',
			'process_id' => '0'
		),
		6 => array(
			'command' => 'Console/cake clusterization_archive generate',
			'arguments' => '',
			'status' => '0',
			'code_string' => 'OK',
			'stderr' => '', 'stdout' => '',
			'started' => '2014-09-07 07:14:05',
			'stopped' => '2014-09-07 07:20:57',
			'created' => '2014-09-06 12:47:34',
			'modified' => '2014-09-06 12:47:34',
			'id' => '7',
			'process_id' => '0'
		),
		7 => array(
			'command' => 'Console/cake clusterization_archive generate',
			'arguments' => '',
			'status' => '1',
			'code_string' => 'OK',
			'stderr' => '', 'stdout' => '',
			'started' => '2014-09-07 07:21:05',
			'stopped' => '2014-09-07 07:26:02',
			'created' => '2014-09-06 12:47:34',
			'modified' => '2014-09-06 12:47:34',
			'id' => '8',
			'process_id' => '0'
		),
		8 => array(
			'command' => 'Console/cake clusterization_archive generate',
			'arguments' => '',
			'status' => '2',
			'code_string' => 'OK',
			'stderr' => '', 'stdout' => '',
			'started' => '2014-09-07 07:26:05',
			'stopped' => '2014-09-07 07:33:28',
			'created' => '2014-09-06 12:47:34',
			'modified' => '2014-09-06 12:47:34',
			'id' => '9',
			'process_id' => '0'
		),
		9 => array(
			'command' => 'Console/cake clusterization_archive generate',
			'arguments' => '',
			'status' => '3',
			'code_string' => 'OK',
			'stderr' => '', 'stdout' => '',
			'started' => '2014-09-07 07:34:06',
			'stopped' => '2014-09-07 07:41:33',
			'created' => '2014-09-06 12:47:34',
			'modified' => '2014-09-06 12:47:34',
			'id' => '10',
			'process_id' => '0'
		),
		10 => array(
			'command' => 'Console/cake clusterization_archive generate',
			'arguments' => '',
			'status' => '4',
			'code_string' => 'OK',
			'stderr' => '', 'stdout' => '',
			'started' => '2014-09-07 07:42:05',
			'stopped' => '2014-09-07 07:51:28',
			'created' => '2014-09-06 12:47:34',
			'modified' => '2014-09-06 12:47:34',
			'id' => '11',
			'process_id' => '0'
		),
		11 => array(
			'command' => 'Console/cake clusterization_archive generate',
			'arguments' => '',
			'status' => '5',
			'code_string' => 'OK',
			'stderr' => '', 'stdout' => '',
			'started' => '2014-09-07 07:52:05',
			'stopped' => '2014-09-07 08:02:32',
			'created' => '2014-09-06 12:47:34',
			'modified' => '2014-09-06 12:47:34',
			'id' => '12',
			'process_id' => '0'
		),
		12 => array(
			'command' => 'Console/cake clusterization_archive generate',
			'arguments' => '',
			'status' => '3',
			'code_string' => 'OK',
			'stderr' => '', 'stdout' => '',
			'started' => '2014-09-07 07:52:05',
			'stopped' => '2014-09-07 08:02:32',
			'created' => '2014-09-06 12:47:34',
			'modified' => '2014-09-06 12:47:34',
			'id' => '13',
			'process_id' => '0'
		),
		13 => array(
			'command' => 'Console/cake clusterization_archive generate',
			'arguments' => '',
			'status' => '3',
			'code_string' => 'OK',
			'stderr' => '', 'stdout' => '',
			'started' => '2014-09-07 07:42:05',
			'stopped' => '2014-09-07 07:51:28',
			'created' => '2014-09-06 12:47:34',
			'modified' => '2014-09-06 12:47:34',
			'id' => '14',
			'process_id' => '0'
		)
	);

}
