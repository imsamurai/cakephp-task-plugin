<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: Dec 23, 2013
 * Time: 5:54:38 PM
 * Format: http://book.cakephp.org/2.0/en/views.html
 */

Configure::write('Pagination.pages', Configure::read('Pagination.pages') ? Configure::read('Pagination.pages') : 10);
$config = (array)Configure::read('Task');
$config += array(
	'checkInterval' => 5,
	'stopTimeout' => 5,
	'maxSlots' => 16,
	'timeout' => 60 * 60 * 8,
	'dateFormat' => 'd.m.Y',
	'dateDiffFormat' => "%a days, %h hours, %i minutes",
	'dateDiffBarFormat' => "%ad, %hh, %im, %ss",
	'truncateError' => 200,
	'truncateOutput' => 500,
	'truncateArguments' => 100,
	'truncateCode' => 5,
	'truncateWaitFor' => 5,
	'profilerLimit' => 100,
	'approximateLimit' => 10,
	'zombieTimeout' => 60,
);
Configure::write('Task', $config);
App::uses('TaskType', 'Task.Lib/Task');
App::uses('Sanitize', 'Utility');
