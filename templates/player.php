<?php
	$id     = uri::current()->path()->nth(uri::current()->path()->count() - 3);
	$player = uri::current()->path()->last();

	$game     = db::one('games',    '*', ['id' => $id]);
	$sessions = db::select('sessions', '*', ['player' => $player], 'start desc');

	$checkpoints = explode(',', $game->checkpoints);

	$nav = ['info', 'sessions'];
?>

<div class="info-unit unit" id="info">
	<h2>Info</h2>
	<?php if($sessions->count() > 0): ?>
		<table class="data">
			<tr><td>Play Count</td><td><?php echo $sessions->count() ?></td></tr>
			<?php if($sessions->count() > 1): ?>
				<tr><td>First Session</td><td><?php echo date('F j, Y g:i:s A', strtotime($sessions->first()->start)) ?></td><tr>
				<tr><td>Last Session</td><td><?php echo date('F j, Y g:i:s A', strtotime($sessions->last()->start)) ?></td><tr>
			<?php else: ?>
				<tr><td>Session Time</td><td><?php echo date('F j, Y g:i:s A', strtotime($sessions->first()->start)) ?></td></tr>
			<?php endif ?>
		</table>
	<?php else: ?>
		<h3 class="light center"><?php echo $player ?> has not played your game yet. :(</h3>
	<?php endif ?>
</div>

<div class="sessions-unit unit" id="sessions">
	<h2>Sessions</h2>
	<?php if($sessions->count() > 0): ?>
		<?php foreach($sessions as $session): ?>
			<?php
				$reached = intval(db::count('checkpoints', ['game' => $game->id, 'session' => $session->id]));
				if(count($checkpoints) > 0){
					$percent = round(100 * $reached / count($checkpoints));
				} else { $percent = 0; }

			?>
			<a href="<?php echo DS . uri::current()->path()->slice(0, uri::current()->path()->count() - 2) . DS . 'session' . DS . $session->id ?>">
				<?php snippet('progress', ['percent' => $percent, 'title' => $session->id]) ?>
			</a>
		<?php endforeach ?>
	<?php else: ?>
		<h3 class="light center"><?php echo $player ?> has not played your game yet. :(</h3>
	<?php endif ?>
</div>

<?php snippet('nav', ['items' => $nav]) ?>