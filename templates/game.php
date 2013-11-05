<?php
	global $path;

	$checkpoints = explode(',', $game->checkpoints);
	$bonuses     = explode(',', $game->bonuses);

	$playcount = 0;
	$playcount = db::count('sessions', ['game' => $game->id]);

	$sessions = db::select('sessions', '*', ['game' => $game->id], 'start desc');

	$today = $thisWeek = $thisMonth = $allTime = [];

	$hours = db::select('sessions', 'start', 'game = "' . $game->id . '" AND start >= CURRENT_DATE()');
	for($i = 0; $i <= intval(date('G')); $i++){ $today[$i] = 0; }
	foreach($hours as $hour){
		$date = intval(date('G', strtotime($hour->start)));
		$today[$date]++;
	}

	$and = '" AND start > DATE_SUB(NOW(), INTERVAL ' . (intval(date('w'))) . ' DAY)';
	if(intval(date('w')) == 0){ $and = '" AND start >= CURRENT_DATE()'; }
	$week = db::select('sessions', 'start', 'game = "' . $game->id . $and);
	for($i = 0; $i < 7; $i++){ $thisWeek[$i] = 0; }
	foreach($week as $day){
		$date = intval(date('w', strtotime($day->start)));
		$thisWeek[$date]++;
	}

	$month = db::select('sessions', 'start', 'game = "' . $game->id . '" AND start > DATE_SUB(NOW(), INTERVAL ' . (intval(date('j')) - 1) . ' DAY)');
	for($i = 0; $i < intval(date('j')); $i++){ $thisMonth[$i] = 0; }
	foreach($month as $day){
		$date = intval(date('j', strtotime($day->start)));
		$thisMonth[$date - 1]++;
	}

	$ever = db::select('sessions', 'start', ['game' => $game->id], 'start asc');
	$allTime = [];
	$allTimeStr = [];
	if(!empty($ever->data)){
		$first = strtotime(date('F j Y', strtotime($ever->first()->start)));
		$last  = strtotime(date('F j Y', strtotime($ever->last()->start)));
		for($i = $first; $i < $last; $i += (24 * 60 * 60)){
			if(!isset($allTimeStr[date('F j Y', $i)])){
				$allTimeStr[date('F j Y', $i)] = date('F j, Y', $i);
			}

			if(!isset($allTime[date('F j Y', $i)])){
				$allTime[date('F j Y', $i)] = 0;
			} else {
				$allTime[date('F j Y', $i)]++;
			}
		}
		$allTimeStr[date('F j Y', $last)] = date('F j, Y', $last);
		foreach($ever as $day){
			$key = date('F j Y', strtotime($day->start));

			if(!isset($allTime[$key])){
				$allTime[$key] = 1;
			} else {
				$allTime[$key]++;
			}
		}
		$allTime    = array_values($allTime);
		$allTimeStr = array_values($allTimeStr);
		if(!isset($allTimeStr[date('F j Y')])){
			$allTimeStr[] = date('F j, Y');
			$allTime[]    = 0;
		}
	}

	$playsToday    = array_sum($today);
	$playsThisWeek = array_sum($thisWeek);

	$scores = db::select('scores', '*', ['game' => $game->id]);

	// units that are always here
	// we'll add to this to build
	// a "table of contents" which
	// can be used to quickly scroll
	// to a given unit
	$nav = ['plays', 'sessions'];
?>

