<?
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
<?
if (!isset($TaskClient)) {
	echo $this->element('no_data');
	return;
}
?>
<table class="table table-bordered table-striped table-sortable">
	<?
	$arguments = '';
	foreach ($TaskClient['arguments'] as $name => $value) {
		if (!is_numeric($name)) {
			$arguments.=' ' . $name;
		}
		$arguments.=' ' . $value;
	}
	$TaskClient['arguments'] = $arguments;
	$TaskClient['stdout'] = Sanitize::html(preg_replace('/(\[[0-9;]{1,}m)/ims', '', $TaskClient['stdout']));
	$TaskClient['stderr'] = Sanitize::html(preg_replace('/(\[[0-9;]{1,}m)/ims', '', $TaskClient['stderr']));
	$TaskClient['wait for'] = $this->element('task/depends-on', array('dependsOnTask' => $DependsOnTask));
	foreach ($TaskClient as $key => $value) {
		?>
		<tr>
			<td><strong><?= $key; ?></strong></td>
			<td style="white-space: pre;"><?= is_array($value) ? Debugger::dump($value) : $value; ?></td>

		</tr>
	<? } ?>
</table>
