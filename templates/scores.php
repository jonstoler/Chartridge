<?php
	$id = uri::current()->path()->slice(0, uri::current()->path()->count() - 1)->last();

	$game = db::one('games', '*', ['id' => $id]);
	$scores = db::select('scores', '*', ['game' => $id], 'score desc');

	$modes = [];
	foreach($scores as $score){
		$modes[] = $score->mode;
	}
	$modes = array_unique($modes);

	$nav = [];
?>

<?php foreach($modes as $mode): ?>
	<?php $nav[] = $mode ?>
	<div class="unit" id="<?php echo $mode ?>">
		<h1><?php echo $mode ?></h1>
		<?php
			$thisMode = db::select('scores', '*', ['mode' => $mode, 'game' => $id], 'score desc');
			$scoresPer = ceil($thisMode->count() / 4);
		?>
		<div class="row">
			<?php for($i = 0; $i < $thisMode->count(); $i++): ?>
				<?php if($i % $scoresPer == 0): ?>
					<?php if($i > 0): ?>
						</ol></div>
					<?php endif ?>
					<div class="g3">
						<ol start="<?php echo $i + 1 ?>">
				<?php endif ?>
				<li><a class="light" href="<?php echo DS . c::get('chartridge.root') . DS . 'game' . DS . $id . DS . 'session' . DS . $thisMode->nth($i)->session ?>"><?php echo $thisMode->nth($i)->score ?></a></li>
			<?php endfor ?>
			</ol></div>
		</div>
	</div>
<?php endforeach ?>

<?php snippet('nav', ['items' => $nav]) ?>