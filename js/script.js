$(document).ready(function(){
	var d = 0;
	$(".progress-inner").each(function(){
		if($(this).attr("data-width") != undefined){
			$(this).delay(d).animate({width: $(this).attr("data-width") + "%"});
			d += 100;
			$(this).find(".progress-title").delay(d + 100).animate({opacity: 1}, 1000);
			$(this).not(".disable-tipsy").attr("title", $(this).attr("data-width") + "%");
		}
	});

	$(".progress-inner").not(".disable-tipsy").tipsy({gravity: 's', fade: true});
	$(".progress").hover(function(){
		$(this).find(".progress-inner").tipsy("show");
	});
	$(".progress").mouseout(function(){
		$(this).find(".progress-inner").tipsy("hide");
	});

	var dela = 200;
	$(".nav-item").each(function(){
		$(this).delay(dela).animate({left: "80%"}, 600, "easeOutBack");
		dela += 100;
	});

	var del = 0;
	$(".play-total, .playcount").each(function(){
		if($(this).attr("data-tick") != undefined){
			var de = del;
			var t = $(this).text();
			var max = Math.floor(parseFloat($(this).attr("data-tick"))) + (10 * (parseFloat($(this).attr("data-tick")) % 1));
			var f = $(this);
			while(t < max){
				t++;
				setTimeout(function(){
					var diff = parseFloat(f.attr("data-tick")) - parseFloat(f.text());
					f.text(parseFloat(f.text()) + (diff >= 1 ? 1 : 0.1));
					if(parseFloat(f.text()) % 1 != 0){
						f.text(parseFloat(f.text()).toFixed(1));
					}
				}, de);
				de += (300 / max);
			}
		}
		del += 200;
	});

	$(".nav-item").hover(function(){
		$(this).animate({left: "0%"}, {queue: false});
	});
	$(".nav-item").mouseout(function(){
		$(this).animate({left: "80%"}, {queue: false});
	});

	$(".nav-item").click(function(){
		var search = $(this).text().toLowerCase();
		search = search.replace(/\s/g, '-');
		var id = document.getElementById(search);
		if(id){
			var y = $(id).offset().top - 20;
			$("html, body").animate({ scrollTop: y });
		}
	});

	$(".delete").click(function(e){
		if(!$(this).hasClass("disable")){
			if($(this).attr("confirm") == undefined){ e.preventDefault(); }
			$(this).text("Are you sure?");
			$(this).attr("confirm", "true");
		}
	});

	var suggestGameID = function(){
		if($("#add-game-id").attr("changed") == undefined && !$("form").hasClass("edit")){
			var t = $(this).val();
			t = t.toLowerCase();
			t = t.replace(" ", "-", "g");
			$("#add-game-id").val(t);
		}
	}

	$("#add-game-name").keypress(suggestGameID);
	$("#add-game-name").keyup(suggestGameID);

	$("#add-game-id").change(function(){
		if($(this).val() == ""){ $(this).removeAttr("changed"); suggestGameID(); }
		else{ $(this).attr("changed", "true"); }
	});

	$("#add-another-checkpoint, #add-another-bonus").click(function(e){
		e.preventDefault();
		var div = document.createElement("div");
		var n = document.createElement("input");
		n.setAttribute("type", "text");
		var x = document.createElement("span");
		x.setAttribute("class", "x");
		x.innerHTML = "x";

		div.appendChild(n);
		div.appendChild(x);

		$(div).insertBefore($(this));
		$(n).focus();
	});

	$("body").on("keypress", ".add-checkpoint input", function(e){
		if(e.keyCode == 13 && !$(this).next().is("input")){
			$("#add-another-checkpoint").click();
		}
	});
	$("body").on("keypress", ".add-bonus input", function(e){
		if(e.keyCode == 13 && !$(this).next().is("input")){
			$("#add-another-bonus").click();
		}
	});

	$("body").on("blur", ".add-checkpoint input, .add-bonus input", function(){
		if($(this).val().match(/(.*)\{([0-9]*)-([0-9]*)\}/g)){
			var start = $(this).val().replace(/(.*)\{([0-9]*)-([0-9]*)\}/gm, "$2");
			var end = $(this).val().replace(/(.*)\{([0-9]*)-([0-9]*)\}/gm, "$3");
			for(var i = start; i <= end; i++){
				var div = document.createElement("div");
				var n = document.createElement("input");
				n.setAttribute("type", "text");
				n.setAttribute("value", $(this).val().replace(/(.*)\{([0-9]*)-([0-9]*)\}/gm, "$1") + i);
				var x = document.createElement("span");
				x.setAttribute("class", "x");
				x.innerHTML = "x";

				div.appendChild(n);
				div.appendChild(x);

				$(div).insertBefore($(this).parent());
			}
			$(this).parent().remove();
		}

		if($(this).val() == "" && ($(this).prev().is("input") || $(this).next().is("input"))){
			$(this).remove();
		}
	});

	$("body").on("click", ".x", function(){
		if($(this).parent().next().is("div:not(.button)") || $(this).parent().prev().is("div:not(.button)")){
			$(this).parent().remove();
		} else {
			$(this).prev().val("");
			$(this).prev().focus();
		}
	});

	$(".onoff").click(function(e){
		e.preventDefault();
		var state = ($(this).attr("state") != "on");
		$(this).attr("state", (state ? "on" : "off"));
		$(this).text($(this).attr((state ? "on" : "off")));
	});

	$("#add-game").click(function(e){
		e.preventDefault();
		var gameName = $("#add-game-name").val();
		createInput("game-name", gameName);
		var gameID = $("#add-game-id").val();
		createInput("game-id", gameID);

		var checkpoints = "";
		$(".add-checkpoint input").each(function(){
			if($(this).val() != ""){
				if(checkpoints == ""){ checkpoints += $(this).val(); }
				else { checkpoints += "," + $(this).val(); }
			}
		});
		createInput("checkpoints", checkpoints);

		var bonuses = "";
		$(".add-bonus input").each(function(){
			if($(this).val() != ""){
				if(bonuses == ""){ bonuses += $(this).val(); }
				else { bonuses += "," + $(this).val(); }
			}
		});
		createInput("bonuses", bonuses);

		createInput("disable_checkpoint_unit", ($("#display-checkpoint").attr("state") != "on") ? '1' : '0');
		createInput("disable_bonus_unit", ($("#display-bonus").attr("state") != "on") ? '1' : '0');
		createInput("disable_score_unit", ($("#display-score").attr("state") != "on") ? '1' : '0');
		createInput("disable_increment_unit", ($("#display-increment").attr("state") != "on") ? '1' : '0');
		createInput("disable_data_unit", ($("#display-data").attr("state") != "on") ? '1' : '0');

		createInput("edit_existing", ($("form").hasClass("edit") ? 'yes' : 'no'));

		$("#submit").click();

		function createInput(name, val){
			var n = document.createElement("input");
			n.setAttribute("type", "text");
			n.setAttribute("name", name);
			n.setAttribute("value", val);
			$(n).insertBefore("#submit");
		}
	});

	var mode = "";
	$("#game-manage .button").click(function(){
		if($(this).hasClass("selected")){
			$(this).text(mode + " game");
			mode = "";
			$(".title-unit h1").html('<span class="section">chartridge &rsaquo;</span> Games');
			$(this).removeClass("selected");
			$("#click-a-game").text("");
			$(".game").each(function(){
				$(this).attr("href", "./game/" + $(this).attr("id"));
			});
		} else if($(this).hasClass("edit")){
			mode = "edit";
			$(".title-unit h1").html('<span class="section">Edit</span> A Game');
			$(this).addClass("selected");
			$("#click-a-game").text("Click on a game to edit it.");
			$(".game").each(function(){
				$(this).attr("href", "./edit/" + $(this).attr("id"));
			});
			$(this).text("Cancel Edit");
			$(".del").removeClass("selected");
			$(".del").text("Delete Game");
		} else if($(this).hasClass("del")){
			mode = "delete";
			$(".title-unit h1").html('<span class="section">Delete</span> A Game');
			$(this).addClass("selected");
			$("#click-a-game").text("Click on a game to delete it. This cannot be undone.");
			$(".game").each(function(){
				$(this).attr("href", "./delete/" + $(this).attr("id"));
			});
			$(this).text("Cancel Delete");
			$(".edit").removeClass("selected");
			$(".edit").text("Edit Game");
		}
	});
});