<?php
	$id = uri::current()->path()->slice(0, uri::current()->path()->count() - 1)->last();

	$game = db::one('games', '*', ['id' => $id]);

	$checkpoints = explode(',', $game->checkpoints);

	$sessions = db::select('sessions', '*', ['game' => $id], 'start desc');

	$nav = ['sessions'];

	$percent = [];
	foreach($sessions as $session){
		$reached = intval(db::count('checkpoints', ['game' => $id, 'session' => $session->id]));
		if(count($checkpoints > 0)){
			$percent[] = round(100 * $reached / count($checkpoints));
		} else { $percent[] = 0; }
	}
?>

<div class="unit" id="sessions">
	<h2>Sessions</h2>
	<?php snippet('progress', ['percent' => a::average($percent), 'title' => 'Average', 'classes' => ['large']]) ?>
	<?php for($i = 0; $i < $sessions->count(); $i++): ?>
		<a href="<?php echo DS . uri::current()->path()->slice(0, uri::current()->path()->count() - 1) . DS . 'session' . DS . $sessions->nth($i)->id ?>">
			<?php snippet('progress', ['percent' => $percent[$i], 'title' => $sessions->nth($i)->id]) ?>
		</a>
	<?php endfor ?>
</div>

<?php snippet('nav', ['items' => $nav]) ?>