<?php

namespace app\admin\controller\v1;

use app\admin\controller\Base;
use app\admin\model\ExceptionLog;
use think\facade\Cache;

/**
 * 异步队列业务逻辑演示类-
 * 非正式业务逻辑代码，只是一个演示案例，切务直接使用。
 */
class AsyncDemo extends Base
{
    /**
     * 保存新建的资源
     */
    public function save():\think\Response\Json
    {
        try {
            $this->inputData = array_merge($this->inputData,$this->request->post());
            //前置拦截
            if (empty($this->inputData)) {
                return $this->jr('请检查提交过来的数据');
            }
            // 额外增加请求参数
            if (!empty($this->params)) {
                $this->inputData = array_merge($this->inputData,$this->params);
            }

            // 验证器
            if ($this->commonValidate(__FUNCTION__,$this->inputData)) {
                return $this->message(true);
            }

            $result = $this->model->addData($this->inputData);
            // 根据新增结果触发异步导入数据
            if ($result) {
                $modelName = $this->model->getFileModelById($this->inputData['file_type']);
                // 改写数据
                $fileTypeStr = (int)$this->inputData['file_type'];
                $versionId = (int)$this->inputData['version_id'];

                // 假如需要根据数据库所有数据进行联动更新操作。先获取所有数据，在这个业务方法里面进行所有数据切分成单一执行逻辑交与异步队列
                $allData = $this->model->getAllData();

                if (!empty($allData)) {
                    foreach ($allData as $data) {
                        if ((int)$data > 0) {
                            $rData = Cache::get($fileTypeStr.':version_'.$versionId.':'.$data);
                            if (!empty($rData)) {
                                // 每循环一次，将对应需要异步处理的第一次方法交给异步队列进行处理，
                                // 这是一个折中的办法 ，目前我用这个方法进行队列处理，还是可以很丝滑的。但还是有一些坑，不过勉强能用了。
                                // 这里面的坑主要是数据库长连接的问题，还有就是守护进程可能会处于僵尸进程状态，需要重启。暂时是可以用这个文案进行处理
                                // 目前主要处理大文件excel进行导入数据，大概陆续处理了近1千多万条数据到数据库，文件有1400多个文件。目前没啥大问题
                                $this->doAsyncFunc($modelName['modelName'],$versionId,$fileTypeStr,(string)array_keys($rData)[0],$rData);
                            }
                        }
                    }
                }

            }
            $this->sql = $this->model->getLastSql();
            return $this->jr(['新增失败','新增成功-请等候3-5分钟刷新查看！'],true);
        } catch (\Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'controller',$this->sql,$this->adminInfo);
            return $this->jr('新增异常，请查看异常日志或者日志文件进行修复');
        }

    }

}