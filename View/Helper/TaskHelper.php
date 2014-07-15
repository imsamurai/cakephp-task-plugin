<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 15.07.2014
 * Time: 13:59:42
 * Format: http://book.cakephp.org/2.0/en/views/helpers.html
 */
App::uses('AppHelper', 'View/Helper');
App::uses('Sanitize', 'Utility');

/**
 * Task Helper
 * 
 * @package Task.Model
 * 
 * @property HtmlHelper $Html Html Helper
 * @property TimeHelper $Time Time Helper
 * @property TextHelper $Text Text Helper
 */
class TaskHelper extends AppHelper {

	/**
	 * Statuses
	 *
	 * @var array 
	 */
	public static $statuses = array(
		TaskType::UNSTARTED => array(
			'name' => 'new',
			'class' => 'label label-success'
		),
		TaskType::DEFFERED => array(
			'name' => 'deffered',
			'class' => ''
		),
		TaskType::RUNNING => array(
			'name' => 'running',
			'class' => 'label-warning'
		),
		TaskType::FINISHED => array(
			'name' => 'finished',
			'class' => 'label-inverse'
		),
		TaskType::STOPPING => array(
			'name' => 'stopping',
			'class' => 'label-info'
		),
		TaskType::STOPPED => array(
			'name' => 'stopped',
			'class' => 'label-important'
		)
	);

	/**
	 * {@inheritdoc}
	 *
	 * @var array 
	 */
	public $helpers = array('Html', 'Time', 'Text');

	/**
	 * True if is cli run
	 *
	 * @var bool
	 */
	protected $_isCli = false;

	/**
	 * Constructor
	 * 
	 * @param View $View
	 * @param array $settings
	 */
	public function __construct(\View $View, $settings = array()) {
		parent::__construct($View, $settings);
		$this->_isCli = isset($settings['cli']) ? $settings['cli'] : (php_sapi_name() === 'cli');
	}

	/**
	 * Id
	 * 
	 * @param array $task
	 * @return string
	 */
	public function id(array $task) {
		return $this->_isCli ? $task['id'] : $this->Html->link($task['id'], array('action' => 'view', $task['id']));
	}

	/**
	 * Command
	 * 
	 * @param array $task
	 * @return string
	 */
	public function command(array $task) {
		return $task['command'];
	}

	/**
	 * Process id
	 * 
	 * @param array $task
	 * @return string
	 */
	public function processId(array $task) {
		return $task['process_id'] ? $task['process_id'] : $this->_none();
	}

	/**
	 * Running
	 * 
	 * @param array $task
	 * @return string
	 */
	public function running(array $task) {
		return $this->_dateDiff($task['started'], $task['stopped']);
	}

	/**
	 * Created
	 * 
	 * @param array $task
	 * @return string
	 */
	public function created(array $task) {
		return $this->_date($task['created']);
	}

	/**
	 * Modified
	 * 
	 * @param array $task
	 * @return string
	 */
	public function modified(array $task) {
		return $this->_date($task['modified']);
	}

	/**
	 * Started
	 * 
	 * @param array $task
	 * @return string
	 */
	public function started(array $task) {
		return $this->_date($task['started']);
	}

	/**
	 * Stopped
	 * 
	 * @param array $task
	 * @return string
	 */
	public function stopped(array $task) {
		return $this->_date($task['stopped']);
	}

	/**
	 * Errors
	 * 
	 * @param array $task
	 * @param bool $full If true output will be not truncate
	 * @return string
	 */
	public function stderr(array $task, $full = true) {
		return $this->_text($task['stderr'], $full ? 0 : Configure::read('Task.truncateError'));
	}

	/**
	 * Output
	 * 
	 * @param array $task
	 * @param bool $full If true output will be not truncate
	 * @return string
	 */
	public function stdout(array $task, $full = true) {
		return $this->_text($task['stdout'], $full ? 0 : Configure::read('Task.truncateOutput'));
	}

	/**
	 * String return code
	 * 
	 * @param array $task
	 * @return string
	 */
	public function codeString(array $task, $full = true) {
		return $this->_isCli || (!$task['code_string']) ? $task['code_string'] : $this->Html->tag('span', $this->_text($task['code_string'], $full ? 0 : Configure::read('Task.truncateCode'), true), array(
			'class' => 'label label-' . ($task['code_string'] == 'OK' ? 'success' : 'important'
				)));
	}

	/**
	 * Return code
	 * 
	 * @param array $task
	 * @return string
	 */
	public function code(array $task) {
		return $task['code'];
	}

	/**
	 * Hash
	 * 
	 * @param array $task
	 * @return string
	 */
	public function hash(array $task) {
		return $task['hash'];
	}

	/**
	 * Execution path
	 * 
	 * @param array $task
	 * @return string
	 */
	public function path(array $task) {
		return $task['path'];
	}

