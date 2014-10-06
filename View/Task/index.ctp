<?php
/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 09.07.2012
 * Time: 19:14:06
 * Format: http://book.cakephp.org/2.0/en/views.html
 *
 * @package Task.View
 */
/* @var $this View */
/* @var $Task TaskHelper */
?>
<h1>Task list</h1>
<?php
echo $this->element('form/task/search');
echo $this->element('pagination/pagination');
echo $this->Form->create('Task', array('type' => 'get', 'class' => 'batch-form', 'url' => array('action' => 'batch', 'controller' => 'task')));
echo $this->Form->select('batch_action', $batchActions, array(
	'empty' => 'Select batch action',
	'class' => 'batch-action'
));
echo $this->Form->button('Apply', array('type' => 'submit', 'class' => 'batch-apply btn-danger'));
?>
<table class="table table-bordered table-striped">
	<thead>
		<tr>
			<th><?= $this->Form->checkbox('_ids.', array('class' => 'batch-ids-all', 'hiddenField' => false)); ?></th>
			<th><?= $this->Paginator->sort('id'); ?></th>
			<th><?= $this->Paginator->sort('command'); ?></th>
			<th>Arguments</th>
			<th><?= $this->Paginator->sort('status'); ?></th>
			<th><?= $this->Paginator->sort('code'); ?></th>
			<th><?= $this->Paginator->sort('wait'); ?></th>
			<th><?= $this->Paginator->sort('stderr', 'Error'); ?></th>
			<th><?= $this->Paginator->sort('runtime'); ?></th>
			<th><?= $this->Paginator->sort('started'); ?></th>
			<th><?= $this->Paginator->sort('stopped'); ?></th>
			<th><?= $this->Paginator->sort('created'); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ($data as $one) {
			$task = $one['TaskClient'];
			$dependentTasks = $one['DependsOnTask'];
			?>
			<tr>
				<td><?= $this->Form->checkbox('ids.', array('class' => 'batch-ids', 'value' => $task['id'])); ?></td>
				<td><?= $this->Task->id($task); ?></td>
				<td><?= $this->Task->command($task); ?></td>
				<td><?= $this->Task->arguments($task, false); ?></td>
				<td><?= $this->Task->status($task); ?></td>
				<td><?= $this->Task->codeString($task, false); ?></td>
				<td><?= $this->Task->waiting($dependentTasks, false); ?></td>
				<td style="word-wrap: break-word; max-width: 200px"><?= $this->Task->stderr($task, false); ?></td>
				<td nowrap="nowrap">
					<?= str_replace(', ', "<br>", $this->Task->running($task)); ?>
					<?= $this->Task->runningBar($task, $approximateRuntimes); ?>

				</td>
				<td nowrap="nowrap"><?= str_replace(', ', "<br>", $this->Task->started($task)); ?></td>
				<td nowrap="nowrap"><?= str_replace(', ', "<br>", $this->Task->stopped($task)); ?></td>
				<td nowrap="nowrap"><?= str_replace(', ', "<br>", $this->Task->created($task)); ?></td>
				<td>
					<div class="btn-group"><button class="btn dropdown-toggle" data-toggle="dropdown"><i class="icon-tasks"></i><span class="caret"></span></button>
						<ul class="dropdown-menu pull-right">
							<?= $this->Html->tag('li', $this->Html->link('View', array('action' => 'view', $task['id']))); ?>
							<?=
							$this->Html->tag('li', $this->Html->link('Stop', array('action' => 'stop', $task['id']), array(
										'class' => 'btn-danger'
											), "Are you sure want to stop '{$task['command']}'?")
							);
							?>
							<?=
							$this->Html->tag('li', $this->Html->link('Restart', array('action' => 'restart', $task['id']), array(
										'class' => 'btn-danger'
											), "Are you sure want to restart '{$task['command']}'?")
							);
							?>
							<?=
							$this->Html->tag('li', $this->Html->link('Delete', array('action' => 'remove', $task['id']), array(
										'class' => 'btn-danger'
											), "Are you sure want to delete '{$task['command']}'?")
							);
							?>
						</ul>
					</div>

				</td>
			</tr>
			<?php
		}
		?>
	</tbody>
</table>
<?php
echo $this->Form->end();
echo $this->element('pagination/pagination');
?>
<script lang="text/javascript">
	if ($) {
		$(document).ready(
				function() {
					$('.batch-ids-all').on('click', function() {
						$('.batch-ids').prop('checked', $(this).prop('checked'));
					});
					$('.batch-apply-conditions').on('click', function(event) {
						var action = $('.batch-action-conditions').first().val();
						if (action && confirm('Are you sure want to apply "' + action + '" on all tasks founded by current search conditions?')) {
							return true;
						} else {
							event.preventDefault();
							event.stopPropagation();
						}
					});
					$('.batch-form').on('submit', function(event) {
						var action = $('.batch-action').first().val();
						if (action && confirm('Are you sure want to apply "' + action + '" on ' + $('.batch-ids:checked').length + ' task(s)?')) {
							return true;
						}
						event.preventDefault();
						event.stopPropagation();
					});
				});
	}
</script>
<style type="text/css">
	.batch-apply {
		margin-bottom:10px;
		margin-left:10px;
	}
	.batch-apply-conditions {
		margin-left:10px;
	}
</style>
