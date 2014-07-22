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

echo $this->Form->create('Task', array('novalidate', 'class' => 'well form-search', 'type' => 'get'));
?>
<div style="float:left;width:400px;margin-right:15px;">
	<div class="div-right">
		<?= $this->Form->input('Task.command', array('options' => $commandList, 'type' => 'select', 'empty' => '--none--')); ?>
	</div>
</div>
<div style="clear:left;"></div>
<div style="float:left;width:400px;">
	<div class="div-right">
		<?= $this->Form->button('Profile', array('class' => 'btn btn-primary', 'div' => false)); ?>
	</div>
</div>
<div style="clear:left;"></div>
<?php
echo $this->Form->end();
