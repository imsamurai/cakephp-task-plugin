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
<h1>Task profiler</h1>
<?php
echo $this->element('form/task/profile');

if (empty($data)) {
	echo $this->element('basics/no_data');
	return;
}
?>
<table class="table table-bordered table-striped table-sortable">
	<tr>
		<td><strong>Command</strong></td>
		<td><?= $data['command']; ?></td>
	</tr>
	<tr>
		<td><strong>Run time</strong></td>
		<td>
			<?php
			if (CakePlugin::loaded('GoogleChart')) {
				$this->GoogleChart->load();
				$statCount = count($data['statistics']);
				echo $this->GoogleChart->draw('ComboChart', $this->GoogleChart->dataFromArray(array(
							's' => $data['statistics'],
							'avg' => array(
								'v' => array_fill(0, $statCount, $data['runtimeAverage']),
								'f' => array_fill(0, $statCount, $data['runtimeAverageHuman'])
							),
							'min' => array(
								'v' => array_fill(0, $statCount, $data['runtimeMin']),
								'f' => array_fill(0, $statCount, $data['runtimeMinHuman'])
							),
							'max' => array(
								'v' => array_fill(0, $statCount, $data['runtimeMax']),
								'f' => array_fill(0, $statCount, $data['runtimeMaxHuman'])
							),
								), array(
							'startedTimestamp' => array(
								'v' => 's.{n}.startedTimestamp',
								'f' => 's.{n}.started',
							),
							'runtime' => array(
								'v' => 's.{n}.runtime',
								'f' => 's.{n}.runtimeHuman',
							),
							'average' => array(
								'v' => 'avg.v.{n}',
								'f' => 'avg.f.{n}',
							),
							'min' => array(
								'v' => 'min.v.{n}',
								'f' => 'min.f.{n}',
							),
							'max' => array(
								'v' => 'max.v.{n}',
								'f' => 'max.f.{n}',
							),
								)
						), array(
					'seriesType' => 'bars',
					'series' => array(
						1 => array('type' => "line"),
						1 => array('type' => "line"),
						2 => array('type' => "line"),
						3 => array('type' => "line"),
					),
					'height' => 400,
					'width' => 1500
						)
				);
			} else {
				?>
				<div>Please install <b>imsamurai/cakephp-google-chart</b> plugin to view graph</div>

				<?php
			}
			?>
			<div>
				<b>min:</b> 
				<?= $data['runtimeMinHuman']; ?><br>
				<b>max:</b> <?= $data['runtimeMaxHuman']; ?><br>
				<b>avg:</b> <?= $data['runtimeAverageHuman']; ?>
			</div>
		</td>
	</tr>


	<tr>
		<td><strong>Count by status</strong></td>
		<td><?php
			$countByStatus = array();
			foreach ($data['countByStatus'] as $status => $count) {
				$countByStatus[] = $this->Task->status(compact('status')) . ": <b>$count</b>";
			}
			echo implode(', ', $countByStatus);
			?></td>
	</tr>
	<tr>
		<td><strong>Errored count</strong></td>
		<td><?= $data['errored']; ?></td>
	</tr>
	<tr>
		<td><strong>Wait time</strong></td>
		<td>
			<?php
			if (CakePlugin::loaded('GoogleChart')) {
				$this->GoogleChart->load();
				$statCount = count($data['statistics']);
				echo $this->GoogleChart->draw('ComboChart', $this->GoogleChart->dataFromArray(array(
							's' => $data['statistics'],
							'avg' => array(
								'v' => array_fill(0, $statCount, $data['waittimeAverage']),
								'f' => array_fill(0, $statCount, $data['waittimeAverageHuman'])
							),
							'min' => array(
								'v' => array_fill(0, $statCount, $data['waittimeMin']),
								'f' => array_fill(0, $statCount, $data['waittimeMinHuman'])
							),
							'max' => array(
								'v' => array_fill(0, $statCount, $data['waittimeMax']),
								'f' => array_fill(0, $statCount, $data['waittimeMaxHuman'])
							),
								), array(
							'startedTimestamp' => array(
								'v' => 's.{n}.startedTimestamp',
								'f' => '{n}.started',
							),
							'waittime' => array(
								'v' => 's.{n}.waittime',
								'f' => 's.{n}.waittimeHuman',
							),
							'average' => array(
								'v' => 'avg.v.{n}',
								'f' => 'avg.f.{n}',
							),
							'min' => array(
								'v' => 'min.v.{n}',
								'f' => 'min.f.{n}',
							),
							'max' => array(
								'v' => 'max.v.{n}',
								'f' => 'max.f.{n}',
							),
								)
						), array(
					'seriesType' => 'bars',
					'series' => array(
						1 => array('type' => "line"),
						1 => array('type' => "line"),
						2 => array('type' => "line"),
						3 => array('type' => "line"),
					),
					'height' => 400,
					'width' => 1500
						)
				);
			} else {
				?>
				<div>Please install <b>imsamurai/cakephp-google-chart</b> plugin to view graph</div>

				<?php
			}
			?>
			<div>
				<b>min:</b> <?= $data['waittimeMinHuman']; ?><br>
				<b>max:</b> <?= $data['waittimeMaxHuman']; ?><br>
				<b>avg:</b> <?= $data['waittimeAverageHuman']; ?>
			</div>
		</td>
	</tr>
</table>