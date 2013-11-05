<?php
	$data   = json_encode($data);
	$names  = json_encode($names);
	$id     = str::random(6, 'alphaLower');
?>

<div class="chart" id="<?php echo $id ?>"></div>

<script type="text/javascript">
	if(typeof data == 'undefined'){ data = {}; names = {}; options = {}; }
	data["<?php echo $id ?>"]  = <?php echo $data ?>;
	names["<?php echo $id ?>"] = <?php echo $names ?>;

	var w = $("#<?php echo $id ?>").innerWidth();
	w -= (data["<?php echo $id ?>"].length <= 10 ? 30 : 10) * data["<?php echo $id ?>"].length;
	w = w / data["<?php echo $id ?>"].length;

	options["<?php echo $id ?>"] = {
		type: "bar",
		width: "100%",
		height: "200px",
		lineColor: false,
		names: names["<?php echo $id ?>"],
		barColor: "#999",
		negBarColor: "#666",
		highlightColor: "#000",
		barWidth: w,
		barSpacing: (data["<?php echo $id ?>"].length <= 10 ? 30 : 10),
		tooltipFormatter: function(sparkline, options, fields){
			var n = options.mergedOptions.names[fields[0].offset];
			if(!n){ n = ""; } else { n += " - "; }
			return n + fields[0].value;
		}
	};


	$("#<?php echo $id ?>").sparkline(data["<?php echo $id ?>"], options["<?php echo $id ?>"]);

	$(window).resize(function(){
		w = $("#<?php echo $id ?>").innerWidth();
		w -= (data["<?php echo $id ?>"].length <= 10 ? 30 : 10) * data["<?php echo $id ?>"].length;
		w = w / data["<?php echo $id ?>"].length;
		options["<?php echo $id ?>"].barWidth = w;
		$("#<?php echo $id ?>").sparkline(data["<?php echo $id ?>"], options["<?php echo $id ?>"]);
	});
</script>