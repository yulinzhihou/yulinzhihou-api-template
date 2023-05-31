<?php
declare (strict_types = 1);

namespace app\admin\controller\v1;

use app\admin\controller\Base;
use app\admin\model\ApiLog as ApiLogModel;
use app\admin\validate\ApiLog as ApiLogValidate;

/**
 * ApiLog 访问日志控制器
 */
class ApiLog extends Base
{
    public function initialize():void
    {
        parent::initialize();
        $this->model = new ApiLogModel();
        $this->validate = new ApiLogValidate();
        $this->order['create_time'] = 'desc';
    }

}
