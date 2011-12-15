<?php

	Class migration_223 extends Migration{

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
			return '2.2.3';
		}

		static function getReleaseNotes(){
			return 'http://symphony-cms.com/download/releases/version/2.2.3/';
		}

		static function upgrade(){
			Symphony::Configuration()->set('version', '2.2.3', 'symphony');
			return Symphony::Configuration()->write();
		}

	}