<div class="plays-unit unit" id="plays">
	<h2>Plays</h2>
	<?php if($playcount == 0): ?>
		<h3 class="light center">Nobody has played your game yet. :(</h3>
	<?php else: ?>
		<a class="detail" href="<?php echo DS . uri::current() . DS . 'info' ?>">View Detailed Information &rsaquo;</a>
		<div class="row top10">
			<div class="g4">
				<h1 class="play-total" data-tick="<?php echo $playcount ?>">0</h1>
				<span class="play-total-subtitle">TOTAL</span>
			</div>
			<div class="g4">
				<h1 class="play-total" data-tick="<?php echo $playsToday ?>">0</h1>
				<span class="play-total-subtitle">TODAY</span>
			</div>
			<div class="g4">
				<h1 class="play-total" data-tick="<?php echo $playsThisWeek ?>">0</h1>
				<span class="play-total-subtitle">THIS WEEK</span>
			</div>
		</div>
		<div class="chartoptions top10">
			<small class="button selected" id="today">Today</small>
			<small class="button" id="thisweek">This Week</small>
			<small class="button" id="thismonth">This Month</small>
			<small class="button" id="alltime">All Time</small>
		</div>
		<div class="row top15">
			<div class="g12">
				<?php snippet('playchart', ['data' => [
					'today'         => $today,
					'thisweek'      => $thisWeek,
					'thismonth'     => $thisMonth,
					'alltime'       => $allTime,
					'alltime_names' => $allTimeStr
				]]) ?>
			</div>
		</div>
	<?php endif ?>
</div>

<div class="sessions-unit unit" id="sessions">
	<h2>Sessions</h2>
	<?php if($sessions->count() == 0): ?>
		<h3 class="light center">Nobody has played your game yet. :(</h3>
	<?php else: ?>
		<a class="detail" href="<?php echo DS . uri::current()->path() . DS . 'sessions' ?>">View All Sessions &rsaquo;</a>
		<?php foreach($sessions->slice(0, 10) as $session): ?>
			<?php
				$reached = intval(db::count('checkpoints', ['game' => $game->id, 'session' => $session->id]));
				if(count($checkpoints) > 0){
					$percent = round(100 * $reached / count($checkpoints));
				} else { $percent = 0; }
			?>
			<a href="<?php echo DS . uri::current()->path() . DS . 'session' . DS . $session->id ?>">
				<?php snippet('progress', ['percent' => $percent, 'title' => $session->id]) ?>
			</a>
		<?php endforeach ?>
	<?php endif ?>
</div>

<?php if($game->disable_checkpoint_unit != '1'): ?>
	<?php $nav[] = 'checkpoints' ?>
	<div class="checkpoint-unit unit" id="checkpoints">
		<h2>Checkpoints</h2>
		<?php if(count($checkpoints) == 0): ?>
			<h3 class="light center">You have not defined any checkpoints yet. :(</h3>
			<h4 class="light center lowercase">Do you want to <a>add some</a>?</h4>
		<?php else: ?>
			<?php if(count($sessions) == 0): ?>
				<h3 class="light center">Nobody has reached a checkpoint yet. :(</h3>
				<h4 class="light center lowercase">You can disable the checkpoint unit in your game's settings.</h4>
			<?php else: ?>
				<?php foreach($checkpoints as $checkpoint): ?>
					<?php
						// TODO: use players instead of sessions if playcount.players is true
						$reached = intval(db::count('checkpoints', ['game' => $game->id, 'name' => $checkpoint]));
						if($sessions->count() > 0){
							$percent = round(100 * $reached / $sessions->count());
						} else { $percent = 0; }
					?>
					<?php snippet('progress', ['percent' => $percent, 'title' => $checkpoint]) ?>
				<?php endforeach ?>
			<?php endif ?>
		<?php endif ?>
	</div>
<?php endif ?>

<?php if($game->disable_bonus_unit != '1'): ?>
	<?php $nav[] = 'bonuses' ?>
	<div class="bonus-unit unit" id="bonuses">
		<h2>Bonuses</h2>
		<?php if(count($checkpoints) == 0): ?>
			<h3 class="light center">You have not defined any bonuses yet. :(</h3>
			<h4 class="light center lowercase">Do you want to <a>add some</a>?</h4>
		<?php else: ?>
			<?php if(count($sessions) == 0): ?>
				<h3 class="light center">Nobody has reached a bonus yet. :(</h3>
				<h4 class="light center lowercase">You can disable the bonus unit in your game's settings.</h4>
			<?php else: ?>
				<?php foreach($bonuses as $bonus): ?>
					<?php
						// TODO: use players instead of sessions if playcount.players is true
						$reached = intval(db::count('bonuses', ['game' => $game->id, 'name' => $bonus]));
						if($sessions->count() > 0){
							$percent = round(100 * $reached / $sessions->count());
						} else { $percent = 0; }
					?>
					<?php snippet('progress', ['percent' => $percent, 'title' => $bonus]) ?>
				<?php endforeach ?>
			<?php endif ?>
		<?php endif ?>
	</div>
<?php endif ?>

<?php if($game->disable_score_unit != '1'): ?>
	<?php $nav[] = 'scores' ?>
	<div class="score-unit unit" id="scores">
		<h2>Scores</h2>
		<?php if($scores->count() == 0): ?>
			<h3 class="light center">Nobody has scored in your game yet. :(</h3>
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
		<a class="detail" href="<?php echo DS . uri::current() . DS . 'scores' ?>">View All Scores &rsaquo;</a>
	</div>
<?php endif ?>

<div class="row">
	<div class="skip2 g4">
		<a class="button center edit" href="<?php echo DS . c::get('chartridge.root') . DS . 'edit' . DS . $game->id ?>">Edit This Game</a>
	</div>
	<div class="g4">
		<a class="button center delete" href="<?php echo DS . c::get('chartridge.root') . DS . 'delete' . DS . $game->id ?>">Delete This Game</a>
	</div>
</div>
<br>
<div class="row">
	<div class="g8 skip2">
		<a class="button center delete full-width" href="<?php echo DS . c::get('chartridge.root') . DS . 'cleardata' . DS . $game->id ?>">Clear Game Data</a>
	</div>
</div>

<?php snippet('nav', ['items' => $nav]) ?>