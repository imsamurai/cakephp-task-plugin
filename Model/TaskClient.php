<?

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 10.06.2013
 * Time: 17:25:07
 * Format: http://book.cakephp.org/2.0/en/models.html
 */
App::uses('TaskModel', 'Task.Model');

/**
 * @package Task.Model
 */
class TaskClient extends TaskModel {

	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	public $name = 'Task';

	/**
	 * Adds new task
	 *
	 * @param string $command
	 * @param string $path
	 * @param array $arguments
	 * @param array $options
	 * @return bool
	 */
	public function add($command, $path, array $arguments = array(), array $options = array()) {
		$task = compact('command', 'path', 'arguments') + $options;
		$task += array(
			'timeout' => '4 hours',
			'status' => 0,
			'code' => 0,
			'stdout' => '',
			'stderr' => '',
			'details' => array(),
			'sceduled' => null
		);

		$timeoutInterval = DateInterval::createFromDateString($task['timeout']);
		$task['timeout'] = $timeoutInterval->s;

		return $this->save($task);
	}

}