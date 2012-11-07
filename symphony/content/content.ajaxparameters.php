<?php
	/**
	 * @package content
	 */
	/**
	 * The AjaxParameters returns an JSON array of all available parameters.
	 */
	require_once(TOOLKIT . '/class.datasourcemanager.php');

	Class contentAjaxParameters extends AjaxPage {

		public function view() {
			$params = array('{$today}', '{$current-time}', '{$this-year}', '{$this-month}', '{$this-day}', '{$timezone}', '{$website-name}', '{$page-title}', '{$root}', '{$workspace}', '{$root-page}', '{$current-page}', '{$current-page-id}', '{$current-path}', '{$current-query-string}', '{$current-url}', '{$cookie-username}', '{$cookie-pass}', '{$page-types}', '{$upload-limit}');
			
			// Get page parameters
			$pages = PageManager::fetch(true, array('params'));
			foreach($pages as $key => $pageparams) {
				if(!empty($pageparams['params'])) {
					$pageparams = explode('/', $pageparams['params']);
					foreach($pageparams as $pageparam) {
						$params[] = '{$' . $pageparam . '}';
					}
				}
			}
			
			// Get Data Sources output parameters
			$datasources = DatasourceManager::listAll();
			foreach($datasources as $datasource) {
				$current = DatasourceManager::create($datasource['handle']);
				$prefix = '{$ds-' . Lang::createHandle($datasource['name']) . '.';
				$suffix = '}';
				
				// Get parameters
				if(is_array($current->dsParamPARAMOUTPUT)) {
					foreach($current->dsParamPARAMOUTPUT as $id => $param) {
						$params[] = $prefix . Lang::createHandle($param) . $suffix;
					}
				}
			}
			
			sort($params);
			$this->_Result = json_encode($params);
		}

		public function generate(){
			header('Content-Type: application/json');
			echo $this->_Result;
			exit;
		}

	}
