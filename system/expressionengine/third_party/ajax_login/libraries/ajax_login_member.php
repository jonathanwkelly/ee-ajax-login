<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class ajax_login_member
{
	/* ========== public methods ========== */

	/**
	 * Will lookup an EE member account by email.
	 *
	 * @access 		public
	 * @param  		string $email
	 * @return 		array|FALSE FALSE if no record could be found; an array of data otherwise
	 */
	public function getMemberFromEmail($email='')
	{
		if(!$email)
			return FALSE;

		$record = ee()->db
			->where('email', $email)
			->or_where('username', $email)
			->get('members')
			->result_array();

		return !empty($record) ? current($record) : FALSE;
	}

	// ---

	/**
	 * Will check the password(s) and see if EE says they're good; process copied from
	 * Member_auth:process_reset_password() in mod.member_auth.php
	 *
	 * @access 		public
	 * @param 		string The unencrypted password
	 * @param  		string The unencrypted password confirmation
	 * @return 		string An error message, if one is produced; FALSE otherwise
	 */
	public function passwordErrors($password='', $password_confirm='')
	{
		if(!class_exists('EE_Validate'))
			require_once APPPATH.'libraries/Validate.php';

		$VAL = new EE_Validate(array(
			'password'			=> $password,
			'password_confirm'	=> $password_confirm,
		 ));

		$VAL->validate_password();

		if(count($VAL->errors) > 0)
			return current($VAL->errors);

		return FALSE;
	}

	// ---

	/**
	 * Will do some checks on the user's status and member groups to see if they 
	 * should be considered "active"
	 * 
	 * @access  	public
	 * @param  		array $member
	 * @return 		boolean 
	 */
	public function memberIsActive($member=array())
	{
		if(!is_array($member) || empty($member) || !isset($member['group_id']))
			return FALSE;

		// --- check EE default groups that should not be considered "active"

		if($member['group_id'] && !in_array($member['group_id'], array(2, 3, 4))) /* banned, guests, pending */
			return TRUE;

		return FALSE;
	}
}