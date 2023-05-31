<?php
declare (strict_types = 1);

namespace app\admin\model;

use app\admin\model\Base;

/**
 * @mixin \think\Model
 */
class AdminRole extends Base
{
    protected $schema = [
        'id'	    =>	'int',
        'admin_id'	=>	'int',
        'role_id'	=>	'int',
        'create_time'	=>	'int',
        'update_time'	=>	'int'
    ];
}
