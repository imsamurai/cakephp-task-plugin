<?
/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 12.06.2013
 * Time: 17:27:41
 * Format: http://book.cakephp.org/2.0/en/development/testing.html
 */

/**
 * @package Task.Test.Case.Model
 */
class TaskServerTest extends CakeTestCase {
    /**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = array(
		'plugin.Task.Task',
		'plugin.Task.DependentTask',
	);

	/**
	 * TaskClient model
	 *
	 * @var TaskClient
	 */
	public $TaskClient = null;

	/**
	 * TaskServer model
	 *
	 * @var TaskServer
	 */
	public $TaskServer = null;

	public function setUp() {
		parent::setUp();
		$this->TaskClient = ClassRegistry::init('Task.TaskClient');
		$this->TaskServer = ClassRegistry::init('Task.TaskServer');
	}

	public function testFreeSlots() {
		$maxSlots = 5;
		Configure::write('Task.maxSlots', $maxSlots);

		$this->TaskClient->add('ls', '', array('-l'));
		$this->assertSame($maxSlots, $this->TaskServer->freeSlots());
		$this->TaskServer->getPending();
		$this->assertSame($maxSlots-1, $this->TaskServer->freeSlots());
	}

	public function testGetPending() {
		$task = $this->TaskClient->add('ls', '', array('-l'));
		$pendedTask = $this->TaskServer->getPending();
		$this->assertEqual($task['status'], TaskType::UNSTARTED);
		$this->assertEqual($pendedTask['status'], TaskType::DEFFERED);
		$this->assertSame($task['id'], $pendedTask['id']);
	}

	public function testStarted() {
		$this->TaskClient->add('ls', '', array('-l'));
		$task = $this->TaskServer->getPending();
		$this->TaskServer->started($task);
		$startedTask = $this->TaskClient->find('first', array('conditions' => array('id'=>$task['id'])));
		$this->assertEqual($startedTask['TaskClient']['status'], TaskType::RUNNING);
	}

	public function testStopped() {
		$this->TaskClient->add('ls', '', array('-l'));
		$task = $this->TaskServer->getPending();
		$this->TaskServer->stoped($task);
		$startedTask = $this->TaskClient->find('first', array('conditions' => array('id'=>$task['id'])));
		$this->assertEqual($startedTask['TaskClient']['status'], TaskType::FINISHED);
	}

	public function testDependent() {
		$task1 = $this->TaskClient->add('ls', '', array('-l'));
		$this->TaskClient->add('ls', '', array('-l'));

		$task2 = $this->TaskClient->add('ls', '', array('-la'));
		$this->TaskClient->add('ls', '', array('-la'));

		$pendedTask1 = $this->TaskServer->getPending();
		$pendedTask2 = $this->TaskServer->getPending();

		$this->assertEqual($pendedTask1['id'], $task1['id']);
		$this->assertEqual($pendedTask2['id'], $task2['id']);
	}
}