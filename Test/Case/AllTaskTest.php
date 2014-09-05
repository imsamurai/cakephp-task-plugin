<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 29.10.2013
 * Time: 21:50:00
 */

/**
 * All task test suite
 * 
 * @package TaskTest
 * @subpackage Test
 */
class AllTaskTest extends PHPUnit_Framework_TestSuite {

	/**
	 * Suite define the tests for this suite
	 *
	 * @return void
	 */
	public static function suite() {
		$suite = new CakeTestSuite('All Task Tests');

		$path = App::pluginPath('Task') . 'Test' . DS . 'Case' . DS;
		$suite->addTestDirectoryRecursive($path);
		return $suite;
	}

}
