<?php
/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 18.06.2013
 * Time: 11:11:57
 * Format: http://book.cakephp.org/2.0/en/views.html
 *
 * @package Task.View
 */
/* @var $this IDEView */
?>
<h1>View Task</h1>
<br />
<?php
if (!isset($task)) {
	echo $this->element('no_data');
	return;
}
?>
<table class="table table-bordered table-striped table-sortable">
	<?php
	$fields = array(
		'id' => null,
		'process_id' => null,
		'statistics' => '<td><div>' . $this->Task->statistics($statistics) . '</div></td>',
		'command' => null,
		'arguments' => null,
		'status' => null,
		'code_string' => null,
		'running' => '<td><div>' . $this->Task->running($task) . '<br>' . $this->Task->runningBar($task, $approximateRuntimes) . '</div></td>',
		'started' => null,
		'stopped' => null,
		'created' => null,
		'modified' => null,
		'timeout' => null,
		'waiting' => '<td><div>' . $this->Task->waiting($dependentTasks) . '</div></td>',
		'stderr' => null,
		'stdout' => null,
		'code' => null,
		'details' => null,
		'path' => null,
		'hash' => null,
		'server_id' => null
	);
	foreach ($fields as $field => $value) {
		?>
		<tr>
			<td><strong><?= Inflector::humanize($field); ?></strong></td>
			<?php
			if ($value) {
				echo $value;
			} else {
				?>
				<td><div style="white-space: pre;max-height: 500px;max-width: 1260px;overflow: auto;"><?= $this->Task->{Inflector::camelize($field)}($task); ?></div></td>
			<?php
			}
			?>

		</tr>
		<?php
	}
	?>
</table>
