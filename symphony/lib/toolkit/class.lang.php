<?php
	/**
	 * @package toolkit
	 */
	/**
	 * The translation function accepts an English string and returns its translation
	 * to the active system language. If the given string is not available in the
	 * current dictionary the original English string will be returned. Given an optional
	 * array of inserts the function will also replace translation placeholders using vsprintf().
	 *
	 * @param string $string
	 *  The string that should be translated
	 * @param array $inserts
	 *  Optional array used to replace translation placeholders, defaults to NULL
	 * @return
	 *  Returns the translated string
	 */
	function __($string, $inserts=NULL) {
		return Lang::Dictionary()->translate($string, $inserts);
	}

	/**
	 * The transliteration function replaces special characters.
	 *
	 * @param string $string
	 *  The string that should be cleaned-up
	 * @return
	 *  Returns the transliterated string
	 */
	function _t($string) {
		$patterns = array_keys(Lang::Transliterations());
		$values = array_values(Lang::Transliterations());
		return preg_replace($patterns, $values, $string);
	}

	/**
	 * The Dictionary class contains a dictionary of all strings of the active
	 * system language. It allows strings to be added, removed, found or translated.
	 * It also offers a function to merge different dictionaries which is used to combine
	 * core and extension translations. The Dictionary class should only be used internally:
	 * for string translations and languages activation please use the translation function
	 * and make use of the Lang class provided in this file.
	 */
	Class Dictionary {

		/**
		 * @var array An associative array mapping English strings and their translations
		 */
 		private $_strings;

		/**
		 * The constructor for Dictionary.
		 *
		 * @param array $strings
		 *  Associative array mapping English strings and their translations, defaults to an empty array
		 */
		public function __construct($strings=array()) {
			$this->_strings = $strings;
		}

		/**
		 * The translation function accepts an English string and returns its translation
		 * to the active system language. If the given string is not available in the
		 * current dictionary the original English string will be returned. Given an optional
		 * array of inserts the function will also replace translation placeholders using vsprintf().
		 *
		 * Note: If you like to translate strings, please use __() which is the common alias for this function.
		 *
		 * @param string $string
		 *  The string that should be translated
		 * @param array $inserts
		 *  Optional array used to replace translation placeholders, defaults to NULL
		 * @return
		 *  Returns the translated string
		 */
 		public function translate($string, $inserts=NULL) {
			$translated = $this->find($string);

			// Default to English if no translation available
			if($translated === false) {
				$translated = $string;
			}

			// Replace translation placeholders
			if(is_array($inserts) && !empty($inserts)) {
				$translated = vsprintf($translated, $inserts);
			}

			return $translated;
		}

		/**
		 * Given a string, return its translation.
		 *
		 * @param string $string
		 *  The string to look for
		 * @return
		 *  Returns either the translation of the string or false if it could not be found
		 */
		public function find($string) {
			if(isset($this->_strings[$string])) {
				return $this->_strings[$string];
			}

			return false;
		}

		/**
		 * Given a source string an its translation, add both to the current dictionary.
		 *
		 * @param string $source
		 *  English string
		 * @param string $translation
		 *  Translation
		 */
		public function add($source, $translation) {
			$this->_strings[$source] = $translation;
		}

		/**
		 * Given an associative array of strings, merge it with the current dictionary.
		 *
		 * @param array $string
		 *  Associative array containing English strings and their translations
		 */
		public function merge($strings) {
			if(is_array($strings)) {
				$this->_strings = array_merge($this->_strings, $strings);
			}
		}

		/**
		 * Given an English string, remove it from the current dictionary.
		 *
		 * @param string $string
		 *  String to be removed from the dictionary.
		 */
		public function remove($string) {
			unset($this->_strings[$string]);
		}

	}

	/**
	 * The Lang class loads and manages languages
	 */
	Class Lang {

		/**
		 * @var string Code of active language
		 */
		private static $_lang;

		/**
		 * @var array Context information of all available languages
		 */
		private static $_languages;

		/**
		 * @var Dictionary Instance of the current dictionary
		 */
		private static $_dictionary;

		/**
		 * @var array Array of transliterations
		 */
		private static $_transliterations;

		/**
		 * @var array Array of months and weekday for localized date output
		 */
		private static $_dates;

		/**
		 * Get dictionary
		 *
		 * @return
		 *  Return the current dictionary
		 */
		public static function Dictionary() {
			return self::$_dictionary;
		}

		/**
		 * Get transliterations
		 *
		 * @return
		 *  Returns the array of transliterations
		 */
		public static function Transliterations() {
			return self::$_transliterations;
		}

		/**
		 * Initialize dictionary, transliterations and dates array
		 */
		public static function initialize() {
			self::$_dictionary = new Dictionary();
			self::$_transliterations = array();
			self::$_dates = array();
		}

		/**
		 * Set system language.
		 *
		 * @param string $lang
		 *  Language code, e. g. 'en' or 'pt-br'
		 */
		public static function set($lang, $enabled=true) {
			if($lang && $lang != self::get()) {

				// Store current language code
				self::$_lang = $lang;

				// Activate language
				self::activate($enabled);

			}
		}

		/**
		 * Get current language
		 */
		public static function get() {
			return self::$_lang;
		}

		/**
		 * Activate language, load translations for core and extensions. If the specified language
		 * cannot be found, Symphony will default to English. If no language is available at all,
		 * Symphony will throw an error.
		 *
		 * Note: Beginning with Symphony 2.2 translations bundled with extensions will only be loaded
		 * when the core dictionary of the specific language is available.
		 */
		public static function activate($enabled=true) {

			// Fetch all available languages
			if(empty(self::$_languages)) {
				self::fetch();
			}

			// Language file available
			$current = self::$_languages[self::get()];
			if(is_array($current) && ($current['status'] == 'enabled' || $enabled == false)) {

				// Load core translations
				self::load($current['path'], true);

				// Load extension translations
				if(class_exists('Symphony')) {

					// Fetch Extension Manager
					if(class_exists('Administration')) {
						$ExtensionManager = Administration::instance()->ExtensionManager;
					}
					else {
						$ExtensionManager = Frontend::instance()->ExtensionManager;
					}

					// Loop through extensions
					foreach($ExtensionManager->listAll() as $handle => $extension) {

						// Skip language extensions
						if(strpos($handle, 'lang_') === false) continue;

						// Load translations
						$path = $ExtensionManager->__getClassPath($handle) . '/lang/lang.' . self::get() . '.php';
						if($extension['status'] == EXTENSION_ENABLED && file_exists($path)) {
							self::load($path);
						}
					}
				}

			}

			// Language file unavailable
			else {

				// Use default language
				self::$_lang = 'en';
				$default = self::$_languages['en'];
				if(is_array($default)) {
					self::load($default['path'], true);

					// Log error
					if(class_exists('Symphony')) {
						Symphony::$Log->pushToLog(
							__('The selected language could not be found. Using default English dictionary instead.'),
							E_ERROR,
							true
						);
					}

				}

				// No language file available at all
				else {
					throw new Exception('Symphony needs at least one language file.');
				}

			}
		}

		/**
		 * Fetch all languages available in the core language folder and the language extensions.
		 * The function stores all language information in the public variable $_languages.
		 * It contains an array with the name, source, path and status of each language. The language
		 * status (enabled/disabled) can only be determined when the Extension Manager has been
		 * initialized before. During installation all extension status are set to disabled.
		 */
		public static function fetch() {
			self::$_languages = array();

			// Fetch list of active extensions
			$enabled = array();
			if(class_exists('Symphony')) {
				$enabled = Symphony::Database()->fetchCol('name',
					"SELECT `name` FROM `tbl_extensions` WHERE `status` = 'enabled'"
				);
			}

			// Fetch core languages
			$directory = General::listStructure(LANG);
			foreach($directory['filelist'] as $file) {
				self::fetchLanguage('core', LANG, $file, $enabled);
			}

			// Fetch extensions
			$extensions = new DirectoryIterator(EXTENSIONS);
			foreach($extensions as $extension) {

				// Explicitly match language extensions
				if(strpos($extension->getFilename(), 'lang_') !== false) {
					$folder = $extension->getPathname() . '/lang';
					$directory = General::listStructure($folder);
					foreach($directory['filelist'] as $file) {
						self::fetchLanguage($extension->getFilename(), $folder, $file, $enabled);
					}
				}

			}

		}

		/**
		 * Fetch language information for a single language.
		 */
		private static function fetchLanguage($source, $folder, $file, $enabled) {

			// Fetch language file
			$path = $folder . '/' . $file;
			if(file_exists($path)) {
				include($path);
			}

			// Get language code
			$lang = explode('.', $file);
			$lang = $lang[1];

			// Get status
			$status = 'disabled';
			if($source == 'core') {
				$status = 'enabled';
			}
			elseif(!empty($enabled) && in_array($source, $enabled)) {
				$status = 'enabled';
			}

			// Save language information
			self::$_languages[$lang] = array(
				'name' => $about['name'],
				'source' => $source,
				'path' => $path,
				'status' => $status
			);
		}

		/**
		 * Load language file. Each language file contains three arrays:
		 * about, dictionary and transliterations.
		 *
		 * @param string $path
		 *  Path of the language file that should be loaded
		 * @param boolean $clear
		 *  True, if the current dictionary should be cleared, defaults to false
		 */
		public static function load($path, $clear=false) {

			// Initialize or clear dictionary
			if(!(self::$_dictionary instanceof Dictionary) || $clear === true) {
				self::initialize();
			}

			// Load language file
			if(file_exists($path)) {
				require($path);
			}

			// Define default dates
			if(empty(self::$_dates)) {
				$dates = array(
					'yesterday', 'today', 'tomorrow', 'now',
					'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday',
					'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat',
					'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December',
					'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
				);
				foreach($dates as $date) {
					self::$_dates[$date] = $date;
				}
			}

			// Merge dictionaries
			if(isset($dictionary) && is_array($dictionary)) {
				self::$_dictionary->merge($dictionary);

				// Add date translations
				foreach(self::$_dates as $date) {
					self::$_dates[$date] = __($date);
				}

			}

			// Populate transliterations
			if(isset($transliterations) && is_array($transliterations)) {
				self::$_transliterations = array_merge(self::$_transliterations, $transliterations);
			}

			// Use default if no transliterations are provided with the translations
			if(empty(self::$_transliterations)) {
				include(LANG . '/lang.en.php');
				self::$_transliterations = $transliterations;
			}

		}

		/**
		 * Get an array of the codes and names of all languages that are available system wide.
		 *
		 * Note: Beginning with Symphony 2.2 language files are only available
		 * when the language extension is explicitly enabled.
		 *
		 * @return array
		 *  Returns an associative array of language codes and names, e. g. 'en' => 'English'
		 */
		public static function getAvailableLanguages($enabled=true) {
			$languages = array();

			// Get available languages
			foreach(self::$_languages as $key => $language) {
				if($language['status'] == 'enabled' || $enabled == false) {
					$languages[$key] = $language['name'];
				}
			}

			// Return languages codes
			return $languages;
		}

		/**
		 * Check if Symphony is localised.
		 *
		 * @return boolean
		 *  Returns true for localized system, false for English system
		 */
		public function isLocalized() {
			return (self::get() != 'en');
		}

		/**
		 * Localize dates.
		 *
		 * @param string $string
		 *  Standard date that should be localized
		 * @return string
		 *  Return the given date with translated month and day names
		 */
		public static function localizeDate($string) {

			// Only translate dates in localized environments
			if(self::isLocalized()) {
				foreach(self::$_dates as $english => $locale) {
					$string = str_replace($english, $locale, $string);
				}
			}

			return $string;
		}

		/**
		 * Standardize dates.
		 *
		 * @param string $string
		 *  Localized date that should be standardized
		 * @return string
		 *  Returns the given date with English month and day names
		 */
		public static function standardizeDate($string) {

			// Get date and time separator
			$separator = Symphony::$Configuration->get('datetime_separator', 'region');

			// Only standardize dates in localized environments
			if(self::isLocalized()) {
				foreach(self::$_dates as $english => $locale) {

					// Translate names to English
					$string = str_replace($locale, $english, $string);

					// Replace custom date and time separator with space:
					// This is important, otherwise PHP's strtotime() function may break
					if($separator != ' ') {
						$string = str_replace($separator, ' ', $string);
					}
				}
			}

			return $string;
		}


		/**
		 * Given a string, this will clean it for use as a Symphony handle. Preserves multi-byte characters.
		 *
		 * @param string $string
		 *	String to be cleaned up
		 * @param int $max_length
		 *  The maximum number of characters in the handle
		 * @param string $delim
		 *  All non-valid characters will be replaced with this
		 * @param boolean $uriencode
		 *  Force the resultant string to be uri encoded making it safe for URLs
		 * @param boolean $apply_transliteration
		 *  If true, this will run the string through an array of substitution characters
		 * @return string
		 *  Returns resultant handle
		 */
		public static function createHandle($string, $max_length=255, $delim='-', $uriencode=false, $apply_transliteration=true, $additional_rule_set=NULL) {

			// Use the transliteration table if provided
			if($apply_transliteration == true) $string = _t($string);

			$max_length = intval($max_length);

			// Strip out any tag
			$string = strip_tags($string);

			// Remove punctuation
			$string = preg_replace('/[\\.\'"]+/', NULL, $string);

			// Trim it
			if($max_length != NULL && is_numeric($max_length)) $string = General::limitWords($string, $max_length);

			// Replace spaces (tab, newline etc) with the delimiter
			$string = preg_replace('/[\s]+/', $delim, $string);

			// Find all legal characters
			preg_match_all('/[^<>?@:!-\/\[-`ëí;‘’…]+/u', $string, $matches);

			// Join only legal character with the $delim
			$string = implode($delim, $matches[0]);

			// Allow for custom rules
			if(is_array($additional_rule_set) && !empty($additional_rule_set)) {
				foreach($additional_rule_set as $rule => $replacement) $string = preg_replace($rule, $replacement, $string);
			}

			// Remove leading or trailing delim characters
			$string = trim($string, $delim);

			// Encode it for URI use
			if($uriencode) $string = urlencode($string);

			// Make it lowercase
			$string = strtolower($string);

			return $string;

		}

		/**
		 * Given a string, this will clean it for use as a filename. Preserves multi-byte characters.
		 *
		 * @param string $string
		 *  String to be cleaned up
		 * @param string $delim
		 *  Replacement for invalid characters
		 * @param boolean $apply_transliteration
		 *  If true, umlauts and special characters will be substituted
		 * @return string
		 *  Returns created filename
		 */
		public static function createFilename($string, $delim='-', $apply_transliteration=true) {

			// Use the transliteration table if provided
			if($apply_transliteration == true) $string = _t($string);

			// Strip out any tag
			$string = strip_tags($string);

			// Find all legal characters
			$count = preg_match_all('/[\p{L}\w:;.,+=~]+/u', $string, $matches);
			if($count <= 0 || $count == false) {
				preg_match_all('/[\w:;.,+=~]+/', $string, $matches);
			}

			// Join only legal character with the $delim
			$string = implode($delim, $matches[0]);

			// Remove leading or trailing delim characters
			$string = trim($string, $delim);

			// Make it lowercase
			$string = strtolower($string);

			return $string;

		}

	}
