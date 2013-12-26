<?
/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 09.07.2012
 * Time: 19:14:06
 * Format: http://book.cakephp.org/2.0/en/views.html
 *
 * @package Task.View
 */
/* @var $this IDEView */
?>
<h1>Task list</h1>
<?
echo $this->element('form/task/search');

//if (empty($data)) {
//	echo $this->element('basics/no_data');
//	return;
//}
echo $this->element('pagination/pagination');
?>
<table class="table table-bordered table-striped">
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
			<th><?= $this->Paginator->sort('started'); ?></th>
			<th><?= $this->Paginator->sort('stopped'); ?></th>
			<th><?= $this->Paginator->sort('created'); ?></th>
			<th><?= $this->Paginator->sort('modified'); ?></th>
			<th>Actions</th>
		</tr>
	</thead>
	<tbody>
<?
foreach ($data as $one) {
	?>
			<tr>
				<td><?= $one['TaskClient']['id']; ?></td>
				<td><?= $one['TaskClient']['process_id']; ?></td>
				<td><?= $one['TaskClient']['command']; ?></td>
				<td><?
					$arguments = '';
					foreach ($one['TaskClient']['arguments'] as $name => $value) {
						if (!is_numeric($name)) {
							$arguments.=' ' . $name;
						}
						$arguments.=' ' . $value;
					}
					echo $arguments;
					?></td>
				<td><?=
				$this->Html->tag('span', $statuses[$one['TaskClient']['status']]['name'], array('class' => 'label ' . $statuses[$one['TaskClient']['status']]['class']));
				?></td>
				<td><?= $this->Html->tag('span', $one['TaskClient']['code_string'], array('class' => 'label label-' . ($one['TaskClient']['code_string'] == 'OK' ? 'success' : 'important'))); ?></td>
				<td><?= $this->element('task/depends-on', array('dependsOnTask' => $one['DependsOnTask'])); ?></td>
				<td><?= $this->Text->truncate(Sanitize::html(preg_replace('/(\[[0-9;]{1,}m)/ims', '', $one['TaskClient']['stderr'])), 500); ?></td>
				<td><?= $one['TaskClient']['started'] ? $one['TaskClient']['started'] : 'Not yet'; ?></td>
				<td><?= $one['TaskClient']['stopped'] ? $one['TaskClient']['stopped'] : 'Not yet'; ?></td>


				<td><?= $one['TaskClient']['created']; ?></td>
				<td><?= $one['TaskClient']['modified']; ?></td>
				<td>
						<div class="btn-group"><button class="btn dropdown-toggle" data-toggle="dropdown">Actions <span class="caret"></span></button>
							<ul class="dropdown-menu">
								<?= $this->Html->tag('li', $this->Html->link('View', array('action' => 'view', $one['TaskClient']['id']))); ?>
								<?= $this->Html->tag('li', 
										$this->Html->link('Stop', array('action' => 'stop', $one['TaskClient']['id']), array(
											'class'=>'btn-danger'
											), "Are you sure want to stop '{$one['TaskClient']['command']}'?")
										); ?>
							</ul>
						</div>
						
					</td>
			</tr>
	<?
}
?>
	</tbody>
</table>
<?php
echo $this->element('pagination/pagination');