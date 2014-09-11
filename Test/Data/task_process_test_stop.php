<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 09.09.2014
 * Time: 16:34:21
 * Format: http://book.cakephp.org/2.0/en/views.html
 */

define('FILE_NAME', $argv[1]);
file_put_contents(FILE_NAME, '');

/**
 * Work emulation
 */
function run() {
	sleep(10);
}

/**
 * Save pid
 */
function savePid() {
	file_put_contents(FILE_NAME, posix_getpid() . "\n", FILE_APPEND);
}

savePid();

$f = pcntl_fork();

if ($f === 0) {
	savePid();
	run();
} else {
	run();
}