<?php
	$data  = json_encode($data);
	$names = json_encode($names);
	$id    = str::random(6, 'alphaLower');
?>

<div class="chart" id="<?php echo $id ?>"></div>

<script type="text/javascript">
	if(typeof data == 'undefined'){ data = {}; names = {}; options = {}; }
	data["<?php echo $id ?>"]  = <?php echo $data ?>;
	names["<?php echo $id ?>"] = <?php echo $names ?>;

	options["<?php echo $id ?>"] = {
		type: "line",
		lineWidth: 1,
		width: "100%",
		height: "200px",
		lineColor: false,
		fillColor: "#999",
		highlightLineColor: "#666",
		highlightSpotColor: false,
		spotColor: false,
		minSpotColor: false,
		maxSpotColor: false,
		names: names["<?php echo $id ?>"],
		tooltipFormatter: function(sparkline, options, fields){
			var n = options.mergedOptions.names[fields.x];
			if(!n){ n = ""; } else { n += " - "; }
			return n + fields.y;
		}
	};

	if(data["<?php echo $id ?>"].length <= 1){
		$("#<?php echo $id ?>").html('<h1 class="light center">More data points necessary. :(</h1>');
	} else {
		$("#<?php echo $id ?>").sparkline(data["<?php echo $id ?>"], options["<?php echo $id ?>"]);
	}

	$(window).resize(function(){
		if(data["<?php echo $id ?>"].length > 1){
			$("#<?php echo $id ?>").sparkline(data["<?php echo $id ?>"], options["<?php echo $id ?>"]);
		}
	});
</script>