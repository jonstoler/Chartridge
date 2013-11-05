<?php
	$data = json_encode($data);
	$id = str::random(6, 'alphaLower');
?>

<div class="chart" id="<?php echo $id ?>"></div>

<script type="text/javascript">
	$(function(){
		var data = <?php echo $data ?>;

		var hrs = [];
		var am = false;
		for(var i = 0; i < 24; i++){
			var h = (i % 12);
			if(h == 0){ h = 12; am = !am; }
			hrs[i] = h + ":00 " + (am ? "AM" : "PM");
		}

		var dates = [];
		var months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
		var m = months[new Date().getMonth()];
		for(var i = 1; i <= 31; i++){
			var ones = i % 10;
			var suffix = "th";
			if(ones == 1 && (i < 10 || i > 20)){ suffix = "st"; }
			else if(ones == 2 && (i < 10 || i > 20)){ suffix = "nd"; }
			else if(ones == 3 && (i < 10 || i > 20)){ suffix = "rd"; }
			dates[i-1] = m + " " + i + suffix;
		}

		var names = {
			"today": hrs,
			"thisweek": ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
			"thismonth": dates,
			"alltime": data.alltime_names
		};

		var options = {
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
			names: names.today,
			tooltipFormatter: function(sparkline, options, fields){
				var n = options.mergedOptions.names[fields.x];
				if(!n){ n = ""; } else { n += " - "; }
				return n + fields.y;
			}
		}

		var d = data.today;
		$("#<?php echo $id ?>").sparkline(d, options);

		$(".chartoptions .button").click(function(){
			if(!$(this).hasClass("selected")){
				$(".chartoptions .button.selected").removeClass("selected");
				$(this).addClass("selected");

				options.names = names[$(this).attr("id")];
				d = data[$(this).attr("id")];
				if(d.length <= 1){
					$("#<?php echo $id ?>").html('<h1 class="light center">More data points necessary. :(</h1>');
				} else {
					$("#<?php echo $id ?>").sparkline(d, options);
				}
			}
		});

		$(window).resize(function(){
			if(d.length > 1){
				$("#<?php echo $id ?>").sparkline(d, options);
			}
		});
	})
</script>