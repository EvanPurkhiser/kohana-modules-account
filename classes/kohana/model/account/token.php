<?php

class Kohana_Model_Account_Token extends ORM {

	/**
	 * @var set the primary ket to the token
	 */
	protected $_primary_key = 'token';

	/**
	 * How long should we remember the user
	 * before destroying their token?
	 */
	public $expire_time = 604800;

	/**
	 * The un-hashed token. will be set upon
	 * creation or modification of a user token
	 */
	public $bare_token = NULL;

	/**
	 * Handles garbage collection and deleting
	 * this object if it's expired.
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if ($this->_loaded AND $this->expires < time())
		{
			$this->delete();
		}

		if (mt_rand(1, 100) === 1)
		{
			// Do garbage collection
			$this->delete_expired();
		}
	}

	/**
	 * Deletes all expired tokens.
	 */
	public function delete_expired()
	{
		// Delete all expired tokens
		DB::delete($this->_table_name)
			->where('expires', '<', time())
			->execute($this->_db);

		return $this;
	}

	/**
	 * Override the save method to keep the token updated
	 */
	public function save(Validation $validation = NULL)
	{
		// Generate a new unique token
		$token = $this->create_token();

		// Hash the token and store it in the database
		$this->token = Account::hash($token);
		$this->bare_token = $token;

		return parent::save($validation);
	}

	/**
	 * Create a token that will always be unique
	 */
	protected function create_token()
	{
		do
		{
			$token = sha1(uniqid(Text::random('alnum', 32), TRUE));
		}
		while(ORM::factory('account_token', array('token' => $token))->loaded());

		return $token;
	}

}