/**
 * @package assets
 */

(function($) {

	/**
	 * @todo: documentation
	 */
	$.fn.symphonyTimeAgo = function(options) {
		var objects = this,
			settings = {
				items: 'time',
				timestamp: 'datetime'
			};
		
		$.extend(settings, options);

	/*-----------------------------------------------------------------------*/
	
		function parse(item) {
			var timestamp = item.data('timestamp'),
				datetime, now;
				
			// Fetch stored timestamp
			if($.isNumeric(timestamp)) {
				return timestamp;
			}
			
			// Parse date
			else {
				datetime = item.attr(settings.timestamp);
				
				// Defined date and time
				if(datetime) {
					
					// Browsers that understand ISO 8601
					timestamp = Date.parse(datetime);
					
					// Browsers that understand ISO 8601 without timezone
					if(isNaN(timestamp)) {
						timestamp = Date.parse(datetime.substring(0, 19));
					}
				
					// Browsers that don't understand ISO 8601
					if(isNaN(timestamp)) {
						timestamp = Date.parse(datetime.replace(/-/g, '/').replace(/T/g, ' ').replace(/Z/, ' UTC'));
					}
				}
				
				// Undefined date and time
				if(!$.isNumeric(timestamp)) {
					now = new Date();
					timestamp = now.getTime();
				} 

				// Store and return timestamp
				item.data('timestamp', timestamp);
				return timestamp;
			}
		}

		function say(from, to) {

			// Calculate time difference
			var distance = to - from,

			// Convert time to minutes
			time = Math.floor(distance / 60000);

			// Return relative date based on passed time
			if(time < 1) {
				return Symphony.Language.get('just now');
			}
			if(time < 2) {
				return Symphony.Language.get('a minute ago');
			}
			if(time < 45) {
				return Symphony.Language.get('{$minutes} minutes ago', {
					'minutes': time
				});
			}
			if(time < 90) {
				return Symphony.Language.get('about 1 hour ago');
			}
			else {
				return Symphony.Language.get('about {$hours} hours ago', {
					'hours': Math.floor(time / 60)
				});
			}
		};

	/*-----------------------------------------------------------------------*/

		objects.find(settings.items).each(function() {
			var item = $(this),
				from = parse(item),
				to = new Date();
				
			// Set relative time
			item.text(say(from, to));
		});

	/*-----------------------------------------------------------------------*/

		return objects;
	};

})(jQuery.noConflict());
