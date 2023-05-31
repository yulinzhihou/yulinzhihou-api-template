<?php
declare (strict_types = 1);

namespace app\admin\model;

use app\admin\model\Base;

/**
 * @mixin \think\Model
 */
class ExceptionLog extends Base
{
    protected $schema = [
        "id"	    =>	"int",
        "admin_id"	=>	"int",
        "admin_name"=>	"string",
        "url"	    =>	"string",
        "params"	=>	"string",
        "class"	    =>	"string",
        "action"	=>	"string",
        "type"	    =>	"string",
        "file"	    =>	"string",
        "error_file"	=>	"string",
        "line"	    =>	"string",
        "error_line"=>	"string",
        "message"	=>	"string",
        "sql"	    =>	"string",
        "create_time"	=>	"int",
        "update_time"	=>	"int",
    ];

    /**
     * 构建数据
     * @param \Exception $e 异常类
     * @param int $errorLine 报错文件行号
     * @param string $errorFile 报错文件名
     * @param string $class     报错文件类名
     * @param string $action    报错文件方法名
     * @param string $type      报错文件类型
     * @param string $sql       报错文件执行的最后一条sql
     * @param array $adminInfo  管理员数组
     */
    public static function buildExceptionData(
        \Exception $e,
        int $errorLine,
        string $errorFile,
        string $class,
        string $action,
        string $type,
        string $sql,
        array $adminInfo = []
    ):void {
        $data = [
            'line'      => $e->getLine(),
            'file'       => $e->getFile(),
            'message'   => $e->getMessage(),
            'code'      => $e->getCode(),
            'url'       => request()->url(true),
            'params'    => json_encode(request()->param()),
            'error_line'    => $errorLine,
            'error_file'     => $errorFile,
            'class'         => $class,
            'action'        => $action,
            'type'          => $type,
            'sql'           => $sql,
            'admin_id'      => $adminInfo['id']??0,
            'admin_name'    => $adminInfo['nickname']??'未登录用户'
        ];

        self::create($data);
    }
}
