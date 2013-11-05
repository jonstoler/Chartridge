<?php
	$id   = uri::current()->path()->nth(uri::current()->path()->count() - 3);
	$sess = uri::current()->path()->last();

	$game    = db::one('games',    '*', ['id' => $id]);
	$session = db::one('sessions', '*', ['id' => $sess]);

	$allCheckpoints = explode(',', $game->checkpoints);
	$allBonuses     = explode(',', $game->bonuses);

	$checkpoints = db::select('checkpoints', '*', ['game' => $id, 'session' => $sess]);
	$bonuses     = db::select('bonuses',     '*', ['game' => $id, 'session' => $sess]);
	$increments  = db::select('increments',  '*', ['game' => $id, 'session' => $sess]);
	$scores      = db::select('scores',      '*', ['game' => $id, 'session' => $sess]);
	$data        = db::select('data',        '*', ['game' => $id, 'session' => $sess]);
	
	if(!$checkpoints){ $checkpoints = new collection(); }
	if    (!$bonuses){     $bonuses = new collection(); }
	if (!$increments){  $increments = new collection(); }
	if       (!$data){        $data = new collection(); }

	$nav = ['session info', 'checkpoints', 'bonuses'];

	function d($start, $now){
		$diff = strtotime($now) - strtotime($start);

		$days = floor($diff / (24 * 60 * 60));
		$hours = floor(($diff / (60 * 60)) - ($days * 24));
		$minutes = floor(($diff / (60)) - ($hours * 60) - ($days * 24 * 60));
		$seconds = floor(($diff) - ($minutes * 60) - ($hours * 60 * 60) - ($days * 24 * 60 * 60));

		$str = '';
		if($days > 0){
			$str .= $days . ' day' . ($days == 1 ? '' : 's');
			if($hours > 0 || $minutes > 0 || $seconds > 0){ $str .= ', '; }
		}
		if($hours > 0){
			$str .= $hours . ' hour' . ($hours == 1 ? '' : 's');
			if($minutes > 0 || $seconds > 0){ $str .= ', '; }
		}
		if($minutes > 0){
			$str .= $minutes . ' minute' . ($minutes == 1 ? '' : 's');
			if($seconds > 0){ $str .= ', '; }
		}
		if($seconds > 0){
			$str .= $seconds . ' second' . ($seconds == 1 ? '' : 's');
		}

		if($str == ''){ $str = '0 seconds'; }

		return $str;
	}
?>

<div class="info-unit unit" id="session-info">
	<h2>Session Info</h2>
	<table class="data">
		<tr><td>Started</td><td><?php echo date('F j, Y g:i:s A', strtotime($session->start)) ?></td></tr>
		<tr><td>Progress</td><td>
			<?php
				if(count($allCheckpoints) > 0){
					$percent = round(100 * $checkpoints->count() / count($allCheckpoints));
				} else {
					$percent = 0;
				}
				echo $percent . '% (' . $checkpoints->count() . '/' . count($allCheckpoints) . ')';
			?>
		</td></tr>
		<tr><td>Player</td><td>
			<a href="<?php echo DS . c::get('chartridge.root') . DS . 'game' . DS . $id . DS . 'player' . DS . $session->player ?>">
				<?php echo $session->player ?>
			</a>
		</td></tr>
		<tr><td>Country</td><td><?php echo $session->ip ?></td></tr>
		<tr><td>URL</td><td>
			<?php if(url::valid($session->location)): ?>
				<a href="<?php echo $session->location ?>"><?php echo url::short($session->location) ?></a>
			<?php else: ?>
				<?php echo $session->location ?>
			<?php endif ?>
			</td></tr>
	</table>
</div>

<div class="checkpoint-unit unit" id="checkpoint">
	<h2>Checkpoints</h2>
	<?php if($checkpoints->count() > 0): ?>
		<table class="data">
			<?php foreach($checkpoints as $checkpoint): ?>
				<tr><td><?php echo $checkpoint->name ?></td><td><?php echo d($session->start, $checkpoint->time) ?></td></tr>
			<?php endforeach ?>
		</table>
	<?php else: ?>
		<h3 class="light center"><?php echo $sess ?> has not reached a checkpoint yet. :(</h3>
	<?php endif ?>
</div>

<div class="bonus-unit unit" id="bonuses">
	<h2>Bonuses</h2>
	<?php if($bonuses->count() > 0): ?>
		<table class="data">
			<?php foreach($bonuses as $bonus): ?>
				<tr><td><?php echo $bonus->name ?></td><td><?php echo d($session->start, $bonus->time) ?></td></tr>
			<?php endforeach ?>
		</table>
	<?php else: ?>
		<h3 class="light center"><?php echo $sess ?> has not received a bonus yet. :(</h3>
	<?php endif ?>
</div>

<?php if($game->disable_score_unit != '1'): ?>
	<?php $nav[] = 'scores' ?>
	<div class="score-unit unit" id="scores">
		<h2>Scores</h2>
		<?php if($scores->count() == 0): ?>
			<h3 class="light center"><?php echo $sess ?> has not scored in your game yet. :(</h3>
			<h4 class="light center">You can disable the score unit in your game's settings.</h4>
		<?php else: ?>
			<?php
				$modes = db::column('scores', 'mode', ['game' => $game->id]);
				$modes = array_unique($modes->toArray());
				$modes = new collection($modes);

				$index = 0;
			?>
			<?php while($index < $modes->count()): ?>
				<?php $page = $modes->slice($index, $index + 4) ?>
					<div class="row">
						<?php foreach($page as $mode): ?>
							<div class="g3">
								<h4 class="mode"><?php echo $mode ?></h4>
								<?php
									$topScores = db::select('scores', 'score', ['game' => $game->id, 'mode' => $mode], 'score desc', 0, 10);
								?>
								<ol>
									<?php foreach($topScores as $score): ?>
										<li><?php echo $score->score ?></li>
									<?php endforeach ?>
								</ol>
							</div>
						<?php endforeach ?>
					</div>
				<?php $index += 4 ?>
			<?php endwhile ?>
		<?php endif ?>
	</div>
<?php endif ?>

<?php if($game->disable_increment_unit != '1'): ?>
	<?php $nav[] = 'increments' ?>
	<div class="increment-unit unit" id="increments">
		<h2>Increments</h2>
		<?php if($increments->count() == 0): ?>
			<h3 class="light center"><?php echo $sess ?> has not received any increments yet. :(</h3>
			<h4 class="light center">You can disable the increments unit in your game's settings.</h4>
		<?php else: ?>
			<table class="data">
				<?php foreach($increments as $increment): ?>
					<tr><td><?php echo $increment->name ?></td><td><?php echo $increment->value ?></td></tr>
				<?php endforeach ?>
			</table>
		<?php endif ?>
	</div>
<?php endif ?>

<?php if($game->disable_data_unit != '1'): ?>
	<?php $nav[] = 'data' ?>
	<div class="increment-unit unit" id="data">
		<h2>Data</h2>
		<?php if($data->count() == 0): ?>
			<h3 class="light center"><?php echo $sess ?> has not logged any data yet. :(</h3>
			<h4 class="light center">You can disable the data unit in your game's settings.</h4>
		<?php else: ?>
			<table class="data">
				<?php foreach($data as $datum): ?>
					<tr><td><?php echo $datum->name ?></td><td><?php echo $datum->value ?></td></tr>
				<?php endforeach ?>
			</table>
		<?php endif ?>
	</div>
<?php endif ?>

<?php snippet('nav', ['items' => $nav]);