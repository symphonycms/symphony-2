<?php
	/**
	 * @package toolkit
	 */
	/**
	 * The AuthorManager class is responsible for managing all Author objects
	 * in Symphony. Unlike other Manager objects, Authors are stored in the
	 * database, and not on the file system. CRUD methods are implemented to
	 * allow Authors to be created (add), read (fetch), updated (edit) and
	 * deleted (delete).
	 */

	require_once(TOOLKIT . '/class.manager.php');

	Class AuthorManager extends Manager {

		/**
		 * Given an associative array of fields, insert them into the database
		 * returning the resulting AuthorID if successful, or false if there
		 * was an error
		 *
		 * @param array $fields
		 *  Associative array of field names => values for the Author object
		 * @return int|boolean
		 *  Returns an author_id of the created Author on success, false otherwise.
		 */
		public static function add(Array $fields){
			if(!Symphony::Database()->insert($fields, 'tbl_authors')) return false;
			$author_id = Symphony::Database()->getInsertID();

			return $author_id;
		}

		/**
		 * Given an Author ID and associative array of fields, update an existing Author
		 * row in the Database's authors table. Returns boolean for success/failure
		 *
		 * @param integer $id
		 *  The ID of the Author that should be updated
		 * @param array $fields
		 *  Associative array of field names => values for the Author object
		 *  This array does need to contain every value for the author object, it
		 *  can just be the changed values.
		 * @return boolean
		 */
		public static function edit($id, Array $fields){
			return Symphony::Database()->update($fields, 'tbl_authors', " `id` = '$id'");
		}

		/**
		 * Given an Author ID, delete an Author from Symphony.
		 *
		 * @param integer $id
		 *  The ID of the Author that should be deleted
		 * @return boolean
		 */
		public static function delete($id){
			return Symphony::Database()->delete('tbl_authors', " `id` = '$id'");
		}

		/**
		 * The fetch method returns all Authors from Symphony with the option to sort
		 * or limit the output. This method returns an array of Author objects.
		 *
		 * @param string $sortby
		 *  The field to sort the authors by, defaults to 'id'
		 * @param string $sortdirection
		 *  Available values of ASC (Ascending) or DESC (Descending), which refer to the
		 *  sort order for the query. Defaults to ASC (Ascending)
		 * @param integer $limit
		 *  The number of rows to return
		 * @param integer $start
		 *  The offset start point for limiting, maps to the LIMIT {x}, {y} MySQL functionality
		 * @return array(Author)
		 *  An array of Author objects.  If no Authors are found, null is returned.
		 */
		public static function fetch($sortby = 'id', $sortdirection = 'ASC', $limit = null, $start = null){

			$records = Symphony::Database()->fetch(sprintf("
					SELECT *
					FROM `tbl_authors`
				 	ORDER BY %s %s
					%s %s
				",
				$sortby, $sortdirection,
				($limit) ? "LIMIT " . $limit : '',
				($start && $limit) ? ', ' . $start : ''
			));

			if(!is_array($records) || empty($records)) return null;

			$authors = array();

			foreach($records as $row){
				$author = new Author;

				foreach($row as $field => $val) {
					$author->set($field, $val);
				}

				$authors[] = $author;
			}

			return $authors;
		}

		/**
		 * The fetchById method returns Author that match the provided ID's with
		 * the option to sort or limit the output.
		 *
		 * @param int|array $id
		 *  A single ID or an array of ID's
		 * @param string $sortby
		 *  The field to sort the authors by, defaults to 'id'
		 * @param string $sortdirection
		 *  Available values of ASC (Ascending) or DESC (Descending), which refer to the
		 *  sort order for the query. Defaults to ASC (Ascending)
		 * @param integer $limit
		 *  The number of rows to return
		 * @param integer $start
		 *  The offset start point for limiting, maps to the LIMIT {x}, {y} MySQL functionality
		 * @return mixed
		 *  If $id was an integer, the result will be an Author object, otherwise an array of
		 *  Author objects will be returned. If no Authors are found, or no $id is given null is returned.
		 */
		public static function fetchByID($id, $sortby = 'id', $sortdirection = 'ASC', $limit = null, $start = null){

			$return_single = false;

			if(!is_array($id)){
				$return_single = true;
				$id = array($id);
			}

			if(empty($id)) return null;

			$records = Symphony::Database()->fetch(sprintf("
					SELECT *
					FROM `tbl_authors`
					WHERE `id` IN (%d)
					ORDER BY %s %s
					%s %s
				",
				implode(",", $id),
				$sortby, $sortdirection,
				($limit) ? "LIMIT " . $limit : '',
				($start && $limit) ? ', ' . $start : ''
			));

			if(!is_array($records) || empty($records)) return null;

			$authors = array();

			foreach($records as $row){
				$author = new Author;

				foreach($row as $field => $val) {
					$author->set($field, $val);
				}

				$authors[] = $author;
			}

			return ($return_single ? $authors[0] : $author);
		}

		/**
		 * The fetchByUsername method returns an Author by Username.
		 *
		 * @param string $username
		 *  The Author's username
		 * @return Author|null
		 *  If an Author is found, an Author object is returned, otherwise null.
		 */
		public static function fetchByUsername($username){
			$records = Symphony::Database()->fetchRow(0, sprintf("
					SELECT *
					FROM `tbl_authors`
					WHERE `username` = '%s'
					LIMIT 1
				",	Symphony::Database()->cleanValue($username)
			));

			if(!is_array($records) || empty($records)) return null;

			$author = new Author;

			foreach($rec as $field => $val)
				$author->set($field, $val);

			return $author;
		}

		/**
		 * This function will allow an Author to sign into Symphony by using their
		 * authentication token as well as username/password.
		 *
		 * @param integer $author_id
		 *  The Author ID to allow to use their authentication token.
		 * @return boolean
		 */
		public static function activateAuthToken($author_id){
			if(!is_int($author_id)) return false;
			return Symphony::Database()->query("UPDATE `tbl_authors` SET `auth_token_active` = 'yes' WHERE `id` = $author_id");
		}

		/**
		 * This function will remove the ability for an Author to sign into Symphony
		 * by using their authentication token
		 *
		 * @param integer $author_id
		 *  The Author ID to allow to use their authentication token.
		 * @return boolean
		 */
		public static function deactivateAuthToken($author_id){
			if(!is_int($author_id)) return false;
			return Symphony::Database()->query("UPDATE `tbl_authors` SET `auth_token_active` = 'no' WHERE `id` = $author_id");
		}

	}
