<?php
/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 20.04.2012
 * Time: 12:59:13
 * 
 * @package Task.View.Element
 */
?>
<div class="pagination pagination-right">
    <ul>
		<?=
		$this->Paginator->numbers(array(
			'first' => 'First',
			'last' => 'Last',
			'tag' => 'li',
			'currentClass' => 'active',
			'modulus' => Configure::read('Pagination.pages'),
			'separator' => ''
		));
		?>
    </ul>
	<?php
	echo $this->Html->div('pull-left', $this->Paginator->counter(array(
				'format' => 'Page {:page} of {:pages}, showing {:current} records out of
             {:count} total, starting on record {:start}, ending on {:end}'
			)), array('style' => 'margin-top:17px;'));
	?>
</div>
