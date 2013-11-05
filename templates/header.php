<?php echo html::doctype() ?>
<html lang="en">
<?php echo html::charset() ?>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<head>
	<title>
		Chartridge
		<?php e($section != 'home' && $display != '' && $title != 'Log In', ' &rsaquo; ' . ucfirst($display), '') ?>
		<?php e($title != '', ' &rsaquo; ' . r($section != 'session' && $section != 'player', ucfirst($title), $title), '') ?>
	</title>
</head>
<?php echo css('css/style.css') ?>
<?php echo css('css/tipsy.css') ?>
<?php echo js('js/jquery.js') ?>
<?php echo js('js/tipsy.js') ?>
<?php echo js('js/script.js') ?>
<?php echo js('js/sparkline.js') ?>
<body>
	<div class="title-unit">
		<?php if($loggedin && $title != 'logout'): ?>
			<form method="post" action="<?php echo uri::current()->baseurl() . DS . c::get('chartridge.root') . DS . 'logout.php' ?>">
				<input type="submit" class="button logout" value="Log Out" />
			</form>
		<?php endif ?>
		<?php 
			$p = uri::current()->path();
			$href = '';
			if($p->count() == 1){
				$h = false;
			} else if($display != 'game' && $display != 'session' && $display != 'player'){
				$h = $p->slice(0, $p->count() - 1);
			} else {
				$h = $p->slice(0, $p->count() - 2);
			}

			if($h){
				$href = 'href="' . uri::current()->baseurl();
				foreach($h as $i){
					$href .= DS . $i;
				}
				$href .= '"';
			}
		?>
		<h1>
			<img class="cart" src="<?php echo uri::current()->baseurl() . DS . c::get('chartridge.root') . DS . 'cart.png' ?>" />
			<a class="section"<?php echo $href ?>><?php echo $display . r($display != '' && $title != '', ' &rsaquo; ', '') ?></a>
			<?php echo $title ?>
		</h1>
	</div>