<?php
declare (strict_types = 1);

namespace app\admin\controller\v1;

use app\admin\controller\Base;
use app\admin\model\ApiManage as ApiManageModel;
use app\admin\validate\ApiManage as ApiManageValidate;

/**
 * ApiManage
 */
class ApiManage extends Base
{
    public function initialize():void
    {
        parent::initialize();
        $this->model = new ApiManageModel();
        $this->validate = new ApiManageValidate();
    }

}
