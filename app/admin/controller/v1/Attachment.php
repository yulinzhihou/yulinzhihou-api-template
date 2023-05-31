<?php
declare (strict_types = 1);

namespace app\admin\controller\v1;

use app\admin\controller\Base;
use app\admin\model\Attachment as AttachmentModel;
use app\admin\validate\Attachment as AttachmentValidate;

/**
 * Attachment 附件上传模块
 */
class Attachment extends Base
{
    public function initialize():void
    {
        parent::initialize();
        $this->model = new AttachmentModel();
        $this->validate = new AttachmentValidate();
    }

}
