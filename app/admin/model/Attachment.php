<?php
declare (strict_types = 1);

namespace app\admin\model;

use app\admin\model\Base;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * @mixin \think\Model
 */
class Attachment extends Base
{
    protected $schema = [
        'id'	    =>	'int',
        'name'	    =>	'string',
        'method'	=>	'string',
        'topic'	    =>	'string',
        'admin_id'	=>	'int',
        'user_id'	=>	'int',
        'url'	    =>	'string',
        'width'	    =>	'int',
        'height'	=>	'int',
        'size'	    =>	'int',
        'mimetype'	=>	'string',
        'use_count'	=>	'int',
        'storage'	=>	'string',
        'sha1'	    =>	'string',
        'create_time'	=>	'int',
        'update_time'	=>	'int',
    ];

    protected $append = [
        'suffix',
        'full_url',
        'admin_name',
        'user_name'
    ];

    /**
     * 动态获取器
     * @param $value
     * @param $row
     * @return string
     */
    public function getUserNameAttr($value, $row):string
    {
        if (isVarExists($row,'user_id')) {
            return $row['user_id'] == 0 ? '后台用户传' : '';
        } else {
            return '';
        }
    }

    /**
     * 动态获取器
     * @param $value
     * @param $row
     * @return string
     */
    public function getAdminNameAttr($value, $row):string
    {
        if ($row['admin_id']) {
            $name = Admin::where('id',$row['admin_id'])->value('nickname');
            return $name == '' ? '' : $name;
        }
        return '';
    }

    /**
     * 动态获取器
     * @param $value
     * @param $row
     * @return string
     */
    public function getSuffixAttr($value, $row):string
    {
        if ($row['name']) {
            $suffix = strtolower(pathinfo($row['name'], PATHINFO_EXTENSION));
            return $suffix && preg_match("/^[a-zA-Z0-9]+$/", $suffix) ? $suffix : 'file';
        }
        return 'file';
    }

    /**
     *
     * @param $value
     * @param $row
     * @return bool|string
     */
    public function getFullUrlAttr($value, $row)
    {
        return full_url($row['url']);
    }

}
