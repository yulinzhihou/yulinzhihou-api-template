<?php
declare (strict_types = 1);

namespace app\admin\model;

use app\admin\model\Base;

/**
 *
 */
class ApiManage extends Base
{
    protected $schema = [
		'id'	    =>	'int',
		'app_id'	=>	'varchar',
		'app_secret'	=>	'varchar',
		'public_key'	=>	'text',
		'private_key'	=>	'text',
		'username'	    =>	'varchar',
		'phone'	    =>	'varchar',
		'email'	    =>	'varchar',
		'nickname'	=>	'varchar',
		'password'	=>	'varchar',
		'salt'	    =>	'varchar',
		'avatar'	=>	'varchar',
		'desc'	    =>	'varchar',
		'status'	=>	'tinyint',
		'login_ip'	=>	'varchar',
		'login_failure'	=>	'tinyint',
		'extension'	    =>	'json',
		'login_time'	=>	'int',
		'create_time'	=>	'int',
		'update_time'	=>	'int',

    ];
}
