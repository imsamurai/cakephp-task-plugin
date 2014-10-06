<?php
/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: Dec 23, 2013
 * Time: 5:50:39 PM
 * Format: http://book.cakephp.org/2.0/en/views.html
 * 
 * @package Task.View
 */
/* @var $this View */

$statusOptions = array();
foreach ($statusList as $status) {
	$statusOptions[$status] = TaskHelper::$statuses[$status]['name'];
}

echo $this->Form->create('Task', array(
	'novalidate',
	'class' => 'well form-search',
	'type' => 'get',
	'url' => array(
		'action' => 'index',
		'controller' => 'task'
	)
));
?>
<div style="float:left;width:400px;margin-right:15px;">
	<div class="div-right">
		<?= $this->Form->input('Task.id'); ?>
	</div>
	<div class="div-right">
		<?= $this->Form->input('Task.command', array('options' => $commandList, 'type' => 'select', 'empty' => '--none--')); ?>
	</div>
	<div class="div-right">
		<?= $this->Form->input('Task.status', array('options' => $statusOptions, 'type' => 'select', 'multiple' => true)); ?>
	</div>
</div>
<div style="float:left;width:300px;">
	<div class="div-right">
		<?= $this->Form->input('started', array('class' => 'input-large daterangepicker')) ?>
	</div>
	<div class="div-right">
		<?= $this->Form->input('stopped', array('class' => 'input-large daterangepicker')) ?>
	</div>
	<div class="div-right">
		<?= $this->Form->input('created', array('class' => 'input-large daterangepicker')) ?>
	</div>
</div>
<div style="clear:left;"></div>
<div style="float:left;width:415px;">
	<div class="div-right">
		<?= $this->Form->button('Search', array('class' => 'btn btn-primary', 'div' => false)); ?>
		<?= $this->Html->link('Clear', array('action' => 'index'), array('class' => 'btn', 'id' => "btn-clear")); ?>
	</div>
</div>
<div style="float:left;width:400px;">
	<div class="div-right">
		<?php
		echo $this->Form->select('batch_action', $batchActions, array(
			'empty' => 'Select batch action',
			'class' => 'batch-action-conditions'
		));
		echo $this->Form->button('Apply', array('type' => 'submit', 'class' => 'batch-apply-conditions btn-danger', 'formaction' => Router::url(array(
			'action' => 'batch', 'controller' => 'task'
		))));
		echo $this->Form->hidden('batch_conditions', array('value' => true));
		?>
	</div>
</div>
<div style="clear:left;"></div>
<?php
echo $this->Form->end();
