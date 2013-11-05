<?php
	$defaults = [
		'gameName' => '',
		'gameID'   => '',

		'checkpoints' => [''],

		'bonuses' => [''],

		'display_checkpoint' => 'on',
		'display_bonus'      => 'on',
		'display_score'      => 'on',
		'display_increment'  => 'on',
		'display_data'       => 'on'
	];

	$info = new collection(array_merge($defaults, $data));

	$editMode = ($data != []);
?>

<div class="unit">
	<h2>Game Information</h2>

	<label>Game Name<span class="desc">what your game is actually called</span></label>
	<input type="text" id="add-game-name" value="<?php echo $info->gameName ?>" />
	<script type="text/javascript">document.getElementById("add-game-name").focus();</script>
	
	<label>Game ID<span class="desc">what the system should call your game (in URLs, for instance)</span></label>
	<input type="text" id="add-game-id" value="<?php echo $info->gameID ?>" />
</div>

<div class="unit add-checkpoint">
	<h2>Checkpoints</h2>
	<?php foreach($info->checkpoints as $checkpoint): ?>
		<div><input type="text" value="<?php echo $checkpoint ?>" /><span class="x">x</span></div>
	<?php endforeach ?>
	<div class="button center full-width" id="add-another-checkpoint">Add Another Checkpoint</div>
</div>

<div class="unit add-bonus">
	<h2>Bonuses</h2>

	<?php foreach($info->bonuses as $bonus): ?>
		<div><input type="text" value="<?php echo $bonus ?>" /><span class="x">x</span></div>
	<?php endforeach ?>
	<div class="button center full-width" id="add-another-bonus">Add Another Bonus</div>
</div>

<div class="unit settings">
	<h2>Game Settings</h2>

	<label>Display Checkpoint Unit</label>
	<?php snippet('onoff', ['id' => 'display-checkpoint', 'on' => 'yes', 'off' => 'no', 'state' => $info->display_checkpoint]) ?>

	<label class="top15">Display Bonus Unit</label>
	<?php snippet('onoff', ['id' => 'display-bonus', 'on' => 'yes', 'off' => 'no', 'state' => $info->display_bonus]) ?>

	<label class="top15">Display Score Unit</label>
	<?php snippet('onoff', ['id' => 'display-score', 'on' => 'yes', 'off' => 'no', 'state' => $info->display_score]) ?>

	<label class="top15">Display Increment Unit</label>
	<?php snippet('onoff', ['id' => 'display-increment', 'on' => 'yes', 'off' => 'no', 'state' => $info->display_increment]) ?>

	<label class="top15">Display Data Unit</label>
	<?php snippet('onoff', ['id' => 'display-data', 'on' => 'yes', 'off' => 'no', 'state' => $info->display_data]) ?>
</div>

<div class="button center" id="add-game">Save</div>

<form class="hide<?php e($editMode, ' edit', '') ?>" method="post" action="<?php echo DS . c::get('chartridge.root') . DS ?>newgame.php" name="submit">
	<?php if($editMode): ?>
		<input type="text" value="<?php echo $info->gameID ?>" name="original_id" />
		<input type="text" name="edit_existing" value="yes" />
	<?php endif ?>
	<input type="submit" id="submit" />
</form>