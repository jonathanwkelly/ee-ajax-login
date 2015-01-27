<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * AJAX Login Module Class
 *
 * @package        ee_ajax_login
 * @author         Jonathan Kelly <jonathanwkelly@gmail.com>
 * @link           http://github.com/jonathanwkelly/ee_ajax_login
 */

class Ajax_login
{
	/* ========== properties ========== */

	/**
	 * Return data
	 *
	 * @access     public
	 * @var        string
	 */
	public $return_data = '';

	/**
	 * Holds an instance of the libraries/ajax_login_member object
	 *
	 * @access     private
	 * @var        object
	 */
	private $member;

	/* ========== public methods ========== */

	public function __construct()
	{
		// --- load up an instance of the member library

		ee()->load->library('ajax_login_member');

		$this->member =& ee()->ajax_login_member;

		// --- get our language file

		ee()->lang->loadfile('ajax_login');
	}

	// ---

	/**
	 * Output the opening & closing form tags, and any other inputs needed 
	 * in order to process an ajax form
	 *
	 * @access 		public
	 * @param 		void
	 * @return 		string
	 */
	public function form()
	{
		// --- set our params (form attrs) from the tag, or use defaults

		$attrs = array(
			'action' 	=> '/',
			'method' 	=> 'post',
			'id' 		=> 'ajax-login-form',
			'class' 	=> ''
		);

		foreach($attrs as $key => $def)
			$attrs[$key] = ee()->TMPL->fetch_param($key, $def);

		// --- build the form HTML

		$ret = '<form data-ajax-login';

		// --- add each of the form attributes

		foreach($attrs as $key => $val)
			$ret .= sprintf(' %s="%s"', $key, $val);

		$ret .= '>' . "\n";

		// --- the guts of the form, as they included within the tag pair

		$ret .= ee()->TMPL->tagdata;

		// --- add in the hidden inputs we need

		$ret .= sprintf('<input type="hidden" name="XID" value="%s">' . "\n", ee()->security->generate_xid());
		$ret .= sprintf('<input type="hidden" name="AID" value="%d">' . "\n", $this->_getActionId('member_login'));

		// --- close the form

		$ret .=  "\n" . '</form>';

		$this->return_data = $ret;

		return $this->return_data;
	}

	// ---

	/**
	 * Handles the processing of the login form, outputting the response in JSON
	 *
	 * @access 		public
	 * @param 		void
	 * @return 		void
	 */
	public function member_login()
	{
		ee()->load->library('auth');

		// --- get the posted values

		$email = ee()->input->post('email');
		$password = ee()->input->post('password');
		$password_confirm = ee()->input->post('password_confirm');

		// --- no record by this email

		if(!($member = $this->member->getMemberFromEmail($email)))
		{
			// --- respond with "no account" code/msg

			$this->_respond(
				FALSE, 
				lang('al_err_no_account_code'), 
				array('email' => lang('al_err_no_account_msg'))
			);
		}

		// --- there's an account for this email; now we do more checks...

		else
		{
			// --- check the login they've posted

			/* will perform all the auth checks against the provided credentials; if this
				has a response, the login is good, but the user is not yet logged in */
			$authTest = ee()->auth->authenticate_email($email, $password);

			// --- login is bad

			if(!$authTest)
			{
				// --- respond with "bad login" code/msg

				$this->_respond(
					FALSE, 
					lang('al_err_wrong_password_code'), 
					array('email' => lang('al_err_wrong_password_msg'))
				);
			}

			// --- login is good; log them in

			else
			{
				ee()->session->create_new_session($member['member_id'], FALSE);

				$this->_respond(
					TRUE,
					null,
					null,
					array(
						'member_id' => $member['member_id'],
						'username' => $member['username'],
						'screen_name' => $member['screen_name']
					)
				);
			}

			// --- is their login correct?

			

			// if($passError = $this->member->passwordErrors($password, $password_confirm))
			// {
			// 	$this->_respond(
			// 		FALSE, 
			// 		lang('al_err_wrong_password_code'), 
			// 		array('password' => $passError)
			// 	);
			// }
		}
	}

	/* ========== private methods ========== */

	/**
	 * Will output the JSON response to the buffer. 
	 * 
	 * @access  	private
	 * @param  		boolean A general fail/pass flag
	 * @param 		number An error code, if one occurred, that maps to the 
	 * error message as defined in the add-on language file. Pass this a FALSE
	 * value to return no error code/message.
	 * @param 		mixed An associative array of data to be returned in the 
	 * output. For example, array('member' => array('id' => 1, 'name' => 'Meh'))
	 * @return 		void
	 */
	private function _respond($success=FALSE, $errorCode=0, $errors=array(), $data=array())
	{
		header('Content-Type: application/json');

		echo json_encode(array(
			'success' => ($success === TRUE),
			'errorCode' => $errorCode,
			'errors' => $errors,
			'data' => $data
		));

		exit;
	}

	/**
	 * Get the values of the template tag parameters
	 *
	 * @access 		private
	 * @param 		array The names of the tag parameters to get
	 * @return 		array
	 */
	private function _getTagParams($defaults=array())
	{
		$params = array();

		foreach($names as $name)
			$params[$name] = ee()->TMPL->fetch_param($name);

		return $params;
	}

	// ---

	/**
	 * A method to sidestep the ee()->functions->fetch_action_id() method, since 
	 * it does not return the ID we need, but instaead a template tag. 
	 *
	 * @access 		private
	 * @param 		string The method that is mapped to the "method" column in the actions table
	 * @return 		number
	 */
	private function _getActionId($method='')
	{
		if($method)
			return (int) current(ee()->db
				->select('action_id')
				->where('class', __CLASS__)
				->where('method', $method)
				->limit(1)
				->get('actions')
				->row());

		return 0;
	}

	// --------------------------------------------------------------------

} // End class

/* End of file mod.ajax_login.php */