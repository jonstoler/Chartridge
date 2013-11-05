<?php
	$id = uri::current()->path()->slice(0, uri::current()->path()->count() - 1)->last();

	$game = db::one('games', '*', ['id' => $id]);

	$lastCheckpoint = a::last(explode(',', $game->checkpoints));
	$sessions = db::select('sessions', '*', ['game' => $game->id]);
	$beaten = intval(db::count('checkpoints', ['game' => $game->id, 'name' => $lastCheckpoint]));

	$beatPercent = round(100 * $beaten / $sessions->count());

	$hours = db::select('sessions', 'start', 'game = "' . $game->id . '" AND start >= CURRENT_DATE()');
	$today = [];
	$todayNames = [];
	$pm = true;
	for($i = 0; $i <= intval(date('G')); $i++){
		$today[$i] = 0;
		$hr = $i % 12;
		if($hr == 0){ $hr = 12; $pm = !$pm; }
		$todayNames[$i] = $hr . ':00 ' . r($pm, 'PM', 'AM');
	}
	foreach($hours as $hour){
		$date = date('G', strtotime($hour->start));
		$today[$date]++;
	}

	$and = '" AND start > DATE_SUB(NOW(), INTERVAL ' . (intval(date('w'))) . ' DAY)';
	if(intval(date('w')) == 0){ $and = '" AND start >= CURRENT_DATE()'; }
	$week = db::select('sessions', 'start', 'game = "' . $game->id . $and);
	$thisWeek = [];
	$thisWeekNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
	for($i = 0; $i < 7; $i++){ $thisWeek[$i] = 0; }
	foreach($week as $day){
		$date = intval(date('w', strtotime($day->start)));
		$thisWeek[$date]++;
	}

	$month = db::select('sessions', 'start', 'game = "' . $game->id . '" AND start > DATE_SUB(NOW(), INTERVAL ' . (intval(date('j')) - 1) . ' DAY)');
	$thisMonth = [];
	$thisMonthNames = [];
	for($i = 0; $i < intval(date('j')); $i++){
		$suffix = 'th';
		$j = $i + 1;
		$ones = $j % 10;
		if($ones == 1 && ($j < 10 || $j > 20)){ $suffix = 'st'; }
		else if($ones == 2 && ($j < 10 || $j > 20)){ $suffix = 'nd'; }
		else if($ones == 3 && ($j < 10 || $j > 20)){ $suffix = 'rd'; }
		$thisMonth[$i] = 0;
		$thisMonthNames[$i] = date('F ') . $j . $suffix;
	}
	foreach($month as $day){
		$date = intval(date('j', strtotime($day->start)));
		$thisMonth[$date - 1]++;
	}

	$ever = db::select('sessions', 'start', ['game' => $game->id], 'start asc');
	$allTime = [];
	$allTimeNames = [];
	$first = strtotime(date('F j Y', strtotime($ever->first()->start)));
	$last  = strtotime(date('F j Y', strtotime($ever->last()->start)));
	for($i = $first; $i < $last; $i += (24 * 60 * 60)){
		if(!isset($allTimeNames[date('F j Y', $i)])){
			$allTimeNames[date('F j Y', $i)] = date('F j, Y', $i);
		}

		if(!isset($allTime[date('F j Y', $i)])){
			$allTime[date('F j Y', $i)] = 0;
		} else {
			$allTime[date('F j Y', $i)]++;
		}
	}
	$allTimeNames[date('F j Y', $last)] = date('F j, Y', $last);
	foreach($ever as $day){
		$key = date('F j Y', strtotime($day->start));

		if(!isset($allTime[$key])){
			$allTime[$key] = 1;
		} else {
			$allTime[$key]++;
		}
	}
	$allTime      = array_values($allTime);
	$allTimeNames = array_values($allTimeNames);
	if(!isset($allTimeNames[date('F j Y')])){
		$allTimeNames[] = date('F j, Y');
		$allTime[]    = 0;
	}

	$dayOfWeek = [0, 0, 0, 0, 0, 0, 0];
	$dayOfWeekNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
	foreach($sessions as $session){
		$day = intval(date('w', strtotime($session->start)));
		$dayOfWeek[$day]++;
	}

	$timeOfDay = [];
	$timeOfDayNames = [];
	$pm = true;
	for($i = 0; $i < 24; $i++){
		$timeOfDay[] = 0;
		$hr = $i % 12;
		if($hr == 0){ $hr = 12; $pm = !$pm; }
		$timeOfDayNames[$i] = $hr . ':00 ' . r($pm, 'PM', 'AM');
	}
	foreach($sessions as $session){
		$hour = intval(date('G', strtotime($session->start)));
		$timeOfDay[$hour]++;
	}


	$colorCycle = ['#999', '#666', '#333', '#aaa', '#777', '#888', '#444', '#bbb'];
	$colorIndex = 0;
	$countries = [];
	$countryColors = [];
	$ips = db::select('sessions', 'ip', ['game' => $game->id]);
	foreach($ips as $ip){
		$country = ip::country($ip);
		if($country == ''){ $country = 'Unknown Country'; }
		if(isset($countries[$country])){
			$countries[$country]++;
		} else {
			$countries[$country] = 1;
			$countryColors[] = $colorCycle[$colorIndex];
			$colorIndex++;
			if($colorIndex >= count($colorCycle)){ $colorIndex = 0; }
		}
	}
	$countryNames = array_keys($countries);
	$countries    = array_values($countries);

	$colorIndex = 0;
	$locations = [];
	$locationColors = [];
	$locs = db::select('sessions', 'location', ['game' => $game->id]);
	foreach($locs as $loc){
		$loc = $loc->location;
		if($loc == ''){ $loc = 'Unknown Location'; }
		if(isset($locations[$loc])){
			$locations[$loc]++;
		} else {
			$locations[$loc] = 1;
			$locationColors[] = $colorCycle[$colorIndex];
			$colorIndex++;
			if($colorIndex >= count($colorCycle)){ $colorIndex = 0; }
		}
	}
	$locationNames = array_keys($locations);
	$locations     = array_values($locations);

	$nav = ['plays today', 'plays this week', 'plays this month', 'every play', 'finish', 'time of day', 'day of week', 'country'];
