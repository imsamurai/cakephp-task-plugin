<?

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 11.06.2013
 * Time: 9:07:59
 * Format: http://book.cakephp.org/2.0/en/models.html
 */
App::uses('TaskModel', 'Task.Model');

/**
 * @package Task.Model
 */
class TaskServer extends TaskModel {

	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	public $name = 'TaskServer';

	/**
	 * Returns number of free slots
	 *
	 * @return int
	 */
	public function freeSlots() {
		$maxSlots = (int) Configure::read('Task.maxSlots');
		if (!$maxSlots) {
			$maxSlots = 10;
		}
		$runnedCount = $this->find('count', array(
			'conditions' => array(
				'status' => 2
			)
		));
		return $maxSlots - (int) $runnedCount;
	}

	/**
	 * Returns task or false
	 *
	 * @return bool|array
	 */
	public function getPending() {
		$task = $this->find('first', array(
			'conditions' => array(
				'status' => 0
			),
			'order' => array(
				'created' => 'asc'
			),
			'limit' => 1
		));

		if (!$task) {
			return false;
		}
		return $task[$this->alias];
	}

	/**
	 * Must be called when task starts running
	 *
	 * @param array $task
	 *
	 * @return mixed
	 */
	public function started(array $task) {
		$task['started'] = $this->_getCurrentDateTime();
		$task['status'] = 2;
		return $this->save($task);
	}

	/**
	 * Must be called when task has been stopped
	 *
	 * @param array $task
	 *
	 * @return mixed
	 */
	public function stoped(array $task) {
		$task['stopped'] = $this->_getCurrentDateTime();
		$task['status'] = 3;
		return $this->save($task);
	}

	/**
	 * Returns current date for DB
	 *
	 * @return string
	 */
	protected function _getCurrentDateTime() {
		return (new DateTime('now'))->format('Y-m-d H:i:s');
	}

}