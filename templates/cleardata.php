<?php
	$id = uri::current()->path()->last();
	
	$name = db::one('games', '*', ['id' => $id]);
	$name = $name->name;

	db::delete('sessions',    ['game' => $id]);
	db::delete('players',     ['game' => $id]);
	db::delete('checkpoints', ['game' => $id]);
	db::delete('bonuses',     ['game' => $id]);
	db::delete('scores',      ['game' => $id]);
	db::delete('increments',  ['game' => $id]);
	db::delete('data',        ['game' => $id]);
?>

<h3 class="light">All data for <?php echo $name ?> has been deleted permanently.</h3>
<a href="<?php echo DS . c::get('chartridge.root') ?>">Back to the game list</a>