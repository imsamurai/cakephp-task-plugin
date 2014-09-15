<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 11.09.2014
 * Time: 16:46:01
 * Format: http://book.cakephp.org/2.0/en/views.html
 */
Router::connect('/tasks', array('controller' => 'task', 'action' => 'index', 'plugin' => 'task'));
Router::connect('/tasks/view/*', array('controller' => 'task', 'action' => 'view', 'plugin' => 'task'));
Router::connect('/tasks/profile/*', array('controller' => 'task', 'action' => 'profile', 'plugin' => 'task'));
