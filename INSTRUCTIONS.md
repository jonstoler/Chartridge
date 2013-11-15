# CHARTRIDGE INSTRUCTIONS

## Installation
Fill out `config.php` for your server and configuration.

**The first time you run Chartrige, load `boot.php`.**

Chartridge is now fully installed!


## How Chartridge Works
Chartridge uses a few different concepts to keep track of game data. They are designed for simplicity and versatility. They are each outlined below.

### Games
This is fairly obvious. Games' data are tracked independently of one another so you can keep track of how much one specific game is actually played.

### Players
Every person (really, every computer) that plays your game is given a unique, anonymous id that is accessible to all games that use Chartridge. So you can track whether a player has played more than one of your games, or if they've played your game part-way through, left, then returned.

### Sessions
Every time a player plays a game, it is treated as a session. Sessions are the main organization tool in Chartridge. Note that one player can have multiple sessions.

### Checkpoints
Checkpoints are linear denominations of progress in your game. For instance, the end of each level of your game could be considered a checkpoint.

All checkpoints are defined ahead of time - there should be no dynamic checkpoints - so Chartridge can easily tell you the percentage progress that any player or session (or the average player or session) has reached.

### Bonuses
Bonuses are like checkpoints in that they are binary (either you got it or you didn't), but they are not measurements of progress. Achievements are a good way to use bonuses, or if you want to track which path a player took when given a choice.

### Increments
Increments are numerical measurements. For instance, you can use increments to keep track of how many times a player dies before finishing a level.

Note that, despite the name, increments can go up or down and do not necessarily have to change by 1 every time. They can also be set to a specific value.

### Scores
Scores are pretty self-explanatory. If your game is arcade-style or keeps track of score, you can track your players' scores and view the top scores.

Scores are divided into *Modes*, which you can use to track difficulty levels or entirely different measurements of score.

### Data
"Data" is the name for any arbitrary data you wish to track. So far, everything tracked has been numerical in nature. Datum let you track strings and are therefore the most flexibile method of measurement, although not always useful for certain games.


## Implementing Chartridge Into Your Game
I have written an ActionScript implementation of Chartridge, which you should use as a template if you wish to include Chartridge support in your framework or engine.

The code is pasted below.

	import flash.events.Event;
	import flash.events.IOErrorEvent;
	import flash.net.SharedObject;
	import flash.net.URLLoader;
	import flash.net.URLRequest;
	import flash.net.URLVariables;
	import flash.net.sendToURL;
	public class Chartridge
	{
		private static var _gameName:String = "";
		private static var _url:String = "";
		private static var _id:String = "";
		private static var _player:String = "";
		private static var _enabled:Boolean = true;
		private static var _location:String = "Unknown";

		public static function init(game:String, url:String, location:String = null):void
		{
			_gameName = game;
			_url = url;
			if(location != null && location != ""){
				_location = location;
			}

			if(_enabled){
				var request:URLRequest = new URLRequest(_url + "register.php");
				var variables:URLVariables = new URLVariables();
				variables["game"] = _gameName;

				var player:SharedObject = SharedObject.getLocal("playerID");
				if(player.size != 0){
					variables["player"] = player.data.id;
					_player = player.data.id;
				}

				variables["location"] = _location;

				request.data = variables;
				request.method = "POST";


				var loader:URLLoader = new URLLoader();
				loader.addEventListener(Event.COMPLETE, setID);
				loader.addEventListener(IOErrorEvent.IO_ERROR, ioError)
				loader.load(request);
			}
		}
		private static function ioError(event:Event):void
		{
			// if the url is not valid/not working/whatever,
			// disable stats for this session
			_enabled = false;
		}
		private static function setID(event:Event):void
		{
			var loader:URLLoader = URLLoader(event.target);
			var data:String = loader.data;
			if(data.indexOf(",") != -1){
				_id = data.split(",")[0];
				_player = data.split(",")[1];

				var so:SharedObject = SharedObject.getLocal("playerID");
				so.data.id = _player;
				so.flush();
			} else {
				_id = loader.data;
			}
		}

		public static function reset(generateNewIDs:Boolean = true):void
		{
			var so:SharedObject = SharedObject.getLocal("playerID");
			so.clear();
			so.flush();

			_id = _player = "";

			if(generateNewIDs){ init(_gameName, _url, _location); }
		}

		public static function disable():void
		{
			_enabled = false;
		}

		public static function checkpoint(name:String):void
		{
			submit({"checkpoint": name});
		}

		public static function bonus(name:String):void
		{
			submit({"bonus": name});
		}

		public static function score(score:Number):void
		{
			submit({"score": score});
		}

		public static function increment(name:String, by:Number = 1, decrease:Boolean = false):void
		{
			var obj:Object = {"increment": name, "by": by};
			if(decrease){ obj["decrease"] = "yes"; }
			submit(obj);
		}
		public static function incrementTo(name:String, to:Number = 0):void
		{
			submit({"increment": name, "to": to});
		}

		public static function data(name:String, value:*):void
		{
			submit({"data": name, "value": value});
		}

		private static function submit(data:Object):void
		{
			if(_gameName != "" && _id != "" && _enabled){
				var request:URLRequest = new URLRequest(_url + "track.php");
				var variables:URLVariables = new URLVariables();

				for(var i:* in data){
					variables[i] = data[i];
				}

				variables["game"] = _gameName;
				variables["id"] = _id;
				variables["location"] = _location;

				request.data = variables;
				request.method = "GET";
				sendToURL(request);
			}
		}
	}