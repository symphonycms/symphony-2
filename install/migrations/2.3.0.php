<?php

	Class migration_230 extends Migration{

		static function run($function, $existing_version = null) {
			self::$existing_version = $existing_version;

			try{
				$canProceed = self::$function();

				return ($canProceed === false) ? false : true;
			}
			catch(DatabaseException $e){
				$error = Symphony::Database()->getLastError();
				Symphony::Log()->writeToLog('Could not complete upgrading. MySQL returned: ' . $error['num'] . ': ' . $error['msg'], E_ERROR, true);

				return false;
			}
			catch(Exception $e){
				Symphony::Log()->writeToLog('Could not complete upgrading because of the following error: ' . $e->getMessage(), E_ERROR, true);

				return false;
			}
		}

		static function getVersion(){
			return '2.3beta2';
		}

		static function getReleaseNotes(){
			return 'https://gist.github.com/1162216';
		}

		static function upgrade(){
			// 2.3dev
			if(version_compare(self::$existing_version, '2.3dev', '<=')) {
				Symphony::Configuration()->set('version', '2.3dev', 'symphony');
				Symphony::Configuration()->set('useragent', 'Symphony/2.3dev', 'general');

				// Add Publish Label to `tbl_fields`
				if(!Symphony::Database()->tableContainsField('tbl_fields', 'publish_label')) {
					Symphony::Database()->query('ALTER TABLE `tbl_fields` ADD `publish_label` VARCHAR(255) DEFAULT NULL');
				}

				// Migrate any Checkbox's Long Description to Publish Label
				try {
					$checkboxes = Symphony::Database()->fetch("SELECT `field_id`, `description` FROM `tbl_fields_checkbox`");

					foreach($checkboxes as $field) {
						if(!isset($field['description'])) continue;

						Symphony::Database()->query(sprintf("
							UPDATE `tbl_fields`
							SET `publish_label` = '%s'
							WHERE `id` = %d
							LIMIT 1;
							",
							$field['description'],
							$field['field_id']
						));
					}

					Symphony::Database()->query("ALTER TABLE `tbl_fields_checkbox` DROP `description`");
				} catch(Exception $ex) {}

				// Removing unused settings
				Symphony::Configuration()->remove('allow_page_subscription', 'symphony');
				Symphony::Configuration()->remove('strict_error_handling', 'symphony');
				Symphony::Configuration()->remove('character_set', 'database');
				Symphony::Configuration()->remove('character_encoding', 'database');
				Symphony::Configuration()->remove('runtime_character_set_alter', 'database');

				if(Symphony::Configuration()->get('pagination_maximum_rows', 'symphony') == '17'){
					Symphony::Configuration()->set('pagination_maximum_rows', '20', 'symphony');
				}

				Symphony::Configuration()->write();
			}

			// 2.3 Beta 1
			if(version_compare(self::$existing_version, '2.3beta1', '<')) {
				Symphony::Configuration()->set('version', '2.3beta1', 'symphony');
				Symphony::Configuration()->set('useragent', 'Symphony/2.3 Beta 1', 'general');

				return Symphony::Configuration()->write();
			}

			// 2.3 Beta 2
			if(version_compare(self::$existing_version, '2.3beta2', '<=')) {

				$fields = Symphony::Database()->fetch('SELECT `publish_label`, `label`, `id` FROM `tbl_fields`');

				foreach($fields as $field){
					if(!$field['publish_label']) continue;

					Symphony::Database()->query(sprintf("
						UPDATE `tbl_fields`
						SET `label` = '%s'
						WHERE `id` = %d
						LIMIT 1;
						",
						$field['publish_label'],
						$field['id']
					));
				}

				Symphony::Database()->query("ALTER TABLE `tbl_fields` DROP `publish_label`");

				Symphony::Configuration()->set('version', '2.3beta2', 'symphony');
				Symphony::Configuration()->set('useragent', 'Symphony/2.3 Beta 2', 'general');

				return Symphony::Configuration()->write();
			}
		}

	}
