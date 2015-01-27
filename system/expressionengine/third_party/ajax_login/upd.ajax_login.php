<?php if( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ajax_login_upd
{
	public $version = '1.0';

	private $name = 'Ajax_login';

	public function install()
	{
		ee()->db->insert(
			'modules', 
			array(
				'module_name' => $this->name,
				'module_version' => $this->version,
				'has_cp_backend' => 'n',
				'has_publish_fields' => 'n'
			)
		);

		return TRUE;
	}

	public function update($current = '')
	{
		return ($current != $this->version);
	}

	public function uninstall()
	{
		ee()->db->query(sprintf("DELETE FROM exp_modules WHERE module_name = '%s'", $this->name));
		
		return TRUE;
	}
}

/* End of File: upd.ajax_login.php */
