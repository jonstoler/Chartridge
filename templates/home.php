<?php
	$sqlSort = '';
	$sortBy = '';
	switch(c::get('games.sort', 'creation')){
		case 'creation': $sqlSort = 'created desc'; break;
		case 'lastplayed': $sortBy = 'lastplayed'; break;
		case 'mostpopular': $sortBy = 'mostpopular'; break;
		case 'leastpopular': $sortBy = 'leastpopular'; break;
		case 'alphabetical': $sqlSort = 'name desc'; break;
		default: $sqlSort = 'created desc'; break;
	}

	$games = db::select('games', '*', null, $sqlSort);

	if($sortBy == 'lastplayed'){
		// sort by last played
	} else if($sortBy == 'mostpopular'){
		// sort by playcount
	} else if($sortBy == 'leastpopular'){
		// sort by lowest playcount
	}

	
	$playCounts = [];
	foreach($games as $game){
		$playCounts[$game->id] = db::count('sessions', ['game' => $game->id]);
	}
?>

<div class="row bottom10" id="game-manage">
	<div class="g4">
		<a class="button center w90 add" href="./add">Add Game</a>
	</div>
	<div class="g4">
		<div class="button center w90 edit<?php e($games->count() == 0, ' disable', '') ?>">Edit Game</div>
	</div>
	<div class="g4">
		<div class="button center w90 del<?php e($games->count() == 0, ' disable', '') ?>">Delete Game</div>
	</div>
</div>
<h2 class="light center" id="click-a-game"></h2>

<?php if(!$games || $games->count() == 0): ?>
	<div class="no-games-unit">
		<h1>You don't have any games. :(</h1>
		<p>Try <a href="./add">adding one</a>?</p>
	</div>
<?php else: ?>
	<?php
		foreach($games as $game){
			$game->add('playcount', $playCounts[$game->id]);
			snippet('game-unit', $game->toArray());
		}
	?>
<?php endif ?>