?>

<div class="unit">
	<h1 class="light" id="plays-today">Plays Today</h1>
	<?php snippet('linechart', ['data' => $today, 'names' => $todayNames]) ?>

	<h1 class="light" id="plays-this-week">Plays This Week</h1>
	<?php snippet('linechart', ['data' => $thisWeek, 'names' => $thisWeekNames]) ?>

	<h1 class="light" id="plays-this-month">Plays This Month</h1>
	<?php snippet('linechart', ['data' => $thisMonth, 'names' => $thisMonthNames]) ?>

	<h1 class="light" id="every-play">Every Play</h1>
	<?php snippet('linechart', ['data' => $allTime, 'names' => $allTimeNames]) ?>

	<h1 class="light" id="finish">Finish</h1>
	<?php snippet('piechart', ['data' => [100-$beatPercent, $beatPercent], 'names' => ['Have Not Beaten', 'Have Beaten'], 'colors' => ['#aaa','#666']]) ?>

	<h1 class="light" id="time-of-day">Time of Day</h1>
	<?php snippet('barchart', ['data' => $timeOfDay, 'names' => $timeOfDayNames]) ?>

	<h1 class="light" id="day-of-week">Day of Week</h1>
	<?php snippet('barchart', ['data' => $dayOfWeek, 'names' => $dayOfWeekNames]) ?>

	<h1 class="light" id="country">Country</h1>
	<?php snippet('piechart', ['data' => $countries, 'names' => $countryNames, 'colors' => $countryColors]) ?>

	<h1 class="light" id="location">Location</h1>
	<?php snippet('piechart', ['data' => $locations, 'names' => $locationNames, 'colors' => $locationColors]) ?>
</div>

<?php snippet('nav', ['items' => $nav]) ?>