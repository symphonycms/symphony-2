<?php

	/**
	 * @package content
	 */
	/**
	 * This class handles sortable objects via `$_REQUEST` parameters.
	 *
	 * @since Symphony 2.3
	 */

	Class Sortable {

		/**
		 * This method initializes the `$result`, `$sort` and `$order` variables by looking at
		 * `$_REQUEST`. Then it calls the `sort()` method on the object being passed by `$object`
		 * to actually sort the items.
		 *
		 * @param object $object
		 *	The object responsible for sorting the items. It must implement a `sort()` method.
		 * @param array $result
		 *	This variable stores an array sorted objects. Once set, its value is available
		 *	to the client class of Sortable.
		 * @param string $sort
		 *	This variable stores the field (or axis) the objects are sorted by. Once set,
		 *	its value is available to the client class of Sortable.
		 * @param string $order
		 *	This variable stores the sort order (i.e. 'asc' or 'desc'). Once set, its value
		 *	is available to the client class of Sortable.
		 * @param array $params (optional)
		 *	An array of parameters that can be passed to the context-based method.
		 */
		public static function init($object, &$result, &$sort, &$order, array $params = array()) {
			$sort = (isset($_REQUEST['sort'])) ? $_REQUEST['sort'] : null;
			$order = ($_REQUEST['order'] == 'desc' ? 'desc' : 'asc');

			$result = $object->sort($sort, $order, $params);
		}

		/**
		 * This method build the markup for sorting-aware table headers. It accepts an array
		 * of columns as shown below, as well as the current sorting axis `$sort` and the
		 * current sort order `$order`. If `$extra_url_params` are provided, they are appended
		 * to the redirect string upon clicking on a table header.
		 *
		 *		'label' => 'Column label',
		 *		'sortable' => (true|false),
		 *		'handle' => 'handle for the column (i.e. the field ID), used as value for $sort',
		 *		'attrs' => array(
		 *			'HTML <a> attribute' => 'value',
					[...]
		 *		)
		 *
		 * @param array $columns
		 *	An array of columns that will be converted into table headers.
		 * @param string $sort
		 *	The current field (or axis) the objects are sorted by.
		 * @param string $order
		 *	The current sort order (i.e. 'asc' or 'desc').
		 * @param string $extra_url_params (optional)
		 *	A string of URL parameters that will be appended to the redirect string.
		 * @return array
		 *	An array of table headers that can be directly passed to `Widget::TableHead`.
		 */
		public static function buildTableHeaders($columns, $sort, $order, $extra_url_params = null) {
			$aTableHead = array();

			foreach($columns as $c) {
				if($c['sortable']) {

					if($c['handle'] == $sort) {
						$link = sprintf(
							'?sort=%s&amp;order=%s%s',
							$c['handle'], ($order == 'desc' ? 'asc' : 'desc'), $extra_url_params
						);
						$label = Widget::Anchor(
							$c['label'], $link,
							__('Sort by %1$s %2$s', array(($order == 'desc' ? __('ascending') : __('descending')), strtolower($c['label']))),
							'active'
						);
					}
					else {
						$link = sprintf(
							'?sort=%s&amp;order=asc%s',
							$c['handle'], $extra_url_params
						);
						$label = Widget::Anchor(
							$c['label'], $link,
							__('Sort by %1$s %2$s', array(__('ascending'), strtolower($c['label'])))
						);
					}

				}
				else {
					$label = $c['label'];
				}

				$aTableHead[] = array($label, 'col', $c['attrs']);
			}

			return $aTableHead;
		}

	}
