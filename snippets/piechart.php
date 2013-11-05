<?php
	$data   = json_encode($data);
	$names  = json_encode($names);
	$colors = json_encode($colors);
	$id     = str::random(6, 'alphaLower');
?>

<div class="chart">
	<div id="<?php echo $id ?>" style="width: 200px; margin: auto;"></div>
</div>

<script type="text/javascript">
	if(typeof data == 'undefined'){ data = {}; names = {}; options = {}; }
	data["<?php echo $id ?>"]  = <?php echo $data ?>;
	names["<?php echo $id ?>"] = <?php echo $names ?>;

	options["<?php echo $id ?>"] = {
		type: "pie",
		width: "100%",
		height: "200px",
		lineColor: false,
		sliceColors: <?php echo $colors ?>,
		names: names["<?php echo $id ?>"],
		highlightColor: "#000",
		tooltipFormatter: function(sparkline, options, fields){
			var n = options.mergedOptions.names[fields.offset];
			if(!n){ n = ""; }
			return Math.round(fields.percent) + "% " + n;
		}
	};


	$("#<?php echo $id ?>").sparkline(data["<?php echo $id ?>"], options["<?php echo $id ?>"]);

	$(window).resize(function(){
		$("#<?php echo $id ?>").sparkline(data["<?php echo $id ?>"], options["<?php echo $id ?>"]);
	});
</script>