<?php

class Kohana_Model_Account_User extends ORM {

	public function filters()
	{
		return array(
			'password' => array(array('Account::hash'))
		);
	}

	/**
	 * Generate a 'remeber me' cookie for
	 * the user this object is currently
	 * initalized too
	 */
	public function generate_cookie()
	{
		// Load the ORM token model
		$token = ORM::factory('account_token');

		// Generate the token entry values
		$values = array(
			'user_id' => $this->id,
			'user_agent' => sha1(Request::$user_agent),
			'expires' => time() + $token->expire_time,
		);

		// Generate token in the database
		$token->values($values)->save();

		// Generate cookie for this token
		Cookie::set('account', $token->bare_token, $token->expire_time);
	}

	/**
	 * Check if the user has a cookie set
	 * to automaticly log them in. If the
	 * cookie is valid in the token database
	 * then initalize this ORM object to their
	 * user account and log them in.
	 *
	 * @return mixed ORM object or FALSE for invalid/no cookie
	 */
	public function validate_cookie()
	{
		// Attempt to load the associated token from the database
		$token = ORM::factory('account_token', Account::hash(Cookie::get('account')));

		// Make sure we found a valid token
		if( ! $token->loaded() OR $token->user_agent !== sha1(Request::$user_agent))
		{
			$this->destroy_cookie();
			return FALSE;
		}

		// Update token, and experation time
		$token->set('expires', time() + $token->expire_time)->save();

		// Do the same for the account cookie
		Cookie::set('account', $token->bare_token, $token->expire_time);

		// Looks good, initalize this object
		return $this->where('id', '=', $token->user_id)->find();
	}

	/**
	 * Remove the users cookie and database
	 * record from the database if applicable
	 */
	public function destroy_cookie()
	{
		// Grab the associated database result for this token
		$token = ORM::factory('account_token', Account::hash(Cookie::get('account')));

		// Delete the record if it exists
		$token->loaded() AND $token->delete();

		// Remove the cookie
		Cookie::delete('account');
	}

}