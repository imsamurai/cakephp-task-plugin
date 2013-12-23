Cakephp Task Plugin
===================

Plugin for run deffered (scheduled) tasks (console scripts)

!Scheduled run is not available yet!


## Installation

	cd my_cake_app/app
	git clone git://github.com/imsamurai/cakephp-task-plugin.git Plugin/Task

or if you use git add as submodule:

	cd my_cake_app
	git submodule add "git://github.com/imsamurai/cakephp-task-plugin.git" "app/Plugin/Task"

then add plugin loading in Config/bootstrap.php

	CakePlugin::load('Task', array('bootstrap' => true));

add tables from `Config/Schema/tasks.sql`

include https://github.com/symfony/Process and https://github.com/kriswallsmith/spork in your project, for ex with composer (tested with 2.3 version)

## Configuration

Write global config if you need to use custom settings function:

	Configure::write('Task', array(
      //maximum runned tasks at the same time
	  'maxSlots' => <number of slots>
	));

## Usage

Use `TaskClient::add()` for adding new tasks
Put `Console/cake Task.task task_server` in the cron, for ex each 1-5 minutes (depends on your needs)
Try `http://yourdomain/task/` for basic view
