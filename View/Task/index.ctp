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

//if (empty($data)) {
//	echo $this->element('basics/no_data');
//	return;
//}
echo $this->element('pagination/pagination');
?>
<table class="table table-bordered table-striped table-sortable">
	<thead>
		<tr>
			<th><?= $this->Paginator->sort('id'); ?></th>
			<th><?= $this->Paginator->sort('process_id'); ?></th>
			<th><?= $this->Paginator->sort('command'); ?></th>
			<th>Arguments</th>
			<th><?= $this->Paginator->sort('status'); ?></th>
			<th><?= $this->Paginator->sort('code'); ?></th>
			<th><?= $this->Paginator->sort('wait'); ?></th>
			<th><?= $this->Paginator->sort('stderr', 'Error'); ?></th>
			<th>Run</th>
			<th><?= $this->Paginator->sort('started'); ?></th>
			<th><?= $this->Paginator->sort('stopped'); ?></th>
			<th><?= $this->Paginator->sort('created'); ?></th>
			<th class="sorter-false"></th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ($data as $one) {
			$task = $one['TaskClient'];
			$dependentTasks = $one['DependsOnTask'];
			?>
			<tr>
				<td><?= $this->Task->id($task); ?></td>
				<td><?= $this->Task->processId($task); ?></td>
				<td><?= $this->Task->command($task); ?></td>
				<td><?= $this->Task->arguments($task, false); ?></td>
				<td><?= $this->Task->status($task); ?></td>
				<td><?= $this->Task->codeString($task, false); ?></td>
				<td><?= $this->Task->waiting($dependentTasks, false); ?></td>
				<td style="word-wrap: break-word; max-width: 200px"><?= $this->Task->stderr($task, false); ?></td>
				<td nowrap="nowrap"><?= str_replace(', ', "<br>", $this->Task->running($task)); ?>
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
echo $this->element('pagination/pagination');
