<?php

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