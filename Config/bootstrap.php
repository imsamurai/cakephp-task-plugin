<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: Dec 23, 2013
 * Time: 5:54:38 PM
 * Format: http://book.cakephp.org/2.0/en/views.html
 * 
 * @package Task.Config
 */
/* @var $this View */

Configure::write('Pagination.pages', Configure::read('Pagination.pages') ? Configure::read('Pagination.pages') : 10);
Configure::write('Task', array(
	'checkInterval' => Configure::read('Task.checkInterval') ? Configure::read('Task.checkInterval') : 5,
	'stopTimeout' => Configure::read('Task.stopTimeout') ? Configure::read('Task.stopTimeout') : 5,
	'maxSlots' => Configure::read('Task.maxSlots') ? Configure::read('Task.maxSlots') : 16,
	'timeout' => Configure::read('Task.timeout') ? Configure::read('Task.timeout') : 60 * 60 * 8,
	'dateFormat' => Configure::read('Task.dateFormat') ? Configure::read('Task.dateFormat') : 'd.m.Y',
));
App::uses('TaskType', 'Task.Lib/Task');
App::uses('Sanitize', 'Utility');
