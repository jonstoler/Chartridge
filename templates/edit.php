<?php
	$gameID = uri::current()->path()->last();

	$game = db::one('games', '*', ['id' => $gameID]);

	$edit = template::create('add');
	if($game){
		$edit->data = [
			'gameName' => $game->name,
			'gameID'   => $game->id,

			'checkpoints' => explode(',', $game->checkpoints),
			'bonuses'     => explode(',', $game->bonuses),

			'display_checkpoint' => r($game->disable_checkpoint_unit != '1', 'on', 'off'),
			'display_bonus'      => r($game->disable_bonus_unit      != '1', 'on', 'off'),
			'display_score'      => r($game->disable_score_unit      != '1', 'on', 'off'),
			'display_increment'  => r($game->disable_increment_unit  != '1', 'on', 'off'),
			'display_data'       => r($game->disable_data_unit       != '1', 'on', 'off')
		];
		$edit->editMode = true;
	}

	echo $edit;
?>