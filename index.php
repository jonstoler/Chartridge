<?php require_once('kirby/bootstrap.php') ?>
<?php require_once('config.php') ?>

<?php	
	global $prowl;

	$loggedin = false;
	if(isset($_COOKIE['chartridge_auth']) && $_COOKIE['chartridge_auth'] == c::get('cookie.value')){
		$loggedin = true;
	}

	c::set('chartridge.version', 0.2);
	c::set('chartridge.version.string', '0.2 alpha');

	g::set('snippetRoot', 'snippets' . DS);

	// current path
	$path = uri::current()->path();

	// remove the root directory from the path
	// root directory is set in config.php
	// under the chartridge.root variable
	// basically, this means $path only contains
	// relevant paths (paths relative to the root)
	$remove = explode(DS, c::get('chartridge.root', ''));
	for($i = 0; $i < count($remove); $i++){
		if($path->first() == $remove[$i]){
			$path = $path->offset(1);
		} else {
			break;
		}
	}

	// shortcut to add css styles to a page
	function css($stylesheet){
		return html::stylesheet(DS . c::get('chartridge.root') . DS . $stylesheet);
	}
	// shortcut to add javascript to a page
	function js($javascript){
		return html::script(DS . c::get('chartridge.root') . DS . $javascript);
	}

	if(!$loggedin){
		$title = 'Log In';
		$display = 'chartridge';
		$section = 'login';
	} else {
		// "main" page title
		$title = $path->last();

		// page subtitle (or subpath)
		if($path->count() > 0){
			$section = $path->nth($path->count() - 2);
		} else {
			$section = 'home';
		}

		if($section == 'games' || $section == 'delete' || $section == 'edit' || $section == 'info'){
			$t = db::one('games', 'name', ['id' => $title]);
			if($t){
				$title = $t->name;
			}
		}

		// page subtitle (or subpath) used for output
		// because things like "home" and "delete"
		// are sometimes a bit ugly
		$display = $section;

		if($section == 'home'){
			$display = 'chartridge';
			$title = 'games';
		} else if($section == ''){
			$section = $title;
			if($title == 'add'){ $display = 'add a game'; }

			$title = '';
		} else if($section == 'players' || $section == 'sessions'){
			$display = db::one('games', 'name', ['id' => $path->nth($path->count() - 3)]);
			$title = $section;
		}

		if($title == 'info'){
			$display = db::one('games', 'name', ['id' => $section])->name;
		}
	}

	// set template directory
	template::$root = 'templates' . DS;

	if($title == 'sessions' || $title == 'info' || $title == 'scores'){
		$page = template::create($title);
	} else {
		$page = template::create($section);
	}
	if(!$page->exists()){
		// if the template for $section does not exist
		// show an error page
		$page = template::create('notfound');
		$page->lookingfor = $path->toString();

		$display = '';
		$title = 'Page Not Found';
	} else if($section == 'game'){
		$game = db::one('games', '*', ['id' => $title]);
		if(!$game){
			// game does not exist
		} else {
			$page->game = $game;
			$title = $game->name;
		}
	}

	$header = template::create('header');
	$header->title = $title;
	$header->section = $section;
	$header->display = $display;
	$header->loggedin = $loggedin;

	$footer = template::create('footer');
	$footer->hideinfo = !$loggedin;
	$footer->version = c::get('chartridge.version.string');

	// build the page!
	echo $header;
	echo $page;
	echo $footer;
?>