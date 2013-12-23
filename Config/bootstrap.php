<?

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: Dec 23, 2013
 * Time: 5:54:38 PM
 * Format: http://book.cakephp.org/2.0/en/views.html
 * 
 * @package Task.Config
 */
/* @var $this View */

Configure::write('Pagination.pages', Configure::read('Pagination.pages') ? Configure::read('Pagination.pages') : 10);
App::uses('TaskType', 'Task.Lib/Task');
App::uses('Sanitize', 'Utility');