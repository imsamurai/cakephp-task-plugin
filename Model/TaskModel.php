<?

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 11.06.2013
 * Time: 9:51:57
 * Format: http://book.cakephp.org/2.0/en/models.html
 */

/**
 * @package Task.Model
 */
abstract class TaskModel extends AppModel {

	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	public $name = 'TaskModel';

	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	public $useTable = 'tasks';

	/**
	 * {@inheritdoc}
	 *
	 * @var array
	 */
	public $actsAs = array(
		'Serializable.Serializable' => array(
			'fields' => array('details', 'arguments')
		)
	);

}