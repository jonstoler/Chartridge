<form action="login.php" action="<?php echo DS . c::get('chartridge.root') . DS . 'login.php' ?>" method="get">
	<div class="w50">
		<input name="pwd" type="password" id="focus" placeholder="password" />
		<input class="button full-width" type="submit" value="Log In" />
	</div>
</form>

<script type="text/javascript">
	document.getElementById("focus").focus();
</script>