	/**
	 * Server id
	 * 
	 * @param array $task
	 * @return string
	 */
	public function serverId(array $task) {
		return $task['server_id'];
	}

	/**
	 * Timeout
	 * 
	 * @param array $task
	 * @return string
	 */
	public function timeout(array $task) {
		return $this->_dateDiff(new DateTime('today'), new DateTime("today +{$task['timeout']} seconds"));
	}

	/**
	 * Details
	 * 
	 * @param array $task
	 * @return string
	 */
	public function details(array $task) {
		return implode(' ,', Hash::flatten($task['details']));
	}

	/**
	 * Status
	 * 
	 * @param array $task
	 * @return string
	 */
	public function status(array $task) {
		$name = static::$statuses[$task['status']]['name'];
		return $this->_isCli ? $name : $this->Html->tag('span', $name, array('class' => 'label ' . static::$statuses[$task['status']]['class']));
	}

	/**
	 * Execution arguments
	 * 
	 * @param array $task
	 * @param bool $full
	 * @return string
	 */
	public function arguments(array $task, $full = true) {
		$arguments = '';
		foreach ($task['arguments'] as $name => $value) {
			if (!is_numeric($name)) {
				$arguments .= ' ' . $name;
			}
			$arguments .= ' ' . $value;
		}
		return $this->_text($arguments, $full ? 0 : Configure::read('Task.truncateArguments'), true);
	}

	/**
	 * Waiting for tasks
	 * 
	 * @param array $tasks
	 * @return string
	 */
	public function waiting(array $tasks) {
		$formattedTasks = $this->_formatWaiting($tasks, true);
		$tasksCount = count($formattedTasks);
		if ($tasksCount === 0) {
			return $this->_none();
		}

		if ($this->_isCli) {
			return implode(', ', $formattedTasks);
		} else {
			$text = implode(', ', $this->_formatWaiting(array_slice($tasks, 0, Configure::read('Task.truncateWaitFor')), false));
			$text .= $tasksCount > Configure::read('Task.truncateWaitFor') ? '...' : '';
			return $this->Html->tag('span', $text, array(
						'title' => implode(', ', $formattedTasks)
			));
		}
	}

	/**
	 * Waiting tasks formatter
	 * 
	 * @param array $tasks
	 * @param bool $plain
	 * @return array
	 */
	protected function _formatWaiting(array $tasks, $plain) {
		$dependsOnTaskFormatted = array();
		foreach ($tasks as $task) {
			if (!in_array((int)$task['status'], array(TaskType::DEFFERED, TaskType::RUNNING, TaskType::STOPPING))) {
				continue;
			}
			$dependsOnTaskFormatted[] = $plain ? $task['id'] : $this->id($task);
		}
		return $dependsOnTaskFormatted;
	}

	/**
	 * Date formatter
	 * 
	 * @param string $date
	 * @return string
	 */
	protected function _date($date = null) {
		$timeAgoInWords = $date ? $this->Time->timeAgoInWords($date, array('format' => Configure::read('Task.dateFormat'))) : $this->_none();
		return $this->_isCli ? $timeAgoInWords : $this->Html->tag('span', $timeAgoInWords, array('title' => $date));
	}

	/**
	 * Date diff formatter
	 * 
	 * @param DateTime|string $start
	 * @param DateTime|string $stop
	 * @return string
	 */
	protected function _dateDiff($start = null, $stop = null) {
		if ($start) {
			$endDate = $stop instanceof DateTime ? $stop : new DateTime($stop ? $stop : 'now');
			$startDate = $start instanceof DateTime ? $start : new DateTime($start);
			$diff = $startDate->diff($endDate)->format(Configure::read('Task.dateDiffFormat'));
			return $this->_isCli ? $diff : $this->Html->tag('span', $diff);
		} else {
			return $this->_none();
		}
	}

	/**
	 * Text formatter
	 * 
	 * @param string $text
	 * @param int $length
	 * @param bool $title
	 * @return string
	 */
	protected function _text($text, $length, $title = false) {
		$textSanitized = Sanitize::html(preg_replace('/(\[[0-9;]{1,}m)/ims', '', $text));
		if ($title && !$this->_isCli) {
			$textSanitized = $this->Html->tag('span', $textSanitized, array(
				'title' => $text
			));
		}
		return $length ? $this->Text->truncate($textSanitized, $length, array(
					'exact' => true, 'html' => true
				)) : $textSanitized;
	}

	/**
	 * None formatter
	 * 
	 * @param string $text
	 * @return string
	 */
	protected function _none($text = 'none') {
		return $this->_isCli ? $text : $this->Html->tag('span', $text, array('class' => 'task-not-yet', 'style' => 'color: gray;'));
	}

}
