/**
 * @package assets
 */

(function($) {

	/**
	 * This plugin makes items selectable. Clicking an item will select it 
	 * by adding the class <code>.selected</code>. Holding down the shift key 
	 * while clicking multiple items creates a selection range. Holding the meta key 
	 * (which is <code>cmd</code> on a Mac or <code>ctrl</code> on Windows) allows 
	 * the selection of multiple items or the modification of an already selected 
	 * range of items. Doubleclicking outside the selection list will 
	 * remove the selection.
	 *
	 * @name $.symphonySelectable
	 * @class
	 *
	 * @param {Object} options An object specifying containing the attributes specified below
	 * @param {String} [options.items='tbody tr:has(input)'] Selector to find items that are selectable
	 * @param {String} [options.handles='td'] Selector to find children that can be clicked to select the
	 * item. Needed to properly handle item highlighting when used in connection with the orderable plugin
	 * @param {String} [options.ignore='a'] Selector to find elements that should not propagate to the handle
	 *
	 *	@example

			var selectable = $('table').symphonySelectable();
			selectable.find('a').mousedown(function(event) {
				event.stopPropagation();
			});
	 */
	$.fn.symphonySelectable = function(options) {
		var objects = this,
			settings = {
				items: 'tbody tr:has(input)',
				handles: 'td',
				ignore: 'a'
			};

		$.extend(settings, options);

	/*-----------------------------------------------------------------------*/

		// Select
		objects.on('click.selectable', settings.items, function(event) {
			var item = $(this),
				items = item.siblings().andSelf(),
				object = $(event.liveFired),
				target = $(event.target),
				selection, deselection, first, last;

			// Ignored elements
			if(target.is(settings.ignore)) {
				return true;
			}

			// Remove text ranges
			if(window.getSelection) {
				window.getSelection().removeAllRanges();
			}

			// Range selection
			if((event.shiftKey) && items.filter('.selected').length > 0 && !object.is('.single')) {

				// Select upwards
				if(item.prevAll().filter('.selected').length > 0) {
					first = items.filter('.selected:first').index();
					last = item.index() + 1;
				}

				// Select downwards
				else {
					first = item.index();
					last = items.filter('.selected:last').index() + 1;
				}

				// Get selection
				selection = items.slice(first, last);

				// Deselect items outside the selection range
				deselection = items.filter('.selected').not(selection).removeClass('selected').trigger('deselect');
				deselection.find('input[type="checkbox"]').attr('checked', false);

				// Select range
				selection.addClass('selected').trigger('select');
				selection.find('input[type="checkbox"]').attr('checked', true);
			}

			// Single selection
			else {

				// Press meta key to adjust current range, otherwise the selection will be removed
				if(!event.metaKey || object.is('.single')) {
					deselection = items.not(item).filter('.selected').removeClass('selected').trigger('deselect');
					deselection.find('input[type="checkbox"]').attr('checked', false);
				}

				// Toggle selection
				if(item.is('.selected')) {
					item.removeClass('selected').trigger('deselect');
					item.find('input[type="checkbox"]').attr('checked', false);		
				}
				else {
					item.addClass('selected').trigger('select');
					item.find('input[type="checkbox"]').attr('checked', true);		
				}
			}

		});	

		// Remove all selections by doubleclicking the body
		$('body').bind('dblclick.selectable', function() {
			objects.find(settings.items).removeClass('selected').trigger('deselect');
		});

	/*-----------------------------------------------------------------------*/

		// Make selectable
		objects.addClass('selectable');

	/*-----------------------------------------------------------------------*/

		return objects;
	};

})(jQuery.noConflict());
