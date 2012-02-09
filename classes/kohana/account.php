<?php

class Kohana_Account {

	public static $salt;

	/**
	 * Login a user, returns false if they are
	 * unable to login
	 *
	 * @param $username Username to login with
	 * @param $password Password to login with
	 * @param $remember Should the user be rememberd?
	 * @return boolean was the user logged in?
	 */
	public static function login($username, $password, $remember = FALSE)
	{

		$user = ORM::factory('account_user')
			->where('username', '=', $username)
			->where('password', '=', Account::hash($password));

		if($user->find()->loaded())
		{
			// Set the current user ID into the session
			Session::instance()->set('user', $user->id);

			// Update the last login time
			$user->set('last_login', time())->save();

			// If they want to remember their password...
			if($remember !== FALSE)
			{
				$user->generate_cookie();
			}

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Destroys the current users session
	 *
	 * @return boolean Ensures the session was
	 * destroyed successfully
	 */
	public static function logout()
	{
		if(($user = Account::get_user()) === FALSE)
			return FALSE;

		// Clear the users cookie
		$user->destroy_cookie();

		// Destroy the users session
		return Session::instance()->destroy();
	}

	/**
	 * Checks if a user is logged in
	 * and returns a ORM object for the user
	 * returns false if no one is logged in
	 *
	 * @return mixed the current user or false
	 */
	public static function get_user()
	{
		$user_id = Session::instance()->get('user');

		if($user_id === NULL)
		{
			// Attempt to login via cookie
			if($user = ORM::factory('account_user')->validate_cookie())
			{
				Session::instance()->set('user', $user->id);
				return $user;
			}

			return FALSE;
		}

		return ORM::factory('account_user', $user_id);
	}

	/**
	 * Hash the text string using a secure
	 * and slow password hashing algo
	 *
	 * @param $password a text string
	 * @return string a hash representation of the string
	 */
	public static function hash($password)
	{
		if(Account::$salt === NULL)
			throw new Kohana_Exception("You must set your salt for Account password hashing.");

		return hash_hmac("sha512", $password, Account::$salt);
	}

}