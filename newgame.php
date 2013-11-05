<?php
	require_once('kirby/bootstrap.php');
	require_once('config.php');

	if(r::get('edit_existing', 'no') == 'yes'){
		db::update('games', [
			'name'                    => r::get('game-name'),
			'id'                      => r::get('game-id'),
			'checkpoints'             => r::get('checkpoints', ''),
			'bonuses'                 => r::get('bonuses', ''),
			'disable_checkpoint_unit' => r::get('disable_checkpoint_unit', '0'),
			'disable_bonus_unit'      => r::get('disable_bonus_unit', '0'),
			'disable_score_unit'      => r::get('disable_score_unit', '0'),
			'disable_increment_unit'  => r::get('disable_increment_unit', '0'),
			'disable_data_unit'       => r::get('disable_data_unit', '0')
		], ['id' => r::get('original_id')]);
	} else {
		db::insert('games', [
			'name'                    => r::get('game-name'),
			'id'                      => r::get('game-id'),
			'checkpoints'             => r::get('checkpoints', ''),
			'bonuses'                 => r::get('bonuses', ''),
			'disable_checkpoint_unit' => r::get('disable_checkpoint_unit', '0'),
			'disable_bonus_unit'      => r::get('disable_bonus_unit', '0'),
			'disable_score_unit'      => r::get('disable_score_unit', '0'),
			'disable_increment_unit'  => r::get('disable_increment_unit', '0'),
			'disable_data_unit'       => r::get('disable_data_unit', '0'),
			'created'                 => date('Y-m-d G:i:s')
		]);
	}

	header('Location: ' . DS . c::get('chartridge.root') . DS);
